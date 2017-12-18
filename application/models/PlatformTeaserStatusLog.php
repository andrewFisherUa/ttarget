<?php

/**
 * This is the model class for table "platforms_teaser_block_status_log".
 *
 * The followings are the available columns in table 'platforms_teaser_block_status_log':
 * @property string $id
 * @property string $platform_id
 * @property string $init_time
 * @property string $complete_time
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 */
class PlatformTeaserStatusLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PlatformTeaserStatusLog the static model class
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
		return 'platforms_teaser_block_status_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('platform_id', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('platform_id', 'length', 'max'=>10),
			array('init_time, complete_time', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, platform_id, init_time, complete_time, status', 'safe', 'on'=>'search'),
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
			'init_time' => 'Init Time',
			'complete_time' => 'Complete Time',
			'status' => 'Status',
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
		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('init_time',$this->init_time,true);
		$criteria->compare('complete_time',$this->complete_time,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}