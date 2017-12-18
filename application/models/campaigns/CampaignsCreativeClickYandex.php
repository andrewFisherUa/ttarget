<?php

/**
 * This is the model class for table "campaigns_creative_click_yandex".
 *
 * The followings are the available columns in table 'campaigns_creative_click_yandex':
 * @property string $id
 * @property string $click_datetime
 * @property string $campaign_id
 * @property string $creative_id
 * @property string $ip
 */
class CampaignsCreativeClickYandex extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsCreativeClickYandex the static model class
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
		return 'campaigns_creative_click_yandex';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, ip', 'required'),
			array('campaign_id, creative_id', 'length', 'max'=>11),
			array('ip', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, click_datetime, campaign_id, creative_id, ip', 'safe', 'on'=>'search'),
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
			'click_datetime' => 'Click Datetime',
			'campaign_id' => 'Campaign',
			'creative_id' => 'Creative',
			'ip' => 'Ip',
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
		$criteria->compare('click_datetime',$this->click_datetime,true);
		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('creative_id',$this->creative_id,true);
		$criteria->compare('ip',$this->ip,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function addClick( $id )
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id', $id, true);
		$creative = CampaignsCreatives::model()->find($criteria);

		$creativeClick = new CampaignsCreativeClickYandex;

		$creativeClick->ip = $_SERVER['SERVER_ADDR'];
		$creativeClick->creative_id = $id;
		$creativeClick->campaign_id = $creative->campaign_id;

		$creativeClick->save();
	}

}