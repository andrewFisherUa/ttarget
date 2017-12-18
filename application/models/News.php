<?php

/**
 * This is the model class for table "news".
 *
 * The followings are the available columns in table 'news':
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $url
 * @property integer $is_active
 * @property integer $failures
 * @property integer $quality
 * @property integer $last_quality_week
 * @property string $campaign_id
 * @property integer $is_deleted
 * @property integer $shows
 * @property integer $clicks
 * @property integer $fake_clicks
 * @property integer $clicks_without_externals
 * @property integer $url_type
 * @property integer $url_status
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 * @property Teasers[] $teasers
 * @property Teasers[] $activeTeasers
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 */
class News extends CActiveRecord
{
    const CACHE_KEY = 'ttarget:news:%u';
    const CACHE_KEY_TTL = 300;

    const URL_TYPE_NORMAL = 0;
    const URL_TYPE_BROKEN = 1;
    const URL_TYPE_SKIP_PARAMS = 2;

	public $ids;
	public $tname;
	public $cname;
	public $clicks_count;
	public $teasers_count;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return News the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
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
        return 'news';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, url, campaign_id', 'required'),
			array('is_active, url_type, failures', 'numerical', 'integerOnly'=>true),
			array('name, description, quality', 'length', 'max'=>250),
            array('url', 'length', 'max' => 512),
			array('campaign_id', 'length', 'max'=>10),
            /* validateIDN отрезает пробелы в конце строки, тут могут возникнуть проблемы */
			array('url', 'url', 'defaultScheme' => 'http://', 'validateIDN' => true),
//            array('url', 'filter', 'filter'=>'trim'),
			array('id, name, teasers_count, quality, create_date, description, url, is_active, url_type, failures, campaign_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'campaign'      => array(self::BELONGS_TO, 'Campaigns', 'campaign_id', 'joinType' => 'INNER JOIN'),
			'teasers'       => array(self::HAS_MANY, 'Teasers', 'news_id'),
            'activeTeasers' => array(self::HAS_MANY, 'Teasers', 'news_id', 'condition' => 'activeTeasers.is_active = 1 AND activeTeasers.is_deleted = 0'),
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

    protected function beforeValidate()
    {
        $this->url = IDN::encodeURL($this->url);
        return parent::beforeValidate();
    }

    protected function afterSave()
    {
        $itsActiveCampaign = Campaigns::model()->checkIsActive($this->campaign_id);

        if ($itsActiveCampaign && $this->isBecameActive()) {

            $this->addToRedis();

        } elseif ($itsActiveCampaign && $this->isBecameNotActive()) {

            $this->deleteFromRedis();

        } elseif ($this->is_deleted) {
            // Cоздаем задание на удаление из БД
            $this->deleteTeasers();
            Yii::app()->resque->createJob('app', 'NewsDelFromDbJob', array('news_id' => $this->id));

        } elseif ($itsActiveCampaign && !$this->getIsNewRecord()) {
            // Cоздаем задание на апдейт данных новости в redis при сохранении новости
            Yii::app()->resque->createJob('app', 'NewsUpdateInRedisJob', array(
                'news_id'               => $this->id,
                'clean_attributes'      => $this->dirty->getCleanAttributes(),
            ));
        }

        if ($this->dirty->isAttributeDirty('url') || $this->dirty->isAttributeDirty('url_type')) {
            foreach($this->teasers as $teaser){
                $teaser->updateLink();
            }
            if ($itsActiveCampaign && $this->is_active) {
                Yii::app()->resque->createJob('stat', 'CampaignCheckNewsUrlJob', array(
                    'campaign_id' => $this->campaign_id,
                    'news_id' => $this->id
                ));
            }
        }

        if ($this->dirty->isDirty()) {
            Yii::app()->cache->delete(sprintf(self::CACHE_KEY, $this->id));
        }

        parent::afterSave();
    }

    /**
     * Создаем задание на добавление всех активных тизеров в редис
     */
    public function addToRedis()
    {
        $activeTeasers = Teasers::model()->getAllActiveByNewsId($this->id);
        foreach ($activeTeasers as $teaser_id) {
            Yii::app()->resque->createJob('app', 'TeaserAddToRedisJob', array('teaser_id' => $teaser_id));
        }
    }

    /**
     * Создаем задание на добавление всех активных тизеров в редис на следующий день
     */
    public function addToRedisTommorow()
    {
        $activeTeasers = Teasers::model()->getAllActiveByNewsId($this->id);
        foreach ($activeTeasers as $teaser_id) {
            Yii::app()->resque->enqueueJobAt(strtotime('tomorrow'), 'app', 'TeaserAddToRedisJob', array('teaser_id' => $teaser_id));
        }
    }

    /**
     * Cоздаем задание на удаление данных новости из redis
     */
    public function deleteFromRedis()
    {
        $activeTeasers = Teasers::model()->getAllActiveByNewsId($this->id);
        foreach ($activeTeasers as $teaser_id) {
            Yii::app()->resque->createJob('app', 'TeaserDelFromRedisJob', array(
                'teaser_id' => $teaser_id
            ));
        }
    }

    /**
     * @return bool Возвращает true, если новость стала активна
     */
    private function isBecameActive()
    {
        return ($this->getIsNewRecord() && $this->is_active) ||
               ($this->is_active && $this->dirty->isAttributeChanged('is_active'));
    }

    /**
     * @return bool Возвращает true, если новость стала неактивной
     */
    private function isBecameNotActive()
    {
        return !$this->getIsNewRecord() && !$this->is_active && $this->dirty->isAttributeChanged('is_active');
    }

    /**
     * Помечает активные тизеры новости как удаленные
     */
    public function deleteTeasers()
    {
        foreach ($this->getRelated('teasers') as $teaser) {
            $teaser->is_deleted = 1;
            $teaser->save(false, array('is_deleted'));
        }
    }

    /**
     * @return News Именованная группа для выборки не удаленных новостей
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
     * @return News Именованная группа для выборки активных новостей
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
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название',
			'description' => 'Описание',
			'url' => 'Url',
			'is_active' => 'Активна',
			'failures' => 'Количество Отказов',
			'campaign_id' => 'Рекламная кампания',
			'create_date' => 'Дата создания',
			'quality' => 'Качество',
            'url_type' => 'Тип ссылки'
		);
	}

	public function getParents($cid){
		$row = Yii::app()->db
					  ->createCommand()
					  ->select('campaigns.name, users.id as uid, users.login')
					  ->from('campaigns')
					  ->where('campaigns.id=:id', array(':id'=>$cid))
					  ->join('users', 'campaigns.client_id = users.id')//;echo $row->getText();
					  ->queryRow();
		return $row;
	}

    /**
     * Проверяет является ли новость активной
     *
     * @param int $id
     *
     * @return bool
     */
    public function checkIsActive($id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('campaign_id');
        $command->from($this->tableName());
        $command->andWhere('id = :id', array(':id' => $id));
        $command->andWhere('is_active = 1');

        if (!($campaignId = $command->queryScalar())) {
            return false;
        }

        return Campaigns::model()->checkIsActive($campaignId);
    }

    /**
     * @return int Возвращает суммарное количество кликов, с учетом поддельных
     */
    public function totalClicks()
    {
        return $this->clicks + $this->fake_clicks;
    }

    /**
     * Возвращает sql-запрос для обновления счетчика показов
     *
     * @param int $news_id
     * @param int $amount
     *
     * @return string
     */
    public static function createUpdateShowsSql($news_id, $amount)
    {
        $sql  = "UPDATE `" . self::getTableName() . "` ";
        $sql .= "SET shows = shows + {$amount} ";
        $sql .= "WHERE id = {$news_id} LIMIT 1;";

        return $sql;
    }

    /**
     * Возвращает sql-запрос для обновления счетчика кликов
     *
     * @param int $news_id
     * @param int $amount
     * @param bool $is_external_platform
     *
     * @return string
     */
    public static function createUpdateClicksSql($news_id, $amount, $is_external_platform)
    {
        $sql  = "UPDATE `" . self::getTableName() . "` ";
        $sql .= "SET clicks = clicks + {$amount} ";
        if(!$is_external_platform){
            $sql .= ", clicks_without_externals = clicks_without_externals + {$amount} ";
        }
        $sql .= "WHERE id = {$news_id} LIMIT 1;";

        return $sql;
    }

    /**
     * Возвращает данные новости
     *
     * Если данные закэшированы, тогда они берутся из redis
     *
     * @param $news_id
     * @return array
     * @throws CException
     */
    public static function getById($news_id)
    {
        $news = Yii::app()->cache->get(sprintf(self::CACHE_KEY, $news_id));
        if (!$news) {
        	$news = self::model()->notDeleted()->findByPk($news_id);
            if($news === null){
                throw new CException('Cant find news by id: '.$news_id);
            }
            Yii::app()->cache->set(sprintf(self::CACHE_KEY, $news_id), $news->getAttributes(), self::CACHE_KEY_TTL);
        }

        return $news;
    }

    public function getAvailableUrlTypes()
    {
        return array(
            self::URL_TYPE_NORMAL => 'Нормальная',
            self::URL_TYPE_BROKEN => 'Использовать разделитель "&" вместо "?"',
            self::URL_TYPE_SKIP_PARAMS => 'Без параметров',
        );
    }

    /**
     * Возвращает обработанный урл новости
     * @param array $params
     * @return string
     */
    public function buildUrl($params = array(), $argsPlaceholder = true){
        $url = $this->url;
        if($this->url_type != self::URL_TYPE_SKIP_PARAMS) {
            $anchor = '';
            if(($anchorPos = strpos($this->url, '#')) !== false){
                $anchor = substr($this->url, $anchorPos);
                $url = substr($this->url, 0, $anchorPos);
            }
            if (strpos($url, '?') === false && $this->url_type == self::URL_TYPE_NORMAL) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= 'utm_source=Ttarget&utm_medium=' . $this->campaign_id
                . '&utm_content=' . $this->id;
            foreach($params as $k => $v){
                $url .= '&'.$k.'=' . $v;
            }
            if($argsPlaceholder){
                $url .= '{args}';
            }
            $url .= $anchor;
        }
        return $url;
    }
}