<?php

/**
 * This is the model class for table "teasers".
 *
 * The followings are the available columns in table 'teasers':
 * @property string $id
 * @property string $title
 * @property string $description
 * @property string $picture
 * @property string $news_id
 * @property integer $is_active
 * @property integer $is_deleted
 * @property integer $is_external
 * @property integer $cloned_id
 * @property string $create_date
 *
 * The followings are the available model relations:
 * @property Tags[] $tags
 * @property Platforms[] $platforms
 * @property News $news
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 */
class Teasers extends CActiveRecord
{
    const CACHE_KEY = 'ttarget:teasers:%u';
    const CACHE_KEY_TTL = 300;

	public $platformIds;
    public $tagIds;

    private $cleanPlatformIds = array();
    private $cleanTagIds = array();

    public function updateLink()
    {
        // Добавляет ссылку на тизер в редис
        if (!$this->is_deleted && $this->isLastClone()) {
            RedisTeaser::instance()->addLink($this);
        }
    }

    /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Teasers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function afterFind()
    {
        if ($this->hasRelated('platforms')) $this->initRelated('platform');
        if ($this->hasRelated('tags')) $this->initRelated('tag');

        parent::afterFind();
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return self::getTableName();
    }

    /**
     * @return string Возвращает название таблицы отчета
     */
    protected static function getTableName()
    {
        return 'teasers';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $width  = Yii::app()->params->teaserImageWidth;
        $height = Yii::app()->params->teaserImageHeight;

		return array(
			array('title, news_id, picture', 'required'),
			array('is_active, is_external', 'numerical', 'integerOnly' => true),
			array('title', 'length', 'max'=>250),
            array('description', 'length', 'max' => 75),
			array('news_id, cloned_id', 'length', 'max'=>10),
//            array('picture', 'match', 'pattern' => '/^[a-zA-Z0-9_\-\.\(\) ]+$/', 'message' => 'Неправильное имя файла'),
            array('picture', 'length', 'max' => 250),
			array('platformIds, tagIds', 'type', 'type' => 'array'),
			array('id, title, picture, news_id, is_active, is_external, create_date', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'platforms' => array(self::MANY_MANY , 'Platforms', 'ct_except(teaser_id, platform_id)'),
            'tags' => array(self::MANY_MANY , 'Tags', 'teasers_tags(teaser_id, tag_id)'),
			'news' => array(self::BELONGS_TO, 'News', 'news_id', 'joinType' => 'INNER JOIN'),
		);
	}

    public function behaviors()
    {
        return array(
            'relations' => array(
                'class' => 'ext.yiiext.behaviors.EActiveRecordRelationBehavior'
            ),
            'dirty' => array(
                'class' => 'application.components.behaviors.DirtyObjectBehavior'
            ),
            'timestamps' => array(
                'class'                 => 'zii.behaviors.CTimestampBehavior',
                'createAttribute'       => 'create_date',
                'updateAttribute'       => null,
                'timestampExpression'   => new CDbExpression('now()'),
            ),
        );
    }

    protected function beforeSave()
    {
        if($this->platformIds !== null)
            $this->platforms = $this->platformIds;
        if($this->tagIds !== null)
            $this->tags = $this->tagIds;

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $itsActiveNews = News::model()->checkIsActive($this->news_id);

        if (!$itsActiveNews && $this->isBecameActive() && Campaigns::model()->checkIsDailyLimitExceeded($this->news->campaign_id)){
            // задача на добавление на завтра, если днейвной лимит исчерпан
            Yii::app()->resque->enqueueJobAt(strtotime('tomorrow'), 'app', 'TeaserAddToRedisJob', array('teaser_id' => $this->id));
        }elseif ($itsActiveNews && $this->isBecameActive()) {
            // создаем задание на добавление в редис
            Yii::app()->resque->createJob('app', 'TeaserAddToRedisJob', array('teaser_id' => $this->id));
        } elseif ($itsActiveNews && $this->isBecameNotActive()) {
            // Cоздаем задание на удаление из redis
            Yii::app()->resque->createJob('app', 'TeaserDelFromRedisJob', array(
                'teaser_id' => $this->id
            ));

        } elseif ($this->is_deleted){
            // Cоздаем задание на удаление из БД
            Yii::app()->resque->createJob('app', 'TeaserDelFromDbJob', array(
                'teaser_id' => $this->id,
                'cities' => Cities::model()->getAllByCampaignId($this->news->campaign_id),
                'countries' => Countries::model()->getAllCodesCampaignId($this->news->campaign_id)
            ));

        } elseif ($itsActiveNews && $this->is_active && !$this->getIsNewRecord()) {
            list($added_platforms, $excepted_platforms) = $this->_getExceptedAndAddedPlatforms();
            $newTagsCount = null;
            if($this->cleanTagIds != $this->tagIds){
                $newTagsCount = count($this->tags);
            }
            // Cоздаем задание на апдейт данных тизера в redis
            Yii::app()->resque->createJob('app', 'TeaserUpdateInRedisJob', array(
                'teaser_id'             => $this->id,
                'clean_attributes'      => $this->dirty->getCleanAttributes(),
                'excepted_platforms'    => $excepted_platforms,
                'added_platforms'       => $added_platforms,
                'new_tags_count'        => $newTagsCount
            ));
        }

        if ($this->dirty->isDirty()) {
            Yii::app()->cache->delete(sprintf(self::CACHE_KEY, $this->id));
        }

        $this->updateLink();

        $this->deleteOldPicture();
        parent::afterSave();
    }


    /**
     * Расчитывает изменения в платформах тизера, учитывая теги и исключенные платформы
     *
     * @return array
     */
    protected function _getExceptedAndAddedPlatforms()
    {
        if(!$this->hasRelated('tags')){
            $this->getRelated('tags');
            $this->initRelated('tag');
        }

        $cleanPlatforms = Platforms::model()->getAllActiveByTagIds($this->cleanTagIds);
        $cleanPlatforms = array_diff($cleanPlatforms, $this->cleanPlatformIds);
        $platforms = Platforms::model()->getAllActiveByTagIds($this->tagIds);
        $platforms = array_diff($platforms, $this->platformIds);

        $excepted = array_diff($cleanPlatforms, $platforms);
        $added = array_diff($platforms, $cleanPlatforms);

        return array(array_values($added), array_values($excepted));
    }

    private function initRelated($name){
        $relName = $name.'s';
        $idsName = $name.'Ids';
        $cleanName = 'clean'.ucfirst($name).'Ids';
        $this->$idsName = array();
        foreach ($this->$relName as $n => $service)
            array_push($this->$idsName, $service->id);
        $this->$cleanName = $this->$idsName;
    }

    /**
     * При удалении тизера, удаляем его изображение
     */
    protected function afterDelete()
    {
        $filePath = Yii::app()->params['imageBasePath'] . DIRECTORY_SEPARATOR . $this->picture;
        $teaser = Teasers::model()->findByAttributes(array('picture' => $this->picture), 'id != :id', array(':id' => $this->id));
        if ($teaser === null && is_file($filePath)) {
            unlink($filePath);
        }

        parent::afterDelete();
    }

    /**
     * @return Teasers Именованная группа для выборки не удаленных тизеров
     */
    public function notDeleted()
    {
        $alias = $this->getTableAlias(false,false);
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias . '.is_deleted = 0',
        ));

        return $this;
    }

    /**
     * @return Teasers Именованная группа для выборки активных тизеров
     */
    public function active()
    {
        $alias = $this->getTableAlias(false,false);
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias . '.is_active = 1',
        ));

        return $this;
    }

    /**
     * Удаляет старое изображение
     */
    private function deleteOldPicture()
    {
        if (!$this->getIsNewRecord() && $this->dirty->isAttributeChanged('picture')) {
            $oldFilePath = Yii::app()->params['imageBasePath'] . DIRECTORY_SEPARATOR . $this->dirty->getCleanAttribute('picture');
            if (is_file($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
    }

    /**
     * @return bool Возвращает true, если тизер стал активным
     */
    private function isBecameActive()
    {
        return ($this->getIsNewRecord() && $this->is_active) ||
               ($this->is_active && $this->dirty->isAttributeChanged('is_active'));
    }

    /**
     * @return bool Возвращает true, если тизер стал неактивен
     */
    private function isBecameNotActive()
    {
        return !$this->getIsNewRecord() && !$this->is_active && $this->dirty->isAttributeChanged('is_active');
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Заголовок',
            'description' => 'Описание',
			'picture' => 'Картинка',
			'news_id' => 'Новость',
			'is_active' => 'Активен',
            'is_external' => 'Внешняя сеть',
		);
	}

	public function getParents($nid){
		$row = Yii::app()->db
					  ->createCommand()
					  ->select('news.name, campaigns.name as cname,campaigns.id as cid, users.id as uid, users.login')
					  ->from('news')
					  ->where('news.id=:id', array(':id'=>$nid))
					  ->join('campaigns', 'campaigns.id = news.campaign_id')//;echo $row->getText();
					  ->join('users', 'campaigns.client_id = users.id')//;echo $row->getText();
					  ->queryRow();
		return $row;
	}

    /**
     * Возвращает полный урл к изображению тизера
     *
     * @return string
     */
    public function getImageUrl()
    {
        return Yii::app()->params['teaserImageBaseUrl'] . '/' . $this->picture;
    }

    /**
     * @return string Возвращает отрендеренный тизер
     */
    public function render()
    {
        return TeasersRenderer::instance()->render($this);
    }

    /**
     * Возвращает идентификаторы активных тизеров для новости
     *
     * @param int $news_id
     *
     * @return array
     */
    public function getAllActiveByNewsId($news_id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('id');
        $command->from($this->tableName());
        $command->andWhere('news_id = :news_id', array('news_id' => $news_id));
        $command->andWhere('is_active = 1 AND is_deleted = 0');

        return $command->queryColumn();
    }

    /**
     * Возвращает все тизеры по переданными идентификаторам
     * с названиями и урлами новостей, а также кампаний
     *
     * @param array $ids
     * @param bool $ctExceptPlatformId
     *
     * @return array
     */
    public function getAllByIds(array $ids, $ctExceptPlatformId = false)
    {
        $command = $this->getDbConnection()->createCommand();
        if($ctExceptPlatformId == false){
            $command->select('t.*, n.name as news_name, n.url as news_url, n.campaign_id, c.name as campaign_name, c.is_active as campaign_is_active');
        }else{
            $command->select('t.*, n.name as news_name, n.url as news_url, n.campaign_id, c.name as campaign_name, c.is_active as campaign_is_active, ISNULL(ce.teaser_id) AS ct_except_is_active');
            $command->leftJoin('ct_except ce', 'ce.teaser_id = t.id AND ce.platform_id = :platform_id', array(':platform_id' => $ctExceptPlatformId));
        }
        $command->from($this->tableName() . ' t');
        $command->join(News::model()->tableName() . ' n', 't.news_id = n.id');
        $command->join(Campaigns::model()->tableName() . ' c', 'n.campaign_id = c.id');
        $command->where('t.id IN (' . implode(',', $ids) . ')');
        $command->andWhere('c.is_deleted = 0 AND n.is_deleted = 0 AND t.is_deleted = 0');
        $command->order('t.title, n.name, c.name');

        return $command->queryAll();
    }

    /**
     * Возвращает названия всех тизеров по переданным идентификаторам
     *
     * @param array $ids
     *
     * @return array
     */
    public function getTitlesByIds(array $ids)
    {
        if (empty($ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select(array('id', 'title'));
        $command->from($this->tableName());
        $command->where('id IN ('. implode(', ', $ids) . ')');

        $result = array();
        foreach($command->queryAll() as $dbRow){
            $result[$dbRow['id']] = $dbRow['title'];
        }

        return $result;
    }

    /**
     * Возвращает именна тегов
     *
     * @return array
     */
    public function getTagNames()
    {
        $names = array();
        foreach($this->tags as $tag) {
            $names[] = $tag->name;
        }
        return $names;
    }

    /**
     * Загружает имена тегов по индентификаторам тизеров
     *
     * @param array $ids
     * @return array
     */
    public function getTagNamesByIds(array $ids)
    {
        if(empty($ids)) return array();

        $command = $this->getDbConnection()->createCommand()
            ->select('tt.teaser_id, t.name')
            ->from('teasers_tags tt')
            ->join('tags t', 't.id = tt.tag_id')
            ->where('tt.teaser_id in ('.implode(',', $ids).')');
        $result = array();
        foreach($command->queryAll() as $tag){
            $result[$tag['teaser_id']][] = $tag['name'];
        }
        return $result;
    }

    /**
     * Возвращает данные тизера
     *
     * Если данные закэшированы, тогда они берутся из redis
     *
     * @param $teaser_id
     * @return array
     * @throws CException
     */
    public static function getById($teaser_id)
    {
        $teaser = Yii::app()->cache->get(sprintf(self::CACHE_KEY, $teaser_id));
        if (!$teaser) {

            $teaser = self::model()->notDeleted()->findByPk($teaser_id);
            if($teaser === null){
                throw new CException('Cant find teaser by id: '.$teaser_id);
            }
            
            Yii::app()->cache->set(
                sprintf(self::CACHE_KEY, $teaser_id),
                $teaser->getAttributes(),
                self::CACHE_KEY_TTL
            );
        }

        return $teaser;
    }

    /**
     * Возвращает зашифрованную ссылку тизера
     *
     * @return bool|string Возвращает шифрованный урл тизера
     */
    public function getEncryptedLink()
    {
        if(isset($this->cloned_id)){
            return Crypt::encryptUrlComponent($this->cloned_id);
        }else{
            return Crypt::encryptUrlComponent($this->id);
        }
    }

    /**
     * Возвращает абсолютный урл тизера
     *
     * @return string
     */
    public function getEncryptedAbsoluteUrl()
    {
        if(isset($this->cloned_id)){
            return TeasersLink::instance()->getAbsoluteUrl($this->cloned_id);
        }else{
            return TeasersLink::instance()->getAbsoluteUrl($this->id);
        }
    }

    /**
     * Ищет тизеры по идентификатору новости и платформы
     *
     * @param int $newsId
     * @param int $platformId
     *
     * @return array
     */
    public function findAllByNewsIdAndPlatformId($newsId, $platformId)
    {
        $alias = $this->getTableAlias(false, false);

        $criteria = new CDbCriteria();
        $criteria->join = "LEFT JOIN ct_except cte ON {$alias}.id = cte.teaser_id AND cte.platform_id = {$platformId} ";
        $criteria->join.= "JOIN platforms_tags pt ON pt.platform_id = {$platformId} ";
        $criteria->join.= "LEFT JOIN teasers_tags tt ON tt.teaser_id = t.id AND tt.tag_id = pt.tag_id";
        $criteria->addCondition("cte.platform_id IS NULL");
        $criteria->addCondition("{$alias}.news_id = {$newsId}");
        $criteria->addCondition("tt.teaser_id IS NOT NULL");
        $criteria->group = "{$alias}.id";

        $this->getDbCriteria()->mergeWith($criteria);
        return $this->findAll();
    }

    protected function isLastClone()
    {
        if(!empty($this->cloned_id)){
            $last = Teasers::model()->findByAttributes(array('cloned_id' => $this->cloned_id), array('order' => 'id DESC'));
            if($last->id == $this->id) return true;
        }else{
            $last = Teasers::model()->findByAttributes(array('cloned_id' => $this->id));
            if(is_null($last)) return true;
        }
        return false;
    }

    /**
     * Возвращает статистические данные участвующие в расчете веса тизера в выдаче
     *
     * @return array
     */
    public function getStats()
    {
        $data = ReportDailyByTeaserAndPlatform::model()->getTotalsByTeaserId($this->id);
        return array(
            'shows' => $data['shows'],
            'clicks' => $data['clicks'],
            'tagsCount' => count($this->tags),
        );
    }
}