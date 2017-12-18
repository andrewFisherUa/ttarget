<?php

/**
 * This is the model class for table "yandex_platforms".
 *
 * The followings are the available columns in table 'yandex_platforms':
 * @property string $id
 * @property integer $paltform_id
 * @property string $created
 * @property string $domain
 * @property string $refer
 * @property string $ip
 */
class YandexPlatforms extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return YandexPlatforms the static model class
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
		return 'yandex_platforms';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('platform_id, domain, referer', 'required'),
			array('platform_id', 'numerical', 'integerOnly'=>true),
			array('domain, referer', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, platform_id, created, domain, referer', 'safe', 'on'=>'search'),
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
			'platform_id' => 'Platform',
			'created' => 'Created',
			'domain' => 'Domain',
			'referer' => 'Referer',
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
		$criteria->compare('platform_id',$this->platform_id);
		$criteria->compare('created',$this->created,true);
		$criteria->compare('domain',$this->domain,true);
		$criteria->compare('referer',$this->referer,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function addPlatform($data)
	{
		$site = $data['site'];

		$criteria = new CDbCriteria();
		$criteria->compare( 'platform_id', $site['id'] );

		$platform = YandexPlatforms::model()->find($criteria);

		if ( empty( $platform ) && !isset( $platform )) {
			$pl = new YandexPlatforms;

			$pl->platform_id = $site['id'];
			$pl->domain = $site['domain'];
			$pl->referer = $site['referer'];

			$pl->save();

			file_put_contents('/home/tox/log.txt', json_encode($pl->errors) );
		}
	}

	public function addGoolePlatform()
	{

		$test_google_platform_id = 928374;
		$criteria = new CDbCriteria();
		$criteria->compare( 'platform_id', $test_google_platform_id );

		$platform = YandexPlatforms::model()->find($criteria);

		if ( empty( $platform ) && !isset( $platform )) {
			$pl = new YandexPlatforms;

			$pl->platform_id = $test_google_platform_id;
			$pl->domain = "google.ru";
			$pl->referer = "http://data.google.ru/";

			$pl->save();
			file_put_contents('/home/tox/log.txt', json_encode($pl->errors) );
		}
	}
}