<?php

/**
 * This is the model class for table "campaigns".
 *
 * The followings are the available columns in table 'campaigns':
 * @property string $id
 * @property integer $client_id
 * @property string $date_start
 * @property string $date_end
 * @property string $max_clicks
 * @property string $comment
 * @property string $name
 * @property integer $limit_per_day
 * @property integer $day_clicks
 * @property integer $is_active
 * @property integer $is_deleted
 * @property integer $fake_clicks
 * @property integer $clicks_without_externals
 * @property integer $is_notified
 * @property string $ga_access_token
 * @property integer $ga_profile_id
 * @property string $cost_type
 * @property string $actions_cost
 * @property integer $bounce_check
 * @property integer $bounce_rate_diff
 * @property integer $shows
 * @property integer $clicks
 * @property integer $actions
 * @property integer $declined_actions
 * @property integer $bounces
 *
 * @property integer[] $citiesIds
 * @property integer[] $countriesIds
 *
 * The followings are the available model relations:
 * @property Cities[] $cities
 * @property Countries[] $countries
 * @property Users  $client
 * @property News[] $news
 * @property News[] $activeNews
 * @property CampaignsActions[] $campaignsActions
 * @property Offers $offers
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 *
 * @method Campaigns findByPk()
 * @method Campaigns[] findAll()
 * @method Offers[] offers()
 *
 * @todo разобраться с fake_clicks и clicks_without_externals. Похоже в текущих реалиях они не нужны
 */
class Campaigns extends CActiveRecord
{
    const BOUNCE_NOTIFY_RATE = 10;
    
    const COST_TYPE_CLICK = 'click';
    const COST_TYPE_ACTION = 'action';
    const COST_TYPE_RTB = 'rtb';

    const RTB_COST_TYPE_CPM = 'cpm';
    const RTB_COST_TYPE_CPC = 'cpc';
    
	public $ids;
	public $days_left;
    public $clone_id;
    public $actions_cost;

    private $_citiesIds = null;
    private $_countriesIds = null;
    private $cleanCitiesIds = null;
    private $cleanCountriesIds = null;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var ReportDailyByCampaign
     */
    private $dayReport;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Campaigns the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * Сетеры/гетеры. Не загружает связи если к ним не обращаются, сохраняет clean атрибуты.
     */
    protected function getCitiesIds(){
        return $this->getIds('cities');
    }

    protected function setCitiesIds($value){
        $this->setIds('cities', $value);
    }

    protected function getCountriesIds(){
        return $this->getIds('countries');
    }

    protected function setCountriesIds($value){
        $this->setIds('countries', $value);
    }

    protected function setIds($attr, $value){
        if(empty($value)){
            $value = array();
        }elseif( !is_array($value) ){
            $value = explode(',', $value);
        }

        $attrCleanIds = 'clean'.ucfirst($attr).'Ids';
        $attrIds = '_'.$attr.'Ids';

        if(null === $this->$attrCleanIds){
            $this->$attrCleanIds = $this->getIds($attr);
        }

        $this->$attrIds = $value;
    }

    protected function getIds($attr)
    {
        $attrIds = '_'.$attr.'Ids';

        if(null === $this->$attrIds){
            $this->$attrIds = array();
            if(!empty($this->$attr)){
                foreach ($this->$attr as $model)
                    array_push($this->$attrIds, $model->id);
            }
        }
        return $this->$attrIds;
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
        return 'campaigns';
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('client_id, date_start, date_end, name, cost_type', 'required'),
			array('client_id, is_active, limit_per_day, day_clicks, clone_id, ga_profile_id', 'numerical', 'integerOnly' => true),
            array('client_id', 'exist', 'className' => 'Users', 'attributeName' => 'id', 'criteria' => array('condition' => 'is_deleted=0')),
			array('max_clicks', 'length', 'max' => 10),
            array('bounce_check, rtb_cost', 'numerical', 'integerOnly' => true, 'allowEmpty' => true),
            array('max_clicks', 'default', 'value' => 0),
			array('comment, name', 'length', 'max' => 250),
            array('ga_access_token', 'length', 'max' => 512),
			array('date_start, date_end', 'date', 'format' => 'yyyy-MM-dd'),
            array('citiesIds, countriesIds', 'type', 'type' => 'array'),
            array('cost_type', 'in', 'range' => array_keys($this->getAvailableCostTypes())),
            array('actions_cost', 'numerical', 'allowEmpty' => true, 'numberPattern' => '/^\d+(\.\d\d?)?$/'),
			array('track_js','default', 'value' => ''),
			array('rtb_url', 'url', 'allowEmpty' => true),
			array('rtb_cost_type', 'default', 'value' => self::RTB_COST_TYPE_CPM),
			array('id, rtb_url, rtb_cost, rtb_cost_type, track_js, client_id, name, days_left, limit_per_day, day_clicks, date_start, date_end, max_clicks, comment, is_active', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'cities'        => array(self::MANY_MANY , 'Cities', 'campaigns_cities(campaign_id, city_id)'),
            'countries'     => array(self::MANY_MANY , 'Countries', 'campaigns_countries(campaign_id, country_id)'),
            'client'        => array(self::BELONGS_TO, 'Users', 'client_id', 'joinType' => 'INNER JOIN'),
			'news'          => array(self::HAS_MANY, 'News', 'campaign_id'),
            'activeNews'    => array(self::HAS_MANY, 'News', 'campaign_id', 'condition' => 'activeNews.is_active = 1 AND activeNews.is_deleted = 0'),
            'campaignsActions'       => array(self::HAS_MANY, 'CampaignsActions', 'campaign_id', 'condition' => 'is_deleted = 0'),
			'offers'        => array(self::HAS_MANY, 'Offers', 'campaign_id'),
			'creatives'     => array(self::HAS_MANY, 'CampaignsCreatives', 'campaign_id'),
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
            )
        );
    }
    
    protected function beforeSave()
    {
        if($this->limit_per_day == 0){
            $this->day_clicks = 0;
        }

        $time = time();
        $start = strtotime($this->date_start);

        if ($start < ($time - 31536000)) {
            $this->date_start = date('Y-m-d', $time - 31536000);
        }

        $end = strtotime($this->date_end);
        if ($end > ($time + 31536000)) {
            $this->date_end = date('Y-m-d', $time + 31536000);
        }

        $this->isActive = $this->checkIsActive($this->id);

        $this->cities = $this->citiesIds;
        $this->countries = $this->countriesIds;
        
        
        //track_id
    	if(!empty($this->track_js)){
        	$this->track_js_compiled = $this->_compileTrackJS($this->track_js);
        } else {
        	$this->track_js_compiled = null;
        }
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        RedisCampaign::instance()->setCampaignCache($this);

        if(!empty($this->actions_cost)){
            $this->setActionsCost();
        }

        if(!empty($this->clone_id)){
            $this->cloneCampaign();
        }

        $clean_date_start = $this->dirty->getCleanAttribute('date_start');
        if(strtotime($clean_date_start) > time() && (!$this->is_active || $clean_date_start != $this->date_start)){
            /** @todo удаляем запланированое добавление если кампания стала неактивной или изменилась дата старта */

        }

        if($this->dirty->getCleanAttribute('date_end') != $this->date_end){
            // изменилась дата окончания,
            Yii::app()->resque->enqueueJobAt(strtotime($this->date_end . ' 23:59:59') + 1, 'app', 'CampaignHandleLimitJob', array(
                'campaign_id' => $this->id,
            ));
        }
        
        if($this->is_active && $this->dirty->isAttributeChanged('date_start') && $this->checkIsActive($this->id, true)){
            // кампания стартует в будущем
            Yii::app()->resque->enqueueJobAt(strtotime($this->date_start), 'app', 'CampaignAddToRedisJob', array(
                'campaign_id' => $this->id,
                'addTeasers' => true
            ));
        }elseif ($this->isBecameActive()) {
            Yii::app()->resque->createJob('app', 'CampaignAddToRedisJob', array('campaign_id' => $this->id));
            // Cоздаем задание на добавление новостей компании в redis
            foreach ($this->activeNews as $news) {
                $news->addToRedis();
            }
        } elseif ($this->isBecameNotActive()) {
            $this->deleteNewsFromRedis();
        } elseif ($this->is_deleted){
            // Cоздаем задание на удаление из БД
            Yii::app()->resque->createJob('app', 'CampaignDelFromDbJob', array('campaign_id' => $this->id));
        } elseif ($this->checkIsActive($this->id) && !$this->getIsNewRecord() && ($this->dirty->isDirty() || $this->cleanCitiesIds != $this->citiesIds || $this->cleanCountriesIds != $this->countriesIds)) {
            // Cоздаем задание на апдейт данных кампании в redis при сохранении новости
            Yii::app()->resque->createJob('app', 'CampaignUpdateInRedisJob',
                array_merge(
                    $this->getGeoDiff(),
                    array(
                        'campaign_id'           => $this->id,
                        'update_score'          => $this->getIsScoreUpdated(),
                    )
                )
            );
        }

        parent::afterSave();
    }

    private function getGeoDiff()
    {
        $cleanCitiesIds = $this->cleanCitiesIds;
        $cleanCountriesIds = $this->cleanCountriesIds;
        $citiesIds = $this->citiesIds;
        $countriesIds = $this->countriesIds;
        if(!empty($cleanCountriesIds)){
            $cleanCountriesIds = Countries::model()->getAllCodesByIds($cleanCountriesIds);
        }elseif(empty($cleanCitiesIds) && empty($cleanCountriesIds)){
            $cleanCitiesIds = Cities::model()->getAllIds();
            $cleanCountriesIds = Countries::model()->getAllCodes();
        }
        if(!empty($countriesIds)){
            $countriesIds = Countries::model()->getAllCodesByIds($countriesIds);
        }elseif(empty($citiesIds) && empty($countriesIds)){
            $citiesIds = Cities::model()->getAllIds();
            $countriesIds = Countries::model()->getAllCodes();
        }

        return array(
            'added_cities'          => array_diff($citiesIds, $cleanCitiesIds),
            'excepted_cities'       => array_diff($cleanCitiesIds, $citiesIds),
            'added_countries'       => array_diff($countriesIds, $cleanCountriesIds),
            'excepted_countries'    => array_diff($cleanCountriesIds, $countriesIds),
        );
    }

    /**
     * Требуется ли пересчет веса
     */
    private function getIsScoreUpdated()
    {
        if($this->dirty->isAttributeDirty('limit_per_day') && $this->limit_per_day == 0){
            return true;
        }elseif($this->dirty->isAttributeDirty('cost_type')){
            return true;
        }elseif($this->day_clicks > 0){
            return $this->day_clicks != $this->dirty->getCleanAttribute('day_clicks');
        }else{
            return
                $this->date_end != $this->dirty->getCleanAttribute('date_end')
                || $this->max_clicks != $this->dirty->getCleanAttribute('max_clicks');
        }
    }

    /**
     * Cоздаем задание на удаление новостей компании из redis
     */
    public function deleteNewsFromRedis()
    {
        foreach ($this->activeNews as $news) {
            $news->deleteFromRedis();
        }

        if(!empty($this->max_clicks) && $this->totalDone() >= $this->max_clicks){
            // отключаем новости и тизеры если достигунт полный лимит переходов
            $this->setActiveRecursively(0);
        }elseif ($this->checkIsDailyLimitExceeded($this->id)){
            // Если достигнут лимит суточных переходов по компании
            // Cоздаем задание на добавление новостей компании в redis
            // Задание будет выполнено на следующие сутки
            foreach ($this->activeNews as $news) {
                $news->addToRedisTommorow();
            }
        }
    }

    /**
     * @return bool Возвращает true, если компания стала неактивна
     */
    private function isBecameNotActive()
    {
        return !$this->getIsNewRecord() && $this->isActive && !$this->checkIsActive($this->id);
    }
    /**
     * @return bool Стала ли активна компания после сохранения изменений в БД
     */
    private function isBecameActive()
    {
        return !$this->isActive && $this->checkIsActive($this->id);
    }

    /**
     * @return Campaigns Именованная группа для выборки активных кампаний
     */
    public function active()
    {
        $alias = $this->getTableAlias(false,false);

        $clicksSubQuery = "SELECT IFNULL((SELECT clicks FROM " . ReportDailyByCampaign::model()->tableName() . " WHERE campaign_id = {$alias}.id AND date = date(now())) ,0)";
        $actionsSubQuery = "SELECT IFNULL((SELECT actions + offers_actions FROM " . ReportDailyByCampaign::model()->tableName() . " WHERE campaign_id = {$alias}.id AND date = date(now())) ,0)";

        $this->activeWithoutLimits();
        
        $this->getDbCriteria()
            ->addCondition(
                "{$alias}.max_clicks = 0 OR {$alias}.max_clicks IS NULL"
                . " OR ({$alias}.cost_type = '" . self::COST_TYPE_CLICK . "' AND {$alias}.max_clicks > {$alias}.clicks + {$alias}.fake_clicks)"
                . " OR ({$alias}.cost_type = '" . self::COST_TYPE_ACTION . "' AND {$alias}.max_clicks > ({$alias}.actions + {$alias}.offers_actions))"
            )
            ->addCondition(
                "{$alias}.day_clicks = 0 OR {$alias}.clicks IS NULL"
                . " OR ({$alias}.cost_type = '" . self::COST_TYPE_CLICK . "' AND {$alias}.day_clicks > ({$clicksSubQuery}))"
                . " OR ({$alias}.cost_type = '" . self::COST_TYPE_ACTION . "' AND {$alias}.day_clicks > ({$actionsSubQuery}))"
            );

        return $this;
    }

    /**
     * @return Campaigns Именованная группа для выборки активных кампаний без учета лимитов
     */
    public function activeWithoutLimits()
    {
        $alias = $this->getTableAlias(false,false);

        $this->getDbCriteria()
            ->addCondition("{$alias}.is_active = 1")
            ->addCondition("date(now()) BETWEEN {$alias}.date_start AND {$alias}.date_end");

        return $this;
    }

    /**
     * @return Campaigns Именованная группа для выборки не удаленных кампаний
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
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'id' => 'ID',
			'client_id' => 'Клиент',
			'date_start' => 'Начало',
			'date_end' => 'Окончание',
			'max_clicks' => 'Общий лимит ' . Arr::ad($this->getAvailableLimitLabels(), $this->cost_type, ''),
			'comment' => 'Комметарий',
			'is_active' => 'Активна',
			'name' => 'Название кампании',
			'limit_per_day' => 'Суточный лимит',
			'day_clicks' => 'Cуточных лимит ' . Arr::ad($this->getAvailableLimitLabels(), $this->cost_type, ''),
			'days_left' => 'Осталось дней',
            'campaignsActions' => 'Цели',
            'cost_type' => 'Тип РК',
            'actions_cost' => 'Стоимость целей',
            'bounce_check' => 'Проверять отказ в течении (сек.)',
			'track_js' => 'Сторонние JS-коды',
			'rtb_url' => 'Ссылка RTB',
			'rtb_cost' => 'Бюджет кампании',
			'rtb_cost_type' => 'Тип стоимости RTB'
		);

        return $labels;
	}

    public function getAvailableLimitLabels()
    {
        return array(
            self::COST_TYPE_ACTION => 'действий',
            self::COST_TYPE_RTB => 'показов',
            self::COST_TYPE_CLICK => 'переходов',
        );
    }

    /**
     * Удаляет новости кампании
     */
    public function deleteNews()
    {
        foreach ($this->getRelated('news') as $news) {
            $news->is_deleted = 1;
            $news->save(false, array('is_deleted'));
        }
    }

	public function getParents($uid){
		$row = Yii::app()->db
					  ->createCommand()
					  ->select('users.id as uid, users.login')
					  ->from('users')
					  ->where('users.id=:id', array(':id'=>$uid))
					  ->queryRow();
		return $row;
	}
	
	public function searchForUser($user_id, $only_active = false){
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
        $criteria->with = array(
            'news' => array('together' => true, 'select' => false),
            'news.teasers' => array('together' => true, 'select' => false)
        );
        $criteria->group = 't.id';

		$criteria->select = 't.*, to_days(t.date_end) - to_days(now()) as days_left';
		
		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.days_left',$this->days_left,true);
        $criteria->compare('t.cost_type', $this->cost_type);
		if($only_active){
			$criteria->compare('t.is_active',1);
            $criteria->addCondition('t.date_end >= CURDATE()');
		}

		$criteria->addCondition('t.client_id = '. $user_id);
        $criteria->addCondition('t.is_deleted = 0');

        if(!empty($this->name)){
            $searchCriteria = new CDbCriteria();
            $searchCriteria->compare('t.name',$this->name,true, 'OR');
            $searchCriteria->compare('t.id',$this->name,false,'OR');
            $searchCriteria->compare('news.id',$this->name,false,'OR');
            $searchCriteria->compare('teasers.id',$this->name,false,'OR');
            $criteria->mergeWith($searchCriteria);
        }

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
		        'attributes'=>array(
		            'days_left'=>array(
		                'asc'=>'to_days(t.date_end) - to_days(now())',
		                'desc'=>'to_days(t.date_end) - to_days(now()) DESC',
		            ),
		            '*',
		        ),
		      )
		));
	}

    /**
     * Проверяет является ли кампания активной
     *
     * @param int $id
     *
     * @return bool
     */
    public function checkIsActive($id, $inFuture = false, $checkLimits = true)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('(1)');
        $command->from($this->tableName() . ' c');
        $command->leftJoin(ReportDailyByCampaign::model()->tableName() . ' r', 'c.id = r.campaign_id AND r.date = date(now())');
        $command->andWhere('c.id = :id', array(':id' => $id));
        $command->andWhere('c.is_active = 1');
        if($inFuture){
            $command->andWhere('CURDATE() < c.date_start');
        }else{
            $command->andWhere('CURDATE() BETWEEN c.date_start AND c.date_end');
        }
        if($checkLimits) {
            $command->andWhere(
                "c.max_clicks = 0 OR c.max_clicks IS NULL"
                . " OR (c.cost_type = '" . self::COST_TYPE_CLICK . "' AND c.max_clicks > c.clicks + c.fake_clicks)"
                . " OR (c.cost_type = '" . self::COST_TYPE_ACTION . "' AND c.max_clicks > (c.actions + c.offers_actions))"
            );
            $command->andWhere(
                "c.day_clicks = 0 OR r.clicks IS NULL"
                . " OR (c.cost_type = '" . self::COST_TYPE_CLICK . "' AND c.day_clicks > r.clicks)"
                . " OR (c.cost_type = '" . self::COST_TYPE_ACTION . "' AND c.day_clicks > (r.actions + r.offers_actions ))"
            );
        }

        return $command->queryScalar() == '1';
    }

    /**
     * Проверяет является ли камания активной в целом, без учета лимита кликов за день
     *
     * @return bool
     */
    public function getGlobalIsActive()
    {
        if(
            $this->getDaysLeft() > -1 &&
            ($this->totalDone() < $this->max_clicks || $this->max_clicks == 0) &&
            ($this->is_active == 1)
        ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Проверяет достигнут ли суточный лимит показов компании
     *
     * @param int $id
     *
     * @return bool
     */
    public function checkIsDailyLimitExceeded($id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('(1)');
        $command->from($this->tableName() . ' c');
        $command->leftJoin(ReportDailyByCampaign::model()->tableName() . ' r', 'c.id = r.campaign_id AND r.date = date(now())');
        $command->andWhere('c.id = :id', array(':id' => $id));
        $command->andWhere('c.is_active = 1');
        $command->andWhere('date(now()) BETWEEN c.date_start AND c.date_end');
        $command->andWhere(
            "c.max_clicks = 0 OR c.max_clicks IS NULL"
            . " OR (c.cost_type = '" . self::COST_TYPE_CLICK . "' AND c.max_clicks > c.clicks + c.fake_clicks)"
            . " OR (c.cost_type = '" . self::COST_TYPE_ACTION . "' AND c.max_clicks > (c.actions + c.offers_actions))"
        );
        $command->andWhere(
            "c.day_clicks > 0 AND r.clicks IS NOT NULL"
            . " AND ("
            . "(c.cost_type = '" . self::COST_TYPE_CLICK . "' AND c.day_clicks <= r.clicks)"
            . " OR (c.cost_type = '" . self::COST_TYPE_ACTION . "' AND c.day_clicks <= (r.actions + r.offers_actions ))"
            . ")"
        );

        return $command->queryScalar() == '1';
    }

    /**
     * @param bool $checkParents
     * @return bool
     */
    public function isLimitExceeded($checkParents = false){
        return !$this->checkIsActive($this->id);
    }

    /**
     * Инициирует вызов синхронизации уровня на котором исчерпаны лимиты.
     *
     * @return bool
     */
    public function handleLimit(){
        if(RedisLimit::instance()->isExists($this)){
            // ничего не делаем, и детям не даем если лимит уже висит.
            return true;
        }

        if(!$this->checkIsActive($this->id)){
            RedisLimit::instance()->set($this);
            foreach ($this->offers(array('scopes' => array('active', 'running'))) as $offer){
                SyncManager::sync($offer);
            }
            SyncManager::syncTomorrow($this);
            $this->deleteNewsFromRedis();
            return true;
        }
        return false;
    }

    /**
     * @return int Возвращает суммарное количество кликов(с учетом поддельных) или действий
     */
    public function totalDone()
    {
        if($this->cost_type == self::COST_TYPE_CLICK){
            return $this->clicks + $this->fake_clicks;
        }else{
            return $this->actions + $this->offers_actions;
        }
    }

    public function totalClicks()
    {
        return $this->clicks + $this->fake_clicks;
    }

    /**
     * @return int Возвращает количество кликов(с учетом поддельных) или действий за день
     */
    public function totalDayDone()
    {
        $report = $this->getDayReport();
        if(!$report){
            return 0;
        }
        if($this->cost_type == self::COST_TYPE_CLICK){
            return $report->totalClicks();
        }else{
            return $report->actions + $report->offers_actions;
        }
    }

    /**
     * Возвращает отчет по показам и кликам за текущий день
     *
     * @return ReportDailyByCampaign
     */
    private function getDayReport()
    {
        if (!isset($this->dayReport)) {
            $this->dayReport = ReportDailyByCampaign::model()->findByAttributes(array(
                'campaign_id'   => $this->id,
                'date'          => date('Y-m-d')
            ));
        }

        return $this->dayReport;
    }

    /**
     * Проверяет, явдяется ли $user_id владельцем компании
     *
     * @param integer $user_id
     *
     * @return bool
     */
    public function isOwner($user_id)
    {
        return $user_id == $this->client_id;
    }

    /**
     * Возвращает sql-запрос для обновления счетчика
     *
     * @param $campaign_id
     * @param $counter
     * @param $amount
     * @param bool $is_external_platform
     * @return string
     */
    public static function createUpdateSql($campaign_id, $counter, $amount, $is_external_platform = false)
    {
        if(!Campaigns::model()->hasAttribute($counter)) { return ''; }

        $sql  = "UPDATE `" . self::getTableName() . "` ";
        $sql .= "SET ".$counter." = ".$counter." + {$amount} ";
        if($counter == 'clicks' && !$is_external_platform){
            $sql .= ", clicks_without_externals = clicks_without_externals + {$amount} ";
        }
        $sql .= "WHERE id = {$campaign_id} LIMIT 1;";

        return $sql;
    }

    /**
     * @return string Возвращает список исключенных городов в виде строки
     */
    public function getExceptedCities()
    {
        if (!$this->cities) return '';
        return implode(', ', array_map(function(Cities $city) { return $city->name; }, $this->cities));
    }

    /**
     * Отправляет уведомление о скором окончании кампании
     *
     * @param $view
     */
    public function notify($view)
    {
        if($this->is_notified)
            return;

        SMail::sendMail(
            Yii::app()->params->notifyEmail,
            'Уведомление об окончании кампании "' . $this->name . '"',
            $view,
            array('campaign' => $this)
        );

        $this->is_notified = 1;
        $this->save();
    }

    public function setActiveRecursively($isActive)
    {
        if(!$this->hasRelated('news')){
            $this->news = $this->with('news:notDeleted.teasers:notDeleted')->getRelated('news');
        }

        foreach($this->news as $news){
            foreach($news->teasers as $teaser){
                $teaser->is_active = $isActive;
                if(!$teaser->save()) throw new Exception('Cant save deactivated teaser');
            }
            $news->is_active = $isActive;
            if(!$news->save()) throw new Exception('Cant save deactivated news');
        }
        foreach ($this->offers as $offer){
            $offer->is_active = $isActive;
            if(!$offer->save()) throw new Exception('Cant save deactivated offer');
        }

        $this->is_active = $isActive;
        if(!$this->save()) throw new Exception('Cant save deactivated campaign');
    }

    protected function cloneCampaign()
    {
        $campaign = Campaigns::model()->with('news.teasers.tags','news.teasers.platforms')->findByPk($this->clone_id);
        foreach($campaign->news as $news){
            $news->id = $news->shows = $news->clicks = $news->fake_clicks = $news->clicks_without_externals = null;
            $news->isNewRecord = true;
            $news->campaign_id = $this->id;
            $teasers = $news->teasers;
            $news->teasers = array();
            if(!$news->save()) throw new Exception('Cant save cloned news');
            foreach($teasers as &$teaser){
                $teaser->cloned_id = is_null($teaser->cloned_id) ? $teaser->id : $teaser->cloned_id;
                $teaser->id = null;
                $teaser->news_id = $news->id;
                $teaser->isNewRecord = true;
                $teaser->tags=null;
                $teaser->platforms=null;
                if(!$teaser->save()) throw new Exception('Cant save cloned teaser');
            }
        }
    }

    public function getDaysLeft()
    {
        if($this->days_left == null){
            $timeEnd = strtotime($this->date_end.' 23:59:59');
            $time = time();
            $this->days_left = floor(($timeEnd - $time) / 60 / 60 / 24);
        }
        return $this->days_left;
    }

    public function getAvailableCostTypes()
    {
        return array(
            self::COST_TYPE_CLICK => 'CPC',
            self::COST_TYPE_ACTION => 'CPA',
        	self::COST_TYPE_RTB => 'RTB'
        );
    }

    public function getAvailableRTBCostTypes()
    {
    	return array(
    		self::RTB_COST_TYPE_CPM => 'Max CPM',
    		self::RTB_COST_TYPE_CPC => 'Target CPC'
    	);
    }
    
    public function setActionsCost(){
        $this->getDbConnection()->createCommand()->update(
            CampaignsActions::model()->tableName(),
            array('cost' => $this->actions_cost),
            '`campaign_id` = :campaign_id',
            array(':campaign_id' => $this->id)
        );
    }

    public function updateWeight()
    {
        //        RedisCampaign::instance()->decCampaignWeight($campaignId, 1);
        /**
         * @todo не оптимально. тут приходится постоянно перегружать Campaigns и ReportDailyByCampaign.
         * Когда будут изменения с таргетингом, надо проработать.
         */
        RedisCampaign::instance()->setCampaignWeight(
            $this->id,
            RedisCampaign::instance()->calcWeight($this)
        );
    }

    /**
     * Возвращает данные кампании
     *
     * Если данные закэшированы, тогда они берутся из redis
     *
     * @param $campaignId
     * @return array
     * @throws CException
     */
    public static function getById($campaignId)
    {
        $campaign = RedisCampaign::instance()->getCampaignCache($campaignId);
        if (!$campaign) {
            $campaign = self::model()->notDeleted()->findByPk($campaignId);
            if($campaign === null){
                throw new CException('Cant find campaign by id: '.$campaignId);
            }
            RedisCampaign::instance()->setCampaignCache($campaign);
        }

        return $campaign;
    }

    public function getBounceRateHtml()
    {
        $result = '-';
        if($this->bounce_check !== null){
            $result = round($this->getBounceRate(), 2).'%';
            if($this->bounce_rate_diff != 0){
                $result .= ' <span class="label label-important">+'.round($this->bounce_rate_diff, 2).'%</span>';
            }
        }
        return $result;
    }

    public function getBounceRate()
    {
        return ($this->clicks > 0 ? 100 - (100  / $this->clicks * $this->bounces) : 0);
    }

    
    /**
     *	Compile JS for using in track_inject.lua
     **/
    protected function _compileTrackJS( $js )
    {
    	$_compiled = null;
    	$_scripts = array();
    	$_images  = array();
    	try{
    		$_dom = new DomDocument();
    		$_dom->loadHTML( $js );
    
    		//scripts
    		foreach($_dom->getElementsByTagName('script') as $_s_obj){
    			$_scripts[] = trim(preg_replace('/\n\s*\n/',"\n",preg_replace('#^\s*//.+$#m','',$_s_obj->nodeValue)));
    		}
    
    		//images
    		$_imgs = $_dom->getElementsByTagName('img');
    		foreach($_imgs as $_img_obj){
    			$_img = array();
    			foreach ($_img_obj->attributes as $attr) {
    				$_img[$attr->nodeName] = $attr->nodeValue;
    			}
    			$_images[] = $_img;
    		}
    
    		//Compile JS
    		$_compiled .= "\n";
    		foreach($_scripts as $_script){
    			$_compiled .= "try{\n";
    			$_compiled .= $_script;
    			$_compiled .= "\n}catch(e){};\n";
    		}
    
    		foreach($_images as $k => $_image){
    			$_compiled .= "try{\nvar newImg_{$k} = document.createElement(\"img\");\n";
    			foreach($_image as $_attr => $_value){
    				$_compiled .= "newImg_{$k}.{$_attr} = \"".preg_replace('/"/','\"',$_value)."\";\n";
    			}
    			$_compiled .= "document.getElementsByTagName(\"head\")[0].appendChild(newImg_{$k});\n}catch(e){};\n";
    		}
    
    		//TODO: hide js
    		//return JSHideHelper::encode($_compiled,"\t"," ",".");
    		return trim($_compiled);
    
    	} catch(Exception $e){
    		//print_r($e->getMessage());
    		return null;
    	}
    }

    public function addShow( $campaigns_id )
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id', $campaigns_id, true);
        $campaign = self::model()->find($criteria);

        $campaign->shows++;
        $campaign->saveAttributes(array('shows'));
    }

    public function addClick( $campaigns_id )
    {
        $criteria=new CDbCriteria;

        $criteria->compare('id', $campaigns_id, true);
        $campaign = self::model()->find($criteria);

        $campaign->clicks++;
        $campaign->saveAttributes(array('clicks'));
    }
}