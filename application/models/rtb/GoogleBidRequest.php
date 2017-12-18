<?php

/**
 * This is the model class for table "google_bid_request".
 *
 * The followings are the available columns in table 'google_bid_request':
 * @property string $id
 * @property string $created
 * @property string $json_data
 */
class GoogleBidRequest extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return GoogleBidRequest the static model class
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
		return 'google_bid_request';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('json_data', 'required'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, created, json_data', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'created' => 'Created',
			'json_data' => 'Json Data',
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
		$criteria->compare('created',$this->created,true);
		$criteria->compare('json_data',$this->json_data,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public static function createBidRequest($data)
	{
		$googleBidReques = new GoogleBidRequest;
		$googleBidReques->json_data = json_encode($data);

		$googleBidReques->save();
		//file_put_contents('/home/tox/log.txt', json_encode(utf8_decode($googleBidReques->errors['created'][0])) );

		return $googleBidReques->id;
	}

	public static function getBidRequestDataById($bidRequestId)
	{
		$criteria=new CDbCriteria;
		$criteria->compare('id', $bidRequestId );

		$bidRequestData = self::model()->find($criteria);

		if ( !empty($bidRequestData) ) {
			return json_decode( $bidRequestData->json_data );
		} else {
			return false;
		}
	}
}