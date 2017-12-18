<?php

/**
 * This is the model class for table "report_rtb_daily_by_platform".
 *
 * The followings are the available columns in table 'report_rtb_daily_by_platform':
 * @property string $platform_id
 * @property string $date
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDailyByPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDailyByPlatform the static model class
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
		return 'report_rtb_daily_by_platform';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('platform_id, date', 'required'),
			array('platform_id', 'length', 'max'=>10),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('platform_id, date, shows, clicks', 'safe', 'on'=>'search'),
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
			'platform_id' => 'Platform',
			'date' => 'Date',
			'shows' => 'Shows',
			'clicks' => 'Clicks',
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

		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}