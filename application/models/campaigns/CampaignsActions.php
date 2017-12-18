<?php

/**
 * This is the model class for table "campaigns_actions".
 *
 * The followings are the available columns in table 'campaigns_actions':
 * @property string $id
 * @property string $campaign_id
 * @property string $target_type
 * @property string $target
 * @property string $name
 * @property string $description
 * @property string $target_match_type
 * @property integer $is_deleted
 * @property string $cost
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 */
class CampaignsActions extends CActiveRecord
{
    const ACTION_KEY = 'ttarget:actions:%s';

    const TARGET_TYPE_URL = 'url';
    const TARGET_TYPE_CLICK = 'click';

    const TARGET_MATCH_TYPE_CONTAIN = 'contain';
    const TARGET_MATCH_TYPE_MATCH = 'match';
    const TARGET_MATCH_TYPE_BEGIN = 'begin';
    const TARGET_MATCH_TYPE_REGEXP = 'regexp';

    /**
    *	Find actions for campaign
    *	@param int $campaign_id
    **/
    public function findByCampaignId($campaign_id)
    {
    	$criteria = new CDbCriteria();
    	$criteria->compare('campaign_id', $campaign_id);
    	return $this->findAll($criteria);
    }
    
    public function getAvailableTargetTypes()
    {
        return array(
            self::TARGET_TYPE_URL => 'URL',
            self::TARGET_TYPE_CLICK => 'Click',
        );
    }

    public function getAvailableMatchTypes($target_type){
        if($target_type === null){
            $target_type = $this->target_type;
        }
        if($target_type == self::TARGET_TYPE_URL){
            return array(
                self::TARGET_MATCH_TYPE_CONTAIN => 'Содержит',
                self::TARGET_MATCH_TYPE_MATCH => 'Совпадает',
                self::TARGET_MATCH_TYPE_BEGIN => 'Начинается',
                self::TARGET_MATCH_TYPE_REGEXP => 'RegExp'
            );
        }else{
            return array(
                self::TARGET_MATCH_TYPE_MATCH => 'Совпадает'
            );
        }
    }

    /**
     * Возвращает данные цели
     *
     * @param $action_id
     * @return array
     * @throws CException
     */
    public static function getById($action_id)
    {
        $result = Yii::app()->redis->hGetAll(sprintf(self::ACTION_KEY, $action_id));
        if(!$result){
            throw new CException('Cant find action in redis by id: '.$action_id);
        }
        return $result;
    }

    public function validateRegexp($attribute){
        if($this->target_match_type == self::TARGET_MATCH_TYPE_REGEXP){

            if(preg_match('#^\/.+/[^\/]*$#', $this->$attribute) === false || @preg_match($this->$attribute, null) === false){
                $this->addError($attribute, 'Не правильное регулярное выражение');
            }
        }
    }

    /**
     * @return string
     */
    public function getEncryptedId()
    {
        return Crypt::encryptUrlComponent($this->id);
    }

    public function checkIsActive(){
        return $this->is_deleted != 1;
    }

    protected function afterSave()
    {
        if($this->dirty->isDirty()){
            if($this->is_deleted){
                Yii::app()->resque->createJob('app', 'ActionDelFromRedisJob', array('action_id' => $this->id));
            }else{
                Yii::app()->resque->createJob('app', 'ActionAddToRedisJob', array('action_id' => $this->id));
            }
        }

        parent::afterSave();
    }

    public function behaviors()
    {
        return array(
            'dirty' => array(
                'class' => 'application.components.behaviors.DirtyObjectBehavior'
            )
        );
    }

    /**
     * @return CampaignsActions Именованная группа для выборки не удаленных целей
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
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsActions the static model class
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
		return 'campaigns_actions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, target_type, target, name', 'required'),
			array('campaign_id, cost', 'length', 'max'=>10),
			array('target_type', 'length', 'max'=>5),
			array('target', 'length', 'max'=>512),
			array('name, description', 'length', 'max'=>255),
            array('target_type', 'in', 'range' => array_keys($this->getAvailableTargetTypes())),
            array('target', 'unique', 'criteria' => array(
                'condition' => 'campaign_id = :campaign_id AND is_deleted = 0',
                'params' => array(':campaign_id' => $this->campaign_id)
            )),
            array('target', 'validateRegexp', 'on' => self::TARGET_TYPE_URL),
            array('target_match_type', 'in', 'range' => array_keys($this->getAvailableMatchTypes(null))),
            array('cost', 'numerical', 'numberPattern' => '/^\d+(\.\d\d?)?$/'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaign_id, target_type, target, name, description', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'campaign_id' => 'Campaign',
			'target_type' => 'Тип цели',
			'target' => 'Цель',
			'name' => 'Нзвание',
			'description' => 'Описание',
            'target_match_type' => 'Target Match Type',
            'cost' => 'Стоимость',
		);
	}
}