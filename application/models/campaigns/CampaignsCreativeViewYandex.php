<?php

/**
 * This is the model class for table "campaigns_creative_view_yandex".
 *
 * The followings are the available columns in table 'campaigns_creative_view_yandex':
 * @property string $id
 * @property string $view_datetime
 * @property string $campaign_id
 * @property string $creative_id
 * @property string $ip
 */
class CampaignsCreativeViewYandex extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsCreativeViewYandex the static model class
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
		return 'campaigns_creative_view_yandex';
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
			array('id, view_datetime, campaign_id, creative_id, ip', 'safe', 'on'=>'search'),
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
			'view_datetime' => 'View Datetime',
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
		$criteria->compare('view_datetime',$this->view_datetime,true);
		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('creative_id',$this->creative_id,true);
		$criteria->compare('ip',$this->ip,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function addShow( $id )
	{
		$criteria=new CDbCriteria;

		$criteria->compare('id', $id, true);
		$creative = CampaignsCreatives::model()->find($criteria);

		$creativeVeiw = new CampaignsCreativeViewYandex();

		$creativeVeiw->ip = $_SERVER['SERVER_ADDR'];
		$creativeVeiw->creative_id = $id;
		$creativeVeiw->campaign_id = $creative->campaign_id;

		$creativeVeiw->save();
	}

}