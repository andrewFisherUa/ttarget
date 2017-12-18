<?php


/**
 * This is the model class for table "offers".
 *
 * The followings are the available columns in table 'offers':
 * @property string $id
 * @property string $action_id
 * @property string $campaign_id
 * @property string $name
 * @property string $description
 * @property double $payment
 * @property double $reward
 * @property integer $is_active
 * @property string $date_start
 * @property string $date_end
 * @property integer $unique_ip
 * @property string $created_date
 * @property integer $lead_status
 * @property string $url
 * @property $cookie_expires
 * @property $use_limits
 * @property integer $limits_per_day
 * @property integer $limits_total
 * @property integer $user_limits_per_day
 * @property integer $user_limits_total
 * @property integer $offers_clicks
 * @property integer $offers_actions
 * @property integer $offers_declined_actions
 * @property integer $is_deleted
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 * @property CampaignsActions $action
 * @property Cities[] $cities
 * @property Countries[] $countries
 * @property OffersImages[] $offersImages
 * @property OffersUsers[] $users
 */
class Offers extends CActiveRecord
{
    const LEAD_STATUS_MODERATION = 1;
	
    //Images ----------------------------
	protected $_imageIdsNew    = array();
	protected $_imageIdsDelete = array();
	//-----------------------------------
	
	//GEO --------------------
	public $countriesIds = array();
	public $citiesIds    = array();
	protected $_geoIsDirty = false;
	
	protected $_countriesCodes;
	protected $_countriesNames;
	//-------------------------
	
	//WMFilter -----------------
	protected $_wmFilterRules   = array(); //[user_id1 => filter_type==OffersUsersFilter::FILTER_TYPE_ALLOW/DENY]
	protected $_wmFilterRulesIsDirty = false;
	//--------------------------
	
	
	
    private $_dayReport = false;
    
    private static $_useDefaultScope = true;
	
	public function getCountriesCodes()
	{
		return $this->_countriesCodes;
	}
	
	public function getCountriesNames()
	{
		return $this->_countriesNames;
	}
	
	public function setImageIdsNew( $ids )
	{
		$this->_imageIdsNew = $ids;
	}
	
	public function setImageIdsDelete( $ids )
	{
		$this->_imageIdsDelete = $ids;
	}
	
	public function setCountriesIds( $ids )
	{
		$this->countriesIds = $ids;
		$this->_geoIsDirty = true;
	}
	
	public function setCitiesIds( $ids )
	{
		$this->citiesIds = $ids;
		$this->_geoIsDirty = true;
	}
	
	/**
	 *	Возвращает красивую строку, содержащую инфу о периоде действия оффера
	 **/
	public function getPeriodStr()
	{
		$_time_start = strtotime($this->date_start);
		$_time_end   = strtotime($this->date_end);
	
		$_day_start  = ((int)date('d', $_time_start));
		$_day_end    = ((int)date('d', $_time_end));
	
		$_months_start = (int)date('m', $_time_start);
		$_months_end   = (int)date('m', $_time_end);
		//DateHelper::getRusMonth()
		$_year_start = date('Y', $_time_start);
		$_year_end   = date('Y', $_time_end);
	
		return ($_year_start == $_year_end) ?
		(($_months_start == $_months_end) ?
				sprintf("С %d по %d %s %s", $_day_start, $_day_end, DateHelper::getRusMonth($_months_start), $_year_start) :
				sprintf("С %d %s по %d %s %s", $_day_start, DateHelper::getRusMonth($_months_start), $_day_end, DateHelper::getRusMonth($_months_end), $_year_start)) :
				sprintf("С %d %s %s по %d %s %s", $_day_start, DateHelper::getRusMonth($_months_start), $_year_start, $_day_end, DateHelper::getRusMonth($_months_end), $_year_end);
	}
	
	/**
	 *	Возвращает true, если для указанной кампании есть доступные создания офферов цели
	 *	@param int $campaign_id
	 *	@return bool
	 **/
	public function hasAvailableCampaignActions( $campaign_id )
	{
		$CampaignsActions = CampaignsActions::model();
	
		$criteria = new CDbCriteria();
		$criteria->alias     = 'A';
		$criteria->select    = 'A.*';
		
		$_condition = 'A.campaign_id = :campaign_id AND A.is_deleted = 0 ';
		$_params = array(':campaign_id' => $campaign_id);

		$criteria->condition = $_condition;
		$criteria->params    = $_params;
	
		$actions = $CampaignsActions -> exists($criteria);
		return $actions;
	}
	
	/**
	*	Возвращает список целей для кампании, которые еще не привязаны к офферам
	*	@param int $campaign_id
	**/
	public function getAvailableCampaignActions( $campaign_id )
	{
		$CampaignsActions = CampaignsActions::model();
		
		$criteria = new CDbCriteria();
		$criteria->alias     = 'A';
		$criteria->select    = 'A.*';
		
		$_condition = 'A.campaign_id = :campaign_id AND A.is_deleted = 0 ';
		$_params = array(':campaign_id' => $campaign_id);

		$criteria->condition = $_condition;
		$criteria->params    = $_params;
		
		$actions = $CampaignsActions -> findAll($criteria);
		return $actions;
	}

	/**
	*	Возвращает список офферов, доступных для пользователя
	**/
	public function getAvailableForUserId( $user_id, $returnProvider = true )
	{
		$criteria = new CDbCriteria();
		
		$criteria->alias = 'offers';
		$criteria->select = 'offers.*';
		$criteria->addCondition('offers.id NOT IN (SELECT DISTINCT(offer_id) FROM offers_users WHERE user_id = :user_id)');
		$criteria->addCondition('offers.is_deleted != 1');
		$criteria->addCondition('offers.is_active = 1');
		
		//wm_filter
		$criteria->addCondition('offers.id IN (SELECT DISTINCT(O.id)
								FROM offers O
								LEFT JOIN offers_users_filter F ON F.offer_id = O.id
								WHERE
								((F.type IS NULL OR F.type = 1) AND (F.user_id IS NULL OR F.user_id = :user_id)))');
		
		$criteria -> params = array(':user_id' => $user_id);
		return $returnProvider ? new CActiveDataProvider($this, array( 'criteria'=>$criteria)) : $this->findAll($criteria);
	}

    protected function beforeSave()
    {
        $this->url = IDN::encodeUrl($this->url);
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $this->_processImages();
        $this->_processGEO();
        $this->_processWMFilter();

        $startTime = strtotime($this->date_start . '00:00:00');
        if($startTime > time()){
            Yii::app()->resque->enqueueJobAt($startTime, 'app', 'OfferSyncToRedisJob', array(
                    'offer_id' => $this->id,
            ));
        }else {
            Yii::app()->resque->createJob('app', 'OfferSyncToRedisJob', array(
                'offer_id' => $this->id,
            ));
        }
        
        //TODO: Notifications to webmasters
        //Yii::app()->resque->createJob('app', 'OfferSyncToRedisJob', array(
        //		'offer_id' => $this->id,
        //));
        
        parent::afterSave();
    }

    public function isActive()
    {
        if(
            $this->is_active &&
            ! $this->is_deleted &&
            $this->isGoing() &&
            Campaigns::model()->checkIsActive($this->campaign_id, false, false)
        ){
            return true;
        }

        return false;
    }
    
    public function isGoing()
    {
    	$time = time();
    	return strtotime($this->date_start . '00:00:00') <= $time && strtotime($this->date_end . '23:59:59') >= $time;
    }

    /**
     * @param bool $checkParents
     * @return bool
     */
    public function isLimitExceeded($checkParents = false)
    {
        if(
            $this->use_limits == 1 &&
            (
                ($this->limits_total != 0 && $this->offers_actions >= $this->limits_total) ||
                (
                    $this->limits_per_day != 0 &&
                    $this->getDayReport() &&
                    $this->getDayReport()->offers_actions >= $this->limits_per_day
                )
            )
        ){
            return true;
        }

        return ($checkParents ? $this->campaign->isLimitExceeded() : false);
    }

    /**
     * Инициирует вызов синхронизации уровня на котором исчерпаны лимиты.
     *
     * @return bool
     */
    public function handleLimit()
    {
        if(RedisLimit::instance()->isExists($this)){
            // ничего не делаем, и детям не даем если лимит уже висит.
            return true;
        }

        if($this->campaign->handleLimit()){
            return true;
        }elseif($this->isLimitExceeded()){
            RedisLimit::instance()->set($this);
            SyncManager::syncNowAndTomorrow($this);
            return true;
        }
        return false;
    }

    /**
    *	Проверяет разрешения на просмотр для пользователя по таблице фильтрации
    **/
    public function isAllowedForUser( $user_id )
    {
    	$criteria = new CDbCriteria();
    	
    	$criteria->alias = 'offers';
    	$criteria->select = 'offers.*';
    	$criteria->addCondition('offers.id = :offer_id');
    	$criteria->addCondition('offers.id IN (SELECT DISTINCT(O.id)
								FROM offers O
								LEFT JOIN offers_users_filter F ON F.offer_id = O.id
								WHERE
								((F.type IS NULL OR F.type = 1) AND (F.user_id IS NULL OR F.user_id = :user_id)))');
    	
    	$criteria -> params = array(':offer_id' => $this->id, ':user_id' => $user_id);
    	return $this->count($criteria);
    }
    
    /**
     * @return ReportDailyByOffer
     */
    private function getDayReport()
    {
        if($this->_dayReport === false){
            $this->_dayReport = ReportDailyByOffer::model()->findByAttributes(array(
                'date' => date('Y-m-d'),
                'offer_id' => $this->id
            ));
        }

        return $this->_dayReport;
    }
	
	/**
	*	Return offers for user
	**/
	public function findByUserId( $user_id, $returnProvider = false )
	{
		$criteria = new CDBCriteria();
		$criteria -> addCondition('id IN (SELECT offer_id FROM offers_users WHERE user_id = :user_id)');
		$criteria -> params = array(':user_id' => $user_id );
		
		return !$returnProvider ? $this->findAll($criteria) :
								new CActiveDataProvider($this, array(
										'criteria'=>$criteria,
								));
		;
	}
	
	
	/**
	*	Check if user is joined this offer
	*	@param int $user_id
	*	@return bool
	**/
	public function isUserJoined($user_id)
	{
		$_criteria = new CDbCriteria();
		$_criteria -> addCondition('user_id = :user_id');
		$_criteria -> addCondition('offer_id = :offer_id');
		$_criteria -> params = array(':offer_id' => $this->id,':user_id' => $user_id);
		return OffersUsers::model()->exists($_criteria);
		
	}
	
	/**
	*	TODO:: Join user to this offer
	*	@param Users $User
	*	@return bool
	*	@throws Exception
	**/
	public function joinUser( $user_id, $description = null )
	{
		$OfferUser = new OffersUsers();
		$OfferUser->offer_id = $this->id;
		$OfferUser->user_id  = $user_id;
		$OfferUser->description = $description;
		
		$OfferUser->save();
		return $OfferUser;
	}
	
	/**
	*	Find by campaign_id
	**/
	public function findByCampaignId( $campaign_id, $period = 'all', $dateFrom = null, $dateTo = null, $order = null, $returnProvider = true )
	{
		$_criteria = new CDbCriteria;
		
		$_condition = 'offers.campaign_id = '.$campaign_id;
		if($period != 'all'){
			$_condition .= ' AND offers.created_date >= \''.$dateFrom.' 00:00:00\' AND offers.created_date <= \''.$dateTo.' 23:59:00\'';
		}
		$_criteria -> condition = $_condition;
		if($order){
			$_criteria -> order = $order;
		} else {
			$_criteria -> order = 'offers.id ASC';
		}
		$_criteria -> with=array(
				'action',
				'countries',
		);
		
		$_results = $returnProvider ? new CActiveDataProvider($this, array( 'criteria'=>$_criteria)) : $this->findAll($_criteria);
		
		return $_results;
	}
	
	
	protected function afterFind()
	{
		$_countriesCodes = array();
		$_countriesNames = array();
		foreach($this->countries as $country){
			$this->countriesIds[] = $country->id;
			$_countriesCodes[] = $country->code;
			$_countriesNames[] = $country->name;
		}
		$this->_countriesCodes = join(', ', $_countriesCodes);
		$this->_countriesNames = join(', ', $_countriesNames);
		foreach($this->cities as $city){
			$this->citiesIds[] = $city->id;
		}
		
		//foreach($this->_wmFilterRules as $user_id => $type){
		//
		//}
	}
	

	/**
	*	Обработка новых и удаленных изображений
	**/
	protected function _processImages()
	{
		if(!empty($this->_imageIdsNew)){
			$_tmpPath = Yii::app()->params->docTmpPath;
			
			foreach($this->_imageIdsNew as $_file_id){
				
				$_image = new OffersImages('create');
				$_image -> offer_id = $this->id;
				$_image -> file = $_file_id;
				$_image -> save(false);
			}
			
		}
		if(!empty($this->_imageIdsDelete)){
			
			OffersImages::model()->deleteByIds($this->id, $this->_imageIdsDelete);
			
		}
	}
	
	/**
	*	Обработка GEO-данных после сохранения модели
	**/
	protected function _processGEO()
	{
		if($this->_geoIsDirty){
			OffersCountries::model()->deleteAll('offer_id = :offer_id', array(':offer_id' => $this->id));
			foreach($this->countriesIds as $country_id){
				$country = new OffersCountries;
				$country -> offer_id = $this->id;
				$country -> country_id = $country_id;
				$country -> save(false);
			}
			
			OffersCities::model()->deleteAll('offer_id = :offer_id', array(':offer_id' => $this->id));
			foreach($this->citiesIds as $city_id){
				$country = new OffersCities;
				$country -> offer_id = $this->id;
				$country -> city_id = $city_id;
				$country -> save(false);
			}
		}
	}
	
	
	public function setWMFilterRules($ids = array(), $type = OffersUsersFilter::FILTER_TYPE_ALLOWED)
	{
		$this->_wmFilterRules = array();
		if(!empty($ids)){
			//set rules
			foreach($ids as $id){
				$this->_wmFilterRules[$id] = $type;
			}
		}
		$this->_wmFilterRulesIsDirty = true;
	}
	
	/**
	*	Обработка данных фильтра показа оффера для вебмастеров
	**/
	protected function _processWMFilter()
	{
		if($this->_wmFilterRulesIsDirty){
			OffersUsersFilter::model()->deleteByOfferId($this->id);
			foreach($this->_wmFilterRules as $user_id => $type){
				$_rule = new OffersUsersFilter();
				$_rule -> offer_id = $this->id;
				$_rule -> user_id = $user_id;
				$_rule -> type = $type;
				$_rule -> save();
			}
		}
	}
	
	//===============================================================
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Offers the static model class
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
		return 'offers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, action_id, name, description, date_start, date_end, url', 'required'),
			array('is_active, unique_ip, lead_status, cookie_expires, use_limits, limits_per_day, limits_total, user_limits_per_day, user_limits_total', 'numerical', 'integerOnly'=>true),
			array('payment, reward', 'numerical'),
			//array('action_id', 'length', 'max'=>10),
			array('name, description', 'length', 'max'=>255),
			array('url', 'length', 'max' => 512),
			array('url','url', 'validSchemes' => array('http','https'),'defaultScheme' => 'http', 'allowEmpty' => false, 'validateIDN' => true),
			//array('created_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaign_id, action_id, name, description, payment, reward, is_active, date_start, date_end, unique_ip, created_date, lead_status, cookie_expires, use_limits, limits_per_day, limits_total, user_limits_per_day, user_limits_total, imageIds', 'safe', 'on'=>'search'),
		
			array('date_start, date_end', 'date', 'format' => 'yyyy-MM-dd'),
			//Проверяем, чтобы дата начала была не меньше даты окончания
			//array('date_end',   'compare',
  			//					'compareAttribute'=>'date_start',
  			//					'operator'=>'>',
  			//					'allowEmpty'=>true ,
  			//					'message'=>'Дата окончания действия оффера должна быть больше даты начала.'),
			

			
		);
	}
	
	/**
	*	Scopes:
	*
	*		active
	*		running
	**/
	public function scopes()
	{
		return array(
			'active' => array(
				'condition' => 'is_active = 1',
			),
			'running' => array(
				'condition' => 'CURRENT_DATE BETWEEN date_start AND date_end'
			),
		);
	}
	
	public function defaultScope()
	{
		return self::$_useDefaultScope ? array(
				'alias' => 'offers',
				'condition' => 'offers.is_deleted = 0'
		) : array();
	}
	
	public static function enableDefaultScope()
	{
		self::$_useDefaultScope = true;
	}
	
	public static function disableDefaultScope()
	{
		self::$_useDefaultScope = false;
	}
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'campaign' => array(self::BELONGS_TO, 'Campaigns', 'campaign_id'),
			'action' => array(self::BELONGS_TO, 'CampaignsActions', 'action_id'),
			'cities' => array(self::MANY_MANY, 'Cities', 'offers_cities(offer_id, city_id)'),
			'countries' => array(self::MANY_MANY, 'Countries', 'offers_countries(offer_id, country_id)'),
			'wmFilterRules' => array(self::HAS_MANY, 'OffersUsersFilter', 'offer_id'),
			'images' => array(self::HAS_MANY, 'OffersImages', 'offer_id'),
			'users' => array(self::HAS_MANY, 'OffersUsers', 'offer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'action_id' => 'Цель',
			'name' => 'Заголовок',
			'description' => 'Описание',
			'payment' => 'Выплата',
			'reward' => 'Вознаграждение',
			'is_active' => 'Активна',
			'date_start' => 'Дата начала',
			'date_end' => 'Дата окончания',
			'unique_ip' => 'Проверять уникальность IP',
			'created_date' => 'Дата создания',
			'lead_status' => 'ЛИДы подтверждены',
			'use_limits' => 'Использовать лимиты',
			'limits_per_day' => 'День',
			'limits_total' => 'Всего',
			'user_limits_per_day' => 'День для вебмастера',
			'user_limits_total' => 'Всего для вебмастера',
			'cookie_expires' => 'Время жизни кукиса',
			'url' => 'URL'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('action_id',$this->action_id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('payment',$this->payment);
		$criteria->compare('reward',$this->reward);
		$criteria->compare('is_active',$this->is_active);
		$criteria->compare('date_start',$this->date_start,true);
		$criteria->compare('date_end',$this->date_end,true);
		$criteria->compare('unique_ip',$this->unique_ip);
		$criteria->compare('created_date',$this->created_date,true);
		$criteria->compare('lead_status',$this->lead_status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function behaviors()
	{
		return array(
			'timestamps' => array(
				'class'                 => 'zii.behaviors.CTimestampBehavior',
				'createAttribute'       => 'created_date',
				'updateAttribute'       => null,
				'timestampExpression'   => new CDbExpression('now()'),
			),
		);
	}
}
