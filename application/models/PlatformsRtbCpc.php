<?php

/**
 * This is the model class for table "platforms_rtb_cpc".
 *
 * The followings are the available columns in table 'platforms_rtb_cpc':
 * @property string $id
 * @property string $date
 * @property string $platform_id
 * @property string $cost
 *
 * The followings are the available model relations:
 * @property RtbPlatforms $platform
 */
class PlatformsRtbCpc extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return PlatformsRtbCpc the static model class
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
		return 'platforms_rtb_cpc';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('date, platform_id, cost', 'required'),
			array('platform_id, cost', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, date, platform_id, cost', 'safe', 'on'=>'search'),
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
			'platform' => array(self::BELONGS_TO, 'YandexPlatforms', 'platform_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'date' => 'Date',
			'platform_id' => 'Platform',
			'cost' => 'Cost',
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
		$criteria->compare('date',$this->date,true);
		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('cost',$this->cost,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function addCost( $cost, $bidRequestData )
	{
		$current_date = date("Y-m-d");

		$criteria=new CDbCriteria;
		$criteria->compare('date', $current_date );

		$platformsRtbCpc = self::model()->find( $criteria );

		if ( !empty($bidRequestData) ) {
			$platform_id = $bidRequestData->site->id;
		} else {
			$platform_id = 928374;// for google
		}

		if ( count( $platformsRtbCpc ) == 0) {
			$platformsRtbCpc = new PlatformsRtbCpc;
			$platformsRtbCpc->date = $current_date;
			$platformsRtbCpc->platform_id = $platform_id;
			$platformsRtbCpc->cost = $cost;

			$platformsRtbCpc->save();
		} else {
			$platformsRtbCpc->cost = $cost;
			$platformsRtbCpc->save();
		}
	}
}