<?php

/**
 * This is the model class for table "notifications".
 *
 * The followings are the available columns in table 'notifications':
 * @property string $id
 * @property string $platform_id
 * @property string $teaser_id
 * @property string $create_date
 * @property string $action
 * @property integer $is_new
 * @property integer $user_id
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 * @property Teasers $teaser
 * @property Users $user
 */
class Notifications extends CActiveRecord
{
    public $name;
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Notifications the static model class
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
		return 'notifications';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
//			array('create_date', 'required'),
			array('is_new, platform_id, teaser_id, user_id', 'numerical', 'integerOnly'=>true),
			array('platform_id, teaser_id, user_id', 'length', 'max'=>10),
			array('action', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, platform_id, teaser_id, create_date, action, is_new, name', 'safe', 'on'=>'search'),
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
			'platform' => array(self::BELONGS_TO, 'Platforms', 'platform_id'),
			'teaser' => array(self::BELONGS_TO, 'Teasers', 'teaser_id'),
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'platform_id' => 'Platform',
			'teaser_id' => 'Teaser',
			'create_date' => 'Create Date',
			'action' => 'Action',
			'is_new' => 'Is New',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search( $onlyNew = false, $perPage = -1 )
	{

        $criteria=new CDbCriteria;

        $criteria->with = array('teaser.news.campaign', 'platform', 'user');
        $criteria->compare('t.create_date',$this->name,true, 'OR');
        $criteria->compare('teaser.title',$this->name,true, 'OR');
        $criteria->compare('campaign.name',$this->name,true, 'OR');
        $criteria->compare('platform.server',$this->name,true, 'OR');
        $criteria->compare('user.login',$this->name,true, 'OR');
        $criteria->compare('user.email',$this->name,true, 'OR');
        
        if($onlyNew){
        	$criteria->compare('is_new', 1);
        }

        if($perPage > 0) {
        	$_pageSize = $perPage;
        } else {
        	$_pageSize = 10;
        }
        
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'sort'=>array(
                'attributes'=>array(
                    'create_date', 'platform.server', 'campaign.name', 'user.login'
                ),
                'defaultOrder' => array(
                    'create_date' => CSort::SORT_DESC,
                )
            ),
        	'pagination' => array(
        		'pageSize' => $_pageSize
        	)
        ));
    }

    public function add($platform_id, $teaser_id){
        $notification = new Notifications();
        $notification->user_id = Yii::app()->user->id;
        $notification->platform_id=$platform_id;
        $notification->teaser_id=$teaser_id;
        return $notification->save();
    }

    public function getNewCount()
    {
        return $this->countByAttributes(array('is_new' => 1));
    }

    public function behaviors()
    {
        return array(
            'timestamps' => array(
                'class'                 => 'zii.behaviors.CTimestampBehavior',
                'createAttribute'       => 'create_date',
                'updateAttribute'       => null,
                'timestampExpression'   => new CDbExpression('now()'),
            ),
        );
    }

	public function changeNewAll( $ids = array(), $save = true )
	{
		$criteria = new CDbCriteria();
		$criteria -> addInCondition('id', $ids);
		$this->updateAll(array('is_new' => 0), $criteria);
	}
}