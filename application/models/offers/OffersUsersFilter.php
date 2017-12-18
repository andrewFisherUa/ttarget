<?php

/**
 * This is the model class for table "offers_users_filter".
 *
 * The followings are the available columns in table 'offers_users_filter':
 * @property string $id
 * @property string $offer_id
 * @property string $user_id
 * @property integer $type
 *
 * The followings are the available model relations:
 * @property Offers $offer
 * @property Users $user
 */
class OffersUsersFilter extends CActiveRecord
{
	const FILTER_TYPE_ALLOWED = 1;
	const FILTER_TYPE_DENIED = 0;
	
	public function findByOfferId( $offer_id, $type = self::FILTER_TYPE_ALLOWED )
	{
		$_criteria = new CDbCriteria();
		$_criteria -> addCondition('offer_id = :offer_id');
		$_criteria -> addCondition('type = :type');
		$_criteria -> with = 'user';
		$_criteria -> params = array(
			':offer_id' => $offer_id,
			':type' => ($type == self::FILTER_TYPE_ALLOWED ? self::FILTER_TYPE_ALLOWED : self::FILTER_TYPE_DENIED)
		);
		return $this->findAll($_criteria);
	}
	
	public function deleteByOfferId($offer_id)
	{
		$_criteria = new CDbCriteria();
		$_criteria -> addCondition('offer_id = :offer_id');
		$_criteria -> params = array(':offer_id' => $offer_id);
		return $this->deleteAll($_criteria);
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OffersUsersFilter the static model class
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
		return 'offers_users_filter';
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
			array('type', 'numerical', 'integerOnly'=>true),
			array('offer_id, user_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, offer_id, user_id, type', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'offer_id' => 'Offer',
			'user_id' => 'User',
			'type' => 'Type',
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
		$criteria->compare('offer_id',$this->offer_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('type',$this->type);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}