<?php

/**
 * This is the model class for table "platforms".
 *
 * The followings are the available columns in table 'platforms':
 * @property string $id
 * @property string $server
 * @property integer $is_active
 * @property integer $is_external
 * @property integer $is_deleted
 * @property string[] $hosts
 * @property string $currency
 * @property integer $user_id
 * @property integer $is_vat
 * @property float $billing_debit
 * @property float $billing_paid
 * @property string $tag_names
 * @property string $last_request_date
 * @property string $lr_notify_date
 * @property integeer $visits_count
 * @property string $url
 *
 * The followings are the available model relations:
 * @property Teasers[] $teasers
 * @property Tags[] $tags
 * @property PlatformsCpc[] $cpcs
 * @property Users $user
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 *
 * @method Platforms findByPk()
 */
class Platforms extends CActiveRecord
{
    /**
     * Ключ в кэше, с данными площадки
     */
    const CACHE_KEY = 'ttarget:platform:%u:data';

    /**
     * Идентификатор площадки, на которую переносим статистику с удаляемой площадки
     */
    const DELETED_PLATFORM_ID = 23;

    public $tagIds = array();
    public $cleanTagIds = array();

	public $tname;

    public $is_code_active;

    public $daily_profit;

    public $total_debit;

    public function afterFind()
    {

        if ($this->hasRelated('tags') && !empty($this->tags))
        {
            foreach ($this->tags as $n => $service)
                $this->tagIds[] = $service->id;
            $this->cleanTagIds = $this->tagIds;
        }

        parent::afterFind();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Platforms the static model class
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
        return 'platforms';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('server, currency', 'required'),
			array('is_active, is_external, is_vat', 'numerical', 'integerOnly' => true),
			array('server', 'length', 'max'=>250),
            array('currency', 'in', 'range' => array_keys(PlatformsCpc::getCurrencies())),
            array('hosts', 'filter', 'filter' => function($hosts) {
                preg_match_all('@(?:https?://)?(?:www\\.)?([\\w-\.]+)@i', strtolower($hosts), $result);
                return implode("\n", $result[1]);
            }),
			array('teasers', 'safe'),
			array('visits_count', 'numerical', 'integerOnly' => true),
            array('tagIds', 'type', 'type' => 'array'),
            array('url', 'length', 'max' => 2048),
            array('url', 'url', 'defaultScheme' => 'http://', 'validateIDN' => true),
			array('id, server, is_active, is_external, user_id, visits_count', 'safe', 'on'=>'search'),
            // sign in
            array('url, tagIds', 'required', 'on' => 'signin'),
            array('is_active, is_external, is_vat, hosts, visits_count', 'unsafe', 'on' => 'signin')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'teasers'   => array(self::MANY_MANY, 'Teasers', '{{ct_except}}(platform_id, teaser_id)'),
            'tags'      => array(self::MANY_MANY , 'Tags', 'platforms_tags(platform_id, tag_id)'),
            'cpcs'      => array(self::HAS_MANY, 'PlatformsCpcs', 'platform_id'),
            'user'      => array(self::BELONGS_TO, 'Users', 'user_id'),
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
                'createAttribute'       => 'created',
                'updateAttribute'       => null,
                'timestampExpression'   => new CDbExpression('now()'),
            ),
        );
    }

    protected function beforeSave()
    {
        $this->tags = $this->tagIds;
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if ($this->isBecameActive()) {
            // создаем задание на добавление в редис
            Yii::app()->resque->createJob('app', 'PlatformAddToRedisJob', array('platform_id' => $this->id));

        } elseif ($this->isBecameNotActive() || $this->is_deleted) {
            // Cоздаем задание на удаление из redis
            Yii::app()->resque->createJob('app', 'PlatformDelFromRedisJob', array('platform_id' => $this->id));

        } elseif (!$this->getIsNewRecord()) {
            // Cоздаем задание на апдейт данных платформы в redis
            Yii::app()->resque->createJob('app', 'PlatformUpdateInRedisJob', array(
                'platform_id'       => $this->id,
                'clean_attributes'  => $this->dirty->getCleanAttributes(),
                'tagIds'            => $this->tagIds,
                'cleanTagIds'       => $this->cleanTagIds
            ));
        }

        if ($this->dirty->isDirty()) {
            Yii::app()->cache->delete(sprintf(self::CACHE_KEY, $this->id));
        }

        parent::afterSave();
    }

    /**
     * @return bool Возвращает true, если платформа стала активна
     */
    private function isBecameActive()
    {
        return ($this->getIsNewRecord() && $this->is_active) ||
        ($this->is_active && $this->dirty->isAttributeChanged('is_active'));
    }

    /**
     * @return bool Возвращает true, если платформа сталв неактивена
     */
    private function isBecameNotActive()
    {
        return !$this->getIsNewRecord() && !$this->is_active && $this->dirty->isAttributeChanged('is_active');
    }

    /**
     * Возвращает список хостов площадки в виде массива
     *
     * @param string hosts null
     *
     * @return array
     */
    public function getHostsAsArray($hosts = '')
    {
        $hosts = $hosts ?: $this->hosts;
        return explode("\n", $hosts);
    }

    public function getHostsDecoded()
    {
        $hosts = $this->getHostsAsArray();
        foreach($hosts as &$host){
            $host = IDN::decodeHost($host);
        }
        return implode("\n", $hosts);
    }

    protected function beforeValidate()
    {
        $hosts = $this->getHostsAsArray();
        foreach($hosts as &$host){
            $host = IDN::encodeHost($host);
        }
        $this->hosts = implode("\n", $hosts);

        $this->url = IDN::encodeURL($this->url);

        return parent::beforeValidate();
    }

    /**
     * @return Platforms Именованная группа для выборки не удаленных площадок
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
     * @return Platforms Именованная группа для выборки активных площадок
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
			'server' => 'Название сайта',
			'is_active' => 'Активна',
			'is_external' => 'Внешняя сеть',
            'hosts' => 'Url-адреса серверов площадки',
            'currency' => 'Валюта',
            'is_vat' => 'НДС',
			'visits_count' => 'Посещаемость',
            'url' => 'URL сайта',
            'tagIds' => 'Тематика сайта'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search( $filter = array(), $pageSize = 10 )
	{
        $dataProvider = new CActiveDataProvider($this, array(
            'pagination' => isset($pageSize) ? array('pageSize'=>$pageSize) : false,
            'sort'=>array(
                'attributes'=>array(
                    'daily_profit' => array('asc'=>'daily_profit','desc'=>'daily_profit DESC',),
                    'total_debit' => array('asc' => 'total_debit', 'desc' => 'total_debit DESC'),
                    'tag_names' => array('asc'=>'tag_names','desc'=>'tag_names DESC'),
                    'is_code_active' => array('asc'=>'is_code_active','desc'=>'is_code_active DESC'),
                    '*',
                ),
            )
        ));

		$criteria=new CDbCriteria;

		$select = array(
            "t.*",
//            "GROUP_CONCAT(tags.name SEPARATOR ' | ') AS tag_names",
            "IF(t.is_active = 1 AND t.last_request_date > '".date('Y-m-d H:i:s', time() - Yii::app()->params->PlatformRequestAlertTime)."'"
                .",1,0) AS is_code_active",
        );

        if(
            $dataProvider->getSort()->getDirection('daily_profit') !== null
            || $dataProvider->getSort()->getDirection('total_debit') !== null
        ) {
            $criteria->join = "LEFT JOIN report_daily_by_platform r ON r.platform_id = t.id "
                . "LEFT JOIN platforms_cpc cpc ON "
                . "r.platform_id = cpc.platform_id "
                . "AND cpc.date = (" . PlatformsCpc::getMaxCpcSql() . ")";

            $select[] = "SUM(IF(r.`date` = CURDATE(), IFNULL(r.clicks,0) * IFNULL(cpc.cost, 0), 0)) AS daily_profit";
            $select[] = "SUM(IFNULL(r.clicks,0) * IFNULL(cpc.cost, 0)) - "
                ."(SELECT IFNULL(SUM(`sum`), 0) FROM `billing_income` b WHERE b.`source_type` = '".BillingIncome::SOURCE_TYPE_PLATFORM."' AND b.`source_id` = t.id) AS total_debit";
        }

        $criteria->select = $select;

		$criteria->compare('id',$this->id,true);
		
		if(isset($filter['is_active'])){
			if($filter['is_active'] == 1 ){
				$criteria->addCondition('is_active = 1');
			} elseif($filter['is_active'] == 2) {
				$criteria->addCondition('is_active = 0');
			}
		}
		
		$criteria->compare('is_external',$this->is_external);
        $criteria->compare('user_id',$this->user_id);
		
		$criteria->addCondition('t.id <> 23');
		$criteria->addCondition('t.is_deleted = 0');
		
		if(!empty($filter['tag_id'])){
			$command = Yii::app()->db->createCommand('SELECT DISTINCT platform_id FROM platforms_tags WHERE tag_id = :tag_id');
			$_tags = $command->queryColumn(array(':tag_id' => $filter['tag_id']));
			$criteria->addInCondition('t.id', $_tags);
		}
        
	    if( !empty($filter['period']) && $filter['period'] != 'all'){
        	$criteria->addCondition("created >= '{$filter['dateFrom']}' AND created <= '{$filter['dateTo']}'");
        }
        
//        $criteria->with = array('tags', 'user');
//        $criteria->together = false;
        $criteria->group = 't.id';

        
        if(!empty($this->server)){
            $searchCriteria = new CDbCriteria();
            $searchCriteria->compare('t.server',$this->server,true,'OR');
            $searchCriteria->compare('t.id',$this->server,false,'OR');
            $criteria->mergeWith($searchCriteria);
        }

        $dataProvider->setCriteria($criteria);
		return $dataProvider;
	}

    /**
     * Возвращает идентификаторы всех активных площадок по заданному сегменту
     *
     * @param int[] $tags
     *
     * @return array
     */
    public function getAllActiveByTagIds($tags)
    {
        if(empty($tags))
            return array();

        $command = $this->getDbConnection()->createCommand();
        $command->selectDistinct('id');
        $command->from($this->tableName());
        $command->join('platforms_tags pt', 'pt.platform_id = id');
        $command->where('is_active = 1 AND is_external = 0 AND is_deleted = 0 AND id <> '.self::DELETED_PLATFORM_ID.' AND pt.tag_id in('.implode(',',$tags).')');

        return $command->queryColumn();
    }

    /**
     * Возвращает идентификаторы всех активных площадок, на которых может быть показа тизер
     *
     * @param int $teaser_id
     * @param bool $withExternal
     *
     * @return array
     */
    public function getAllActiveByTeaserId($teaser_id, $withExternal = false)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->selectDistinct('p.id');
        $command->from($this->tableName() . ' p');
        $command->leftJoin('ct_except e', 'p.id = e.platform_id AND e.teaser_id = :t_id', array('t_id' => $teaser_id));
        $command->join('platforms_tags pt', 'pt.platform_id = p.id');
        $command->leftJoin('teasers_tags tt', 'tt.teaser_id = :t_id AND tt.tag_id = pt.tag_id', array('t_id' => $teaser_id));
        $command->where('p.is_active = 1 AND p.is_deleted = 0 '.($withExternal ? '' : 'AND p.is_external = 0 ').'AND p.id <> 23 AND e.platform_id IS NULL');
        $command->andWhere('(tt.teaser_id IS NOT NULL'.($withExternal ? ' OR p.is_external = 1' : '').')');

        return $command->queryColumn();
    }

    /**
     * Возвращает идентификаторы всех активных площадок, на которых может быть показана новость
     *
     * @todo не учитывает теги тизеров, и поэтому может вернут лишние. старый алгоритм ротации.
     * @param int $news_id
     *
     * @return array
     */
    public function getAllActiveByNewsId($news_id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->selectDistinct('p.id')
            ->from($this->tableName() . ' p')
            ->leftJoin('teasers t', 't.news_id = :news_id AND t.is_active = 1 AND t.is_deleted = 0', array(':news_id' => $news_id))
            ->leftJoin('ct_except e', 'e.platform_id = p.id AND e.teaser_id = t.id')
            ->where('e.teaser_id IS NULL AND p.is_active = 1 AND p.is_deleted = 0 AND p.id <> 23 AND p.is_external = 0');
        return $command->queryColumn();
    }

    /**
     * Возвращает идентификаторы всех активных площадок, на которых может быть показана РК
     *
     * @param int $campaign_id
     * @param bool $withExternal
     *
     * @return array
     */
    public function getAllActiveByCampaignId($campaign_id, $withExternal = false, $activeOnly = true)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->selectDistinct('p.id');
        $command->from($this->tableName() . ' p');
        $command->join(
            'news n',
            'n.campaign_id = :campaign_id'.($activeOnly ? ' AND n.is_active = 1' : ''),
            array(':campaign_id' => $campaign_id)
        );
        $command->join('teasers t', 't.news_id = n.id'.($activeOnly ? ' AND t.is_active = 1' : ''));
        $command->leftJoin('ct_except e', 'p.id = e.platform_id AND e.teaser_id = t.id');
        $command->leftJoin('platforms_tags pt', 'pt.platform_id = p.id');
        $command->leftJoin('teasers_tags tt', 'tt.teaser_id = t.id AND tt.tag_id = pt.tag_id');
        $command->where('p.is_active = 1 AND p.is_deleted = 0 '.($withExternal ? '' : 'AND p.is_external = 0 ').'AND p.id <> 23 AND e.platform_id IS NULL');
        $command->andWhere('(tt.teaser_id IS NOT NULL'.($withExternal ? ' OR p.is_external = 1' : '').')');

        return $command->queryColumn();
    }

    /**
     * Возвращает данные платформы
     *
     * Если данные закэшированы, тогда они берутся из redis
     *
     * @param $platform_id
     * @return array
     * @throws CException
     */
    public static function getById($platform_id)
    {
        $platform = Yii::app()->cache->get(sprintf(self::CACHE_KEY, $platform_id));
        if (!$platform) {
            $platform = self::model()->notDeleted()->findByPk($platform_id);
            if ($platform === null) {
                throw new CException('Cant get platform by id: '.$platform_id);
            }
            Yii::app()->cache->set(sprintf(self::CACHE_KEY, $platform_id), $platform->getAttributes());
        }

        return $platform;
    }

    public function getBilling_paid()
    {
        return round(BillingIncome::model()->getPaidByPlatform($this->id),2);
    }

    public function getBilling_debit()
    {
        if($this->total_debit === null){
            $this->total_debit = round(
                BillingIncome::model()->getProfitByPlatform($this->id)
                - BillingIncome::model()->getPaidByPlatform($this->id)
                ,2
            );
        }
        return sprintf('%.2f', $this->total_debit);
    }

    public function getLink()
    {
        return '.'.$this->id.'.'.$this->getEncryptedId();
    }

    public function getEncryptedId()
    {
        return Crypt::encryptUrlComponent($this->id);
    }

    /**
     * @return Platforms Именованная группа для выборки площадок для вывода списком
     */
    public function printable()
    {
        $alias = $this->getTableAlias(false,false);
        $this->notDeleted()->getDbCriteria()->mergeWith(array(
            'condition' => $alias . '.id <> :deleted',
            'params' => array(':deleted' => self::DELETED_PLATFORM_ID),
            'order' => 'server ASC'
        ));

        return $this;
    }

    /**
     * Возвращает названия всех платформ по переданным идентификаторам
     *
     * @param array $ids
     *
     * @return array
     */
    public function getServersByIds(array $ids)
    {
        if (empty($ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select(array('id', 'server'));
        $command->from($this->tableName());
        $command->where('id IN ('. implode(', ', $ids) . ')');

        $result = array();
        foreach($command->queryAll() as $dbRow){
            $result[$dbRow['id']] = $dbRow['server'];
        }

        return $result;
    }

    public function getDailyProfit()
    {
        if($this->daily_profit === null){
            $this->daily_profit = ReportDailyByPlatform::model()->getPriceSumByAttributes(array(
                'platform_id' => $this->id,
                'date' => date('Y-m-d'),
            ));
        }

        return sprintf('%.2f', $this->daily_profit);
    }

    public function getTag_names()
    {
        $names = array();
        foreach($this->tags as $t){
            $names[] = $t->name;
        }
        return implode(' | ', $names);
    }

    /**
     * Поиск платформ от которых не приходят запросы
     * @return Platforms[]
     */
    public function findAllWithRequestAlert()
    {
        return Platforms::model()->findAll(
            "last_request_date != lr_notify_date AND last_request_date < :date",
            array(':date' => date('Y-m-d H:i:s', time() - Yii::app()->params->PlatformRequestAlertTime))
        );
    }
}