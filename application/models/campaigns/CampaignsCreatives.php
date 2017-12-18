<?php

/**
 * This is the model class for table "campaigns_creative".
 *
 * The followings are the available columns in table 'campaigns_creative':
 * @property string $id
 * @property string $campaign_id
 * @property string $name
 * @property string $type
 * @property integer $max_shows_hour
 * @property integer $max_shows_day
 * @property integer $max_shows_week
 * @property integer $is_active
 * @property integer $status
 * @property integer $rtb_id
 * @property integer $cost
 * @property string $creative_data
 * @property integer[] $segmentsIds
 *
 * @property DirtyObjectBehavior $dirty
 * @property IdsBehavior $ids
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 * @property CampaignsCreativeTypesRelations[] $campaignsCreativeTypesRelations
 * @property Segments[] $segments
 *
 * @method CampaignsCreatives[] findAll()
 * @method CampaignsCreatives[] findAllBySql()
 * @method CampaignsCreatives findByPk()
 */
class CampaignsCreatives extends CActiveRecord
{
	const TYPE_IMAGE = 'image';
	const TYPE_AUDIO = 'audio';
	const TYPE_VIDEO = 'video';
	
	public $typesIds = array();
	public $categoryIds = array();
	
	const YENDEX_MODERATION_CREATED 		= 1; // Креатив создан
	const YENDEX_MODERATION_SEND_ON_MOD 	= 2; // Креатив на модерации
	const YENDEX_MODERATION_REJECTED 		= 3; // Креатив не прошел модерацию
	const YENDEX_MODERATION_CONFIRMED		= 4; // Креатив прошел модерацию

	const YANDEX_DEFAULT_BRAND = 1;

	const GEO_UKRAINE_ID = 187; // [id] => 187  | [parentId] => 166
	const GEO_RUSSIAN_ID = 225; // [id] => 225 | [parentId] => 10001

	protected $_filename;
	
	private $cleanTypeIds = array();
	private $cleanCategoryIds = array();

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsCreatives the static model class
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
		return 'campaigns_creative';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, name, type, cost', 'required'),
			array('max_shows_hour, max_shows_day, max_shows_week, is_active, filesize, cost', 'numerical', 'integerOnly'=>true),
			array('campaign_id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>50),
			array('type', 'length', 'max'=>5),
			array('size', 'in', 'range' => array_keys($this->getAvailableSizes()), 'allowEmpty' => true),
			array('link', 'length', 'max'=>255),
			array('filename','length', 'max'=>255),
            array('segmentsIds', 'type', 'type' => 'array'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaign_id, name, type, max_shows_hour, max_shows_day, max_shows_week, count_shows_total, created_date, is_active, filename, link, cost, last_bid_request_id, creative_data', 'safe', 'on'=>'search'),
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
			'campaign' => array(self::BELONGS_TO, 'Campaigns', 'campaign_id'),
			'creativeTypes' => array(self::HAS_MANY, 'CampaignsCreativeTypesRelations', 'creative_id'),
			'creativeCategories' => array(self::HAS_MANY, 'CampaignsCreativeCategoryRelations', 'creative_id'),
            'segments' => array(self::MANY_MANY, 'Segments', 'creative_segments(creative_id, segment_id)')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'campaign_id' => 'Кампания',
			'name' => 'Название',
			'type' => 'Формат',
			'size' => 'Размер креатива',
			'max_shows_hour' => 'Макс. показов в час',
			'max_shows_day' => 'Макс. показов в день',
			'max_shows_week' => 'Макс. показов в неделю',
			'link' => 'Ссылка на ресурс',
			'is_active' => 'Активен',
			'cost' => 'Ставка',
            'segments' => 'Сегменты'
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
		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('max_shows_hour',$this->max_shows_hour);
		$criteria->compare('max_shows_day',$this->max_shows_day);
		$criteria->compare('max_shows_week',$this->max_shows_week);
		$criteria->compare('is_active',$this->is_active);
		$criteria->compare('cost',$this->cost);
		$criteria->compare('creative_data',$this->creative_data);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

/**
	*	Find by campaign_id
	**/
	public function findByCampaignId( $campaign_id, $period = 'all', $dateFrom = null, $dateTo = null, $order = null, $returnProvider = true )
	{
		$_criteria = new CDbCriteria;
		
		$_condition = 'campaigns_creative.campaign_id = '.$campaign_id;
		if($period != 'all'){
			$_condition .= ' AND campaigns_creative.created_date >= \''.$dateFrom.' 00:00:00\' AND campaigns_creative.created_date <= \''.$dateTo.' 23:59:00\'';
		}
		$_criteria -> condition = $_condition;
		$_criteria -> alias = 'campaigns_creative';
		if($order){
			$_criteria -> order = $order;
		} else {
			$_criteria -> order = 'campaigns_creative.id ASC';
		}
		//$_criteria -> with=array(
		//		'action',
		//		'countries',
		//);
		
		$_results = $returnProvider ? new CActiveDataProvider($this, array( 'criteria'=>$_criteria)) : $this->findAll($_criteria);
		
		return $_results;
	}

	public function isOwner( $user_id )
	{
		return $user_id == $this->campaign->client_id;
	}
	
	protected function beforeSave()
	{
	    //check if file is changed
	    if(!empty($this->filename)){
	        //moving file to right place
	        $_fileTmpDir = Yii::app()->params['docTmpPath'];
	        $_fileNewDir = Yii::app()->params['rtbCreativeFileUploadsPath'];
	        $_fileTmpPath = $_fileTmpDir . DIRECTORY_SEPARATOR . $this->filename;
	        $_fileNewPath = $_fileNewDir . DIRECTORY_SEPARATOR . $this->filename;
	       
	        if(is_file($_fileTmpPath)){
	            rename($_fileTmpPath, $_fileNewPath);
	            return true;
	        }
	    }
	    return true;
	}
	
	protected function afterSave()
	{
        if($this->_isTypeIdsDirty()) {
            //refreshing types
            Yii::app()->db->createCommand(
                'DELETE FROM campaigns_creative_types_relations WHERE creative_id = :creative_id'
            )->execute(array(':creative_id' => $this->id));
            if (!empty($this->typesIds)) {
                foreach ($this->typesIds as $_id) {
                    Yii::app()->db->createCommand(
                        "INSERT INTO campaigns_creative_types_relations (creative_id, type_id) VALUES(:creative_id, :type_id)"
                    )->execute(array(':creative_id' => $this->id, ':type_id' => $_id));
                }
            }
        }

        if($this->_isCategoryIdsDirty()) {
            //refreshing categories
            Yii::app()->db->createCommand(
                'DELETE FROM campaigns_creative_category_relations WHERE creative_id = :creative_id'
            )->execute(array(':creative_id' => $this->id));
            if (!empty($this->categoryIds)) {
                foreach ($this->categoryIds as $_id) {
                    Yii::app()->db->createCommand(
                        "INSERT INTO campaigns_creative_category_relations (creative_id, category_id) VALUES(:creative_id, :category_id)"
                    )->execute(array(':creative_id' => $this->id, ':category_id' => $_id));
                }
            }
        }

        if($this->ids->isAttributeDirty('segmentsIds')){
            //refreshing segments
            Yii::app()->db->createCommand(
                'DELETE FROM creative_segments WHERE creative_id = :creative_id'
            )->execute(array(':creative_id' => $this->id));
            if (!empty($this->segmentsIds)) {
                foreach ($this->segmentsIds as $_id) {
                    Yii::app()->db->createCommand(
                        "INSERT INTO creative_segments (creative_id, segment_id) VALUES(:creative_id, :segment_id)"
                    )->execute(array(':creative_id' => $this->id, ':segment_id' => $_id));
                }
            }
        }
        
        return true;
	}

    protected function _isTypeIdsDirty()
    {
        return $this->cleanTypeIds != $this->typesIds;
    }

    protected function _isCategoryIdsDirty()
    {
        return $this->cleanCategoryIds != $this->categoryIds;
    }
	
	protected function afterFind()
	{
	    $this->_filename = $this->filename;
	    
	    $_types = Yii::app()->db->createCommand(
	        'SELECT type_id FROM campaigns_creative_types_relations WHERE creative_id = :id'
	    )->queryAll(true, array(':id' => $this->id));
	    foreach($_types as $_id){
	        $this->typesIds[] = $_id['type_id'];
	    }
        $this->cleanTypeIds = $this->typesIds;
	    
	    $_category = Yii::app()->db->createCommand(
	        'SELECT category_id FROM campaigns_creative_category_relations WHERE creative_id = :id'
	    )->queryAll(true, array(':id' => $this->id));
	    foreach($_category as $_id){
	        $this->categoryIds[] = $_id['category_id'];
	    }
        $this->cleanCategoryIds = $this->categoryIds;
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
            'ids' => array(
                'class' => 'application.components.behaviors.IdsBehavior',
                'attributes' => array('segments')
            ),
            'dirty' => array(
                'class' => 'application.components.behaviors.DirtyObjectBehavior'
            ),
		);
	}

    public function getSegmentsIds()
    {
        return $this->ids->segmentsIds;
    }

    public function setSegmentsIds($value)
    {
        $this->ids->segmentsIds = $value;
    }

    public function moderationRequired()
    {
        if (
            $this->is_created == 1 &&
            (
                $this->dirty->isAttributeChanged('filename') ||
                $this->dirty->isAttributeChanged('type') ||
                $this->dirty->isAttributeChanged('name') ||
                $this->_isTypeIdsDirty() ||
                $this->_isCategoryIdsDirty()
            )
        ){
            $this->status = CampaignsCreatives::YENDEX_MODERATION_SEND_ON_MOD;
            $this->to_update = True;
            $this->is_winner = False;
            return true;
        }

        return false;
    }

	public function getAvailableTypes()
	{
		return array(
		    self::TYPE_IMAGE => 'Картинка',
		    self::TYPE_AUDIO => 'Аудио',
		    self::TYPE_VIDEO => 'Видео'
		);
	}
	
	public function getCountShowsTotal()
	{
		return $this->count_shows_total;
	}
	
	public function getCountActionsTotal()
	{
		return $this->count_actions_total;
	}
	
	public function getCTR()
	{
		if ($this->count_actions_total == 0 && $this->count_shows_total == 0) {
			return '-';
		} else {
			if ( $this->count_shows_total != 0 ) {
				return round(($this->count_actions_total * 100) / $this->count_shows_total);
			} else {
				return '-';
			}
		}

	}

	public function getAuctionStatus($id = null, $status = null)
	{
        if(null === $id){ $id = $this->id; }
        if(null === $status) { $status = $this->status; }

        if ($status == 0 ) return "Ожидает создания";
        elseif ($status == 1 ) return "Создан";
        elseif ($status == 2 ) return "Ожидает модерацию";
        elseif ($status == 3 ) return "<a href='/' style='cursor: pointer' class='creative-rejection' creative_id='".$id."'>Причина отказа</a>";
        elseif ($status == 4 ) return "Участвует в аукционе";
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

    private function _groupPlaces($places)
    {
        $groups = array();
        foreach($places as $place){
            $size = $place['width'] . 'x' . $place['height'];
            if(!isset($groups[$place['bidfloor']][$size])){
                $groups[$place['bidfloor']][$size] = array($place['id']);
            }else{
                $groups[$place['bidfloor']][$size][] = $place['id'];
            }
        }
        krsort($groups);
        return $groups;
    }

    private function _createRTBSql($uid)
    {
        $isUid = empty($uid);
        return "SELECT t.*, ".($isUid ? "RAND()" : "IFNULL(ss.count, RAND())")." AS ss_count FROM `".$this->tableName()."` t "
            . ($isUid ? "" :
                "LEFT JOIN `creative_segments` cs ON cs.creative_id = t.id "
                . "LEFT JOIN `sessions_segments` ss ON ss.uid = ".$this->dbConnection->quoteValue($uid)." AND ss.segment_id = cs.segment_id "
              )
            . "WHERE t.size = :size AND cost >= :bidfloor AND is_active = 1 AND is_created = 1 AND status = 4 "
            . "__exclude_id__"
            . "AND (max_shows_hour = 0 OR max_shows_hour > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '2015-12-24 11:53:12' AND '2015-12-24 12:53:12') AND v.creative_id = t.id)) "
            . "AND (max_shows_day = 0 OR max_shows_day > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '2015-12-23 12:53:12' AND '2015-12-24 12:53:12') AND v.creative_id = t.id)) "
            . "AND (max_shows_week = 0 OR max_shows_week > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '2015-12-17 12:53:12' AND '2015-12-24 12:53:12') AND v.creative_id = t.id)) "
            . ($isUid ? "" : "GROUP BY t.id ")
            . "ORDER BY ss_count DESC "
            . "LIMIT :count";
    }

	/**
	 * Отдает креативы подходящие для RTB
	 *
	 */
	public function getCreativesForRTB($places, $uid)
	{

        $time = time();
        $nowDateTime = date('Y-m-d H:m:i', $time);
        $nowDateTimeMinusHour = date('Y-m-d H:m:i', $time - 60*60);
        $nowDateTimeMinusDay = date('Y-m-d H:m:i', $time - 60*60*24);
        $nowDateTimeMinusWeek = date('Y-m-d H:m:i', $time - 60*60*24*7);

        // рандомно выбираем креативы, так чтоб не пересекались, начиная с самого большого bidfloor

        $groups = CampaignsCreatives::_groupPlaces($places);

        $sql = $this->_createRTBSql($uid);
        $result = array();
        foreach($groups as $bidfloor => $sizes){
            foreach($sizes as $size => $placeIds) {
                $creatives = CampaignsCreatives::model()->findAllBySql(
                    str_replace(
                        '__exclude_id__',
                        (empty($result) ? "" : "AND t.`id` NOT IN (" . implode(',', array_keys($result)) . ") "),
                        $sql
                    ),
                    array(
                        ':size' => $size,
                        ':bidfloor' => $bidfloor,
                        ':count' => count($placeIds)
                    )
                );
                foreach($placeIds as $i => $placeId){
                    if(!isset($creatives[$i])){
                        break;
                    }
                    $result[$creatives[$i]->id] = array(
                        'placeId' => $placeId,
                        'creative' => $creatives[$i]
                    );
                }

//                $ids = $this->getDbConnection()->createCommand("SELECT t.`id` FROM `".$this->tableName()."` t "
//                    . "WHERE size = :size AND cost >= :bidfloor AND is_active = 1 AND is_created = 1 AND status = 4 "
//                    . (empty($result) ? "" : "AND t.`id` NOT IN (" . implode(',', array_keys($result)) . ") ")
//                    . "AND (max_shows_hour = 0 OR max_shows_hour > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '".$nowDateTimeMinusHour."' AND '".$nowDateTime."') AND v.creative_id = t.id)) "
//                    . "AND (max_shows_day = 0 OR max_shows_day > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '".$nowDateTimeMinusDay."' AND '".$nowDateTime."') AND v.creative_id = t.id)) "
//                    . "AND (max_shows_week = 0 OR max_shows_week > (SELECT COUNT(*) FROM `campaigns_creative_view_yandex` `v` WHERE (view_datetime BETWEEN '".$nowDateTimeMinusWeek."' AND '".$nowDateTime."') AND v.creative_id = t.id)) "
//                )->queryColumn(array(
//                    ':size' => $size,
//                    ':bidfloor' => $bidfloor,
//                ));
//                foreach ($placeIds as $placeId) {
//                    if(empty($ids)){ break ;}
//                    $key = array_rand($ids);
//                    $id = $ids[$key];
//                    unset($ids[$key]);
//                    $result[$id] = array(
//                        'placeId' => $placeId,
//                    );
//                }
            }
        }
//        $creatives = CampaignsCreatives::model()->findAllByPk(array_keys($result));
//        foreach($creatives as $creative){
//            $result[$creative->id]['creative'] = $creative;
//        }

        return $result;
	}

	public function updateCreativesBidRequestId( $bidRequestId, $creativeIds )
	{
        $this->getDbConnection()->createCommand()->update(
            $this->tableName(),
            array(
                'last_bid_request_id' => $bidRequestId,
                'is_winner' => 0
            ),
            $this->getDbConnection()->commandBuilder->createInCondition($this->tableName(), 'id', $creativeIds)
        );
	}

	public function addShow( $id )
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id', $id, true);
		$creative = self::model()->find($criteria);

		$creative->count_shows_total++;

		if ($creative->is_winner == 0 ) {
			$creative->is_winner = 1;
		}

		$creative->save();
	}

	public function addClick( $id )
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id', $id, true);
		$creative = self::model()->find($criteria);

		$creative->count_actions_total++;
		$creative->save();
	}

	/**
	 * Создает креатив на RTS
	 *
	 * @param int $creativeId
	 */
	public function addCreativeToRTSById( $creativeId )
	{
		$creative = CampaignsCreatives::model()->with('creativeTypes', 'creativeCategories')->findByPk($creativeId);

		$criteria=new CDbCriteria;
		$criteria->compare('campaign_id', $creative->attributes['campaign_id'] );

		$campaignsCountries = CampaignsCountries::model()->findAll( $criteria );

		foreach ($campaignsCountries as $countriesId) {
			$criteria=new CDbCriteria;
			$criteria->compare( 'id', $countriesId['country_id'] );

			$countries = Countries::model()->find( $criteria );
			if ( isset($countries) && !empty($countries) ) {
				if ( $countries->attributes['code'] == 'RU') {
					$geoItem[] = (object) array('id' => (int) self::GEO_RUSSIAN_ID, 'exclude' => false);
				} elseif ( $countries->attributes['code'] == 'UA' ) {
					$geoItem[] = (object) array('id' => (int) self::GEO_UKRAINE_ID, 'exclude' => false);
				}
			}
		}

		$campaign = Campaigns::model()->findByPk($creative->attributes['campaign_id']);
		$expireDate = date("Ymd\TH:i:s", strtotime($campaign->attributes['date_end']) );

		$mediaFileName = $creative->attributes['filename'];
		$mediaFileType = $creative->attributes['type'];

		if ( isset( $creative->creativeCategories ) && !empty( $creative->creativeCategories ) ) {
			foreach ($creative->creativeCategories as $category) {
				$creativeCategory = CampaignsCreativeYandexCategory::model()->find('id=:id', array(':id'=> $category->attributes['category_id'] ));
				$creativeCategories[] = (int) $creativeCategory->attributes['category_id'];
			}
		}

		if ( isset( $creative->creativeTypes ) && !empty( $creative->creativeTypes )) {
			foreach ($creative->creativeTypes as $key => $types) {
				$creativeType = CampaignsCreativeTypes::model()->find('id=:id', array(':id'=> $types->attributes['type_id']));
				$creativeTypes[] = $creativeType->attributes['name'];
			}

			if ( !empty( $creativeTypes ) ) {
				foreach ( $creativeTypes as $typeName) {
					if ( $typeName == 'Yandex') {
						$fileId = YandexRTB::fileUpload($mediaFileName, Transliteration::transliterate($creative->attributes['name']) , $mediaFileName);

						if ($fileId != false ) {
							$creativeName = $creative->attributes['name'];
							$click_url =  Yii::app()->params['YandexRTBClickUrl'] . $creativeId;

							$newCreative = YandexRTB::creativeCreate($mediaFileType, $mediaFileType, $creativeName, $expireDate, $fileId, $click_url );

							if ( !empty($newCreative['validationErrors'] ) )
							{
								$rejection_text = '';
								foreach ($newCreative['validationErrors'] as $validationError) {
									$rejection_text .= "<p>".$validationError['parameterName'].' - '.$validationError['errorMessage']. "</p>";
								}

								$creative->status = self::YENDEX_MODERATION_REJECTED;
								$creative->rejection = $rejection_text;
							} else {
								$RTBCreativeId = $newCreative['descr']['id'];
								$updateTnsArticleStatus = YandexRTB::creativeUpdateTnsArticle( (int) $RTBCreativeId, $creativeCategories);
								$creativeUpdateTnsBrandStatus = YandexRTB::creativeUpdateTnsBrand( (int) $RTBCreativeId, array( self::YANDEX_DEFAULT_BRAND ) );

								if ( isset( $geoLocation ) ) {
									$geoItem = array( (object) array('id' => $geoLocation, 'exclude' => false) );

									YandexRTB::creativeUpdateGeo( (int) $RTBCreativeId, $geoItem );
								}

								if ( $updateTnsArticleStatus == true && $creativeUpdateTnsBrandStatus == true ) {
									$creative->is_created = 1;
									$creative->rtb_id = $newCreative['descr']['id'];

									$moderation = YandexRTB::creativeRequestModeration($newCreative['descr']['id']);
									if ( $moderation != false ) {
										$creative->status = self::YENDEX_MODERATION_SEND_ON_MOD;
									}else {
                                        $creative->status = self::YENDEX_MODERATION_REJECTED;
                                    }
								}
							}

							$creative->update( array('is_created', 'rtb_id', 'status', 'rejection') );
						}
					} elseif ( $typeName == 'Google' ) {
						//GoogleRTB::createCreative();
					}
				}
			}
		}
	}

	public function updateCreativeToRTSById( $creativeId, $RTBCreativeId )
	{
		$creativeStatus = YandexRTB::creativeGet((int) $RTBCreativeId, 0);

		if ( $creativeStatus['status'] != self::YENDEX_MODERATION_CREATED)
			YandexRTB::creativeRequestEdit( (int) $RTBCreativeId );

		$creative = CampaignsCreatives::model()->with('creativeTypes', 'creativeCategories')->findByPk($creativeId);

		$criteria=new CDbCriteria;
		$criteria->compare('campaign_id', $creative->attributes['campaign_id'] );

		$campaignsCountries = CampaignsCountries::model()->findAll( $criteria );

		foreach ($campaignsCountries as $countriesId) {
			$criteria=new CDbCriteria;
			$criteria->compare( 'id', $countriesId['country_id'] );

			$countries = Countries::model()->find( $criteria );
			if ( isset($countries) && !empty($countries) ) {
				if ( $countries->attributes['code'] == 'RU') {
					$geoItem[] = (object) array('id' => (int) self::GEO_RUSSIAN_ID, 'exclude' => false);
				} elseif ( $countries->attributes['code'] == 'UA' ) {
					$geoItem[] = (object) array('id' => (int) self::GEO_UKRAINE_ID, 'exclude' => false);
				}
			}
		}

		$campaign = Campaigns::model()->findByPk($creative->attributes['campaign_id']);

		$expireDate = date("Ymd\TH:i:s", strtotime($campaign->attributes['date_end']) );

		$mediaFileName = $creative->attributes['filename'];
		$mediaFileType = $creative->attributes['type'];

		if ( isset( $creative->creativeCategories ) && !empty( $creative->creativeCategories )) {
			foreach ($creative->creativeCategories as $category) {
				$creativeCategory = CampaignsCreativeYandexCategory::model()->find('id=:id', array(':id'=> $category->attributes['category_id'] ));
				$creativeCategories[] = (int) $creativeCategory->attributes['category_id'];
			}
		}

		if ( isset( $creative->creativeTypes ) && !empty( $creative->creativeTypes ) && !empty($creativeCategories)) {
			foreach ($creative->creativeTypes as $key => $types) {
				$creativeType = CampaignsCreativeTypes::model()->find('id=:id', array(':id'=> $types->attributes['type_id']));
				$creativeTypes[] = $creativeType->attributes['name'];
			}

			foreach ( $creativeTypes as $typeName) {
				if ( $typeName == 'Yandex') {
					$fileId = YandexRTB::fileUpload($mediaFileName, Transliteration::transliterate($creative->attributes['name']), $mediaFileName);

					if (!empty($fileId) && isset($fileId)) {
						$creativeId = $creative->attributes['id'];
						$creativeName = $creative->attributes['name'];
						$click_url = Yii::app()->params['YandexRTBClickUrl'] . $creativeId;

						$creativeUpdate = YandexRTB::creativeUpdate((int) $RTBCreativeId, (int)$mediaFileType, $creativeName, $fileId, $click_url);

						if ( !empty($creativeUpdate) ) {
							if ( !empty($creativeUpdate['validationErrors'] ) )
							{
								$rejection_text = '';
								foreach ($creativeUpdate['validationErrors'] as $validationError) {
									$rejection_text .= "<p>".$validationError['parameterName'].' - '.$validationError['errorMessage']. "</p>";
								}

								$creative->status = self::YENDEX_MODERATION_REJECTED;
								$creative->rejection = $rejection_text;
							} else {
								$updateTnsArticleStatus = YandexRTB::creativeUpdateTnsArticle( (int) $RTBCreativeId, $creativeCategories);
								$creativeUpdateTnsBrandStatus = YandexRTB::creativeUpdateTnsBrand( (int) $RTBCreativeId, array( self::YANDEX_DEFAULT_BRAND ) );
								$updateCreativeSignedExpireDate = YandexRTB::updateCreativeSignedExpireDate( (int) $RTBCreativeId, $expireDate );

								if ( isset( $geoItem ) ) {
									YandexRTB::creativeUpdateGeo( (int) $creative->attributes['rtb_id'], $geoItem );
								}

								if ( $updateTnsArticleStatus == 1 && $creativeUpdateTnsBrandStatus == 1 && $updateCreativeSignedExpireDate == 1 ) {
									$moderation = YandexRTB::creativeRequestModeration( (int) $RTBCreativeId );
									if ( $moderation != false ) {
                                        $creative->status = self::YENDEX_MODERATION_SEND_ON_MOD;
                                        $creative->to_update = False;
									}else{
                                        $creative->status = self::YENDEX_MODERATION_REJECTED;
                                    }
								}
							}

							$creative->update( array('is_created', 'status', 'to_update', 'rejection') );
						}
					}

				} elseif ( $typeName == 'Google' ) {
					//GoogleRTB::createCreative();
				}
			}
		}
	}

	public function checkCreativeStatusToRTSById( $creativeId )
	{
		$creative = CampaignsCreatives::model()->findByPk( $creativeId );

		$creativeStatus = YandexRTB::creativeGet( (int) $creative->attributes['rtb_id'], 0);

        if(false !== $creativeStatus) {
            $rejection_text = "";

            if ($creativeStatus['status'] == self::YENDEX_MODERATION_REJECTED) {
                foreach ($creativeStatus['rejectReasons'] as $reason) {
                    $rejection_text .= "<p>" . $reason['errorMessage'] . "</p>";
                }

                $creative->rejection = $rejection_text;
                $creative->creative_data = '';
            } elseif ($creativeStatus['status'] == self::YENDEX_MODERATION_CONFIRMED) {
                $creative->rejection = "";
                $creativeData = YandexRTB::getCreativeByNmb((int)$creative->attributes['rtb_id'], 0);
                $creative->creative_data = json_encode($creativeData);
            }

            $creative->status = $creativeStatus['status'];
            $creative->update(array('status', 'rejection', 'creative_data'));
        }
	}

	public function go_test() {
		GoogleRTB::creativeCreate();
	}

	public function ya_test()
	{
		print_r( YandexRTB::tnsArticleGetList() );
		//file_put_contents('/home/tox/log.txt', json_encode('cron') );
	}

	public function getAvailableSizes()
	{
		return array(
			'240x400' => '240x400',
			'300x300' => '300x300',
			'1000x120' => '1000x120',
			'160x600' => '160x600',
			'300x250' => '300x250',
			'728x90' => '728x90'
		);
	}

    public function checkIsActive()
    {
        if($this->is_active && Campaigns::model()->checkIsActive($this->campaign_id, false, false)){
            return true;
        }

        return false;
    }



}