<?php

/**
 * This is the model class for table "offers_users".
 *
 * The followings are the available columns in table 'offers_users':
 * @property string $id
 * @property string $offer_id
 * @property string $user_id
 * @property string $created_date
 * @property integer $status
 * @property integer $lead_status
 * @property integer $limits_per_day
 * @property integer $limits_total
 * @property integer $offers_actions
 * @property integer $offers_declined_actions
 * @property integer $offers_moderation_actions
 *
 * The followings are the available model relations:
 * @property Offers $offer
 * @property Users $user
 *
 * @method OffersUsers findByPk
 */
class OffersUsers extends CActiveRecord
{
    const STATUS_MODERATION = 0;
    const STATUS_ACCEPTED   = 1;
    const STATUS_DECLINED   = 2;
    const STATUS_DELETED    = 3;

    public $conversions = 0;
    public $reward_total = 0;
	public $is_deleted = false;
    private $_dayReport = false;
    
    /**
    *	Find offer user request
    **/
    public function findOfferRequestByUserId($offer_id, $user_id)
    {
    	$criteria = new CDbCriteria();
    	$criteria->addCondition('offer_id = :offer_id');
    	$criteria->addCondition('user_id = :user_id');
    	$criteria->params = array(
    		':offer_id' => $offer_id,
    		':user_id'  => $user_id
    	);
    	
    	return $this->find($criteria);
    }
    
    /**
    *	Find offers requests by user id
    **/
    public function findByUserId( $user_id, $returnDataProvider = true, $status = -1 ){
    	
    	//TODO: offers_users statistic: clicks, conversions, total
    	
    	
    	$_criteria = new CDbCriteria();
    	$_params = array(':user_id' => $user_id);
    	$_criteria -> addCondition('user_id = :user_id');
    	if($status >= 0){
    		$_criteria -> addCondition('status = :status');
    		$_params[':status'] = $status;
    	}
    	$_criteria -> params = $_params;
    	$_criteria -> with = 'offer';
    	
    	return $returnDataProvider ? new CActiveDataProvider($this, array( 'criteria'=>$_criteria)) : $this->findAll($_criteria);
    }
    
    /**
    *	Mark all deleted by offer id
    **/
    public function deleteByOfferId( $offer_id )
    {
    	$criteria = new CDbCriteria();
    	$criteria -> addCondition('offer_id = :offer_id');
    	$criteria -> params = array(':offer_id' => $offer_id);
    	$this->updateAll(array('status' => self::STATUS_DELETED), $criteria);
    }
    
    /**
     *	List available statuses and it's names
     **/
    public function getAvailableStatuses()
    {
    	return array(
    			self::STATUS_MODERATION => 'новая',
    			self::STATUS_ACCEPTED => 'подтверждена',
    			self::STATUS_DECLINED => 'отклонена',
    			self::STATUS_DELETED => 'неактивна'
    	);
    }
    
    /**
    *	Возвращает имя статуса
    **/
    public function getStatusName( $status = -1 )
    {
    	if($status == -1){
    		$status = $this->status;
    	}
    	
    	switch ($status){
    		case self::STATUS_MODERATION:
    			return 'на модерации';
    		case self::STATUS_ACCEPTED:
    			return 'подтверждена';
    		case self::STATUS_DECLINED:
    			return 'отклонена';
    		case self::STATUS_DELETED:
    			return 'неактивна';
    	}
    }
    
    
	/**
	*	Check if offer moderated
	*	@return bool
	**/
	public function isModerated()
	{
		return $this->status != self::STATUS_MODERATION;
	}

    /**
     * Check if user offer is accepted
     * @return bool
     */
    public function isAccepted()
    {
        return $this->status == self::STATUS_ACCEPTED;
    }
	
    
	/*
	*	Moderate webmaster offer
	*	@param bool $acceept
	*	@return bool
	*	@throws Exception
	**/
	public function moderate( $accept = false )
	{
		if(!$this->isModerated()){
			
			if( $accept ){
				$this->status = self::STATUS_ACCEPTED;
			} else {
				$this->status = self::STATUS_DECLINED;
			}
			
			//todo something e.g. notices or anything...
			
		} else {
			//Offer user already moderated, it means that we change status for special reasons
			
		}
		
		$this->save();
		
		return true;
	}

	public function setStatus( $status, $save = false, $notify = false )
	{
		$this->status = $status;
		//TODO: notification if needed
		if(!$this->isNewRecord && $notify){
			
			$text = 'Ваша заявка на подключение предложения "'.$this->offer->name.'" переведена в статус "'.$this->getStatusName($status).'"';
			OffersUsersNotifications::model()->send($this->user_id, $text);
			
		}
		return $save ? $this->save(true) : true;
	}
	
    /**
     * @return bool
     */
    public function isActive()
    {
        if(
            $this->isAccepted() &&
            $this->offer && $this->offer->isActive()
        ){
            return true;
        }
        return false;
    }

    /**
     * @param bool $checkParents
     * @return bool
     */
    public function isLimitExceeded($checkParents = false)
    {
        if(
            ($this->limits_total != 0 && $this->offers_actions >= $this->limits_total) ||
            (
                $this->limits_per_day != 0 &&
                $this->getDayReport() &&
                $this->getDayReport()->offers_actions >= $this->limits_per_day
            )
        ){
            return true;
        }

        return ($checkParents ? $this->offer->isLimitExceeded() : false);
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

        if($this->offer->handleLimit()){
            return true;
        }elseif($this->isLimitExceeded()){
            RedisLimit::instance()->set($this);
            SyncManager::syncNowAndTomorrow($this);
            return true;
        }
        return false;
    }

    /**
     * @return ReportDailyByOfferUser
     */
    private function getDayReport()
    {
        if($this->_dayReport === false){
            $this->_dayReport = ReportDailyByOfferUser::model()->findByAttributes(array(
                'date' => date('Y-m-d'),
                'offer_user_id' => $this->id
            ));
        }

        return $this->_dayReport;
    }

    /**
     * OffersUsers encrypted link
     * @return string
     */
    public function getEncryptedId()
    {
        return Crypt::encryptUrlComponent('offer|'.$this->id);
    }

    /**
     * OffersUsers full url
     * @return string
     */
	public function getUrl()
    {
        return Yii::app()->params->offerLinkBaseUrl . $this->getEncryptedId();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OffersUsers the static model class
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
		return 'offers_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('offer_id, user_id', 'required'),
			array('status,limits_per_day,limits_total', 'numerical', 'integerOnly'=>true),
			array('offer_id, user_id', 'length', 'max'=>10),
			array('description','required','message' => 'Необходимо заполнить комментарий к заявке'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, offer_id, user_id, created_date, status,limits_per_day,limits_total', 'safe'),
		);
	}

    /**
     *	Scopes:
     *
     *		accepted
     **/
    public function scopes()
    {
        return array(
            'accepted' => array(
                'condition' => 'status = :status',
                'params' => array(':status' => self::STATUS_ACCEPTED)
            ),
        );
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'offer' => array(self::BELONGS_TO, 'Offers', 'offer_id'),
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
            'shortLink' => array(
                self::HAS_ONE,
                'ShortLink',
                array('target_id' => 'id'),
                'condition' => "shortLink.target_type = '".ShortLink::TARGET_TYPE_OFFER_USER."'"
            )
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'Id',
			'offer_id' => 'Offer',
			'user_id' => 'User',
			'description' => 'Комментарий',
			'created_date' => 'Created Date',
			'status' => 'Status',
			'lead_status' => 'Lead Status',
			'limits_per_day' => 'Лимит за день',
			'limits_total' => 'Лимит всего'
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($period = 'all', $dateFrom = null, $dateTo = null, $status = null, $search = null, $returnProvider = true)
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$_criteria = new CDbCriteria();
		$_criteria -> alias = 'offers_users';
        $_criteria -> compare('user_id',$this->user_id);
		$_params = array();
		
		if($period != 'all'){
			$_criteria -> addCondition('offers_users.created_date >= :date_from AND offers_users.created_date <= :date_to');
			$_params[':date_from'] = $dateFrom . ' 00:00:00';
			$_params[':date_to']   = $dateTo . ' 23:59:59';
		}
		if( null !== $status ){
			$_criteria -> addCondition('offers_users.status = :status');
			$_params[':status']   = $status;
		} else {
			$_criteria -> addCondition('offers_users.status != :status');
			$_params[':status']   = self::STATUS_DELETED;
		}

		$_criteria -> params = array_merge($_criteria -> params, $_params);
		$_criteria -> with = array('user' => 'user','offer' => 'offer');
		//$_criteria -> order = $_order;
		
		if($search){
			
			$_search = new CDbCriteria();
			$_search->addSearchCondition('user.login',$search,true,'OR');
			$_search->addSearchCondition('user.email',$search,true,'OR');
			$_search->addSearchCondition('offers.name',$search,true,'OR');
			$_search->addSearchCondition('offers.description',$search,true,'OR');
			
			$_criteria->mergeWith($_search);
		}

		return $returnProvider ? new CActiveDataProvider($this, array(
			'criteria'=>$_criteria,
		)) : $this->findAll($_criteria);
	}

	public function getCountNew()
	{
		return $this->countByAttributes(array('status' => 0));
	}
	
	
	protected function afterFind()
	{
		$this->conversions = $this->offers_clicks > 0 ? sprintf('%.2f', $this->offers_actions / $this->offers_clicks * 100) : 0;
		//TODO: уточнить
		$this->reward_total = $this->offers_actions * ($this->offer ? $this->offer->reward : 0);
		
		$this->is_deleted = ($this->status == self::STATUS_DELETED);
		return parent::afterFind();
	}

    protected function afterSave()
    {
        Yii::app()->resque->createJob('app', 'OfferUserSyncToRedisJob', array(
            'offer_user_id' => $this->id,
        ));

        parent::afterSave();
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
