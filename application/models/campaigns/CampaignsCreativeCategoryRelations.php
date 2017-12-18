<?php

/**
 * This is the model class for table "campaigns_creative_category_relations".
 *
 * The followings are the available columns in table 'campaigns_creative_category_relations':
 * @property string $id
 * @property string $creative_id
 * @property string $category_id
 * @property string $type_id
 *
 * The followings are the available model relations:
 * @property CampaignsCreativeTypes $type
 * @property CampaignsCreative $creative
 * @property CampaignsCreativeYandexCategory $category
 */
class CampaignsCreativeCategoryRelations extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsCreativeCategoryRelations the static model class
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
		return 'campaigns_creative_category_relations';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('creative_id, category_id', 'required'),
			array('creative_id, category_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, creative_id, category_id', 'safe', 'on'=>'search'),
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
			'creative' => array(self::BELONGS_TO, 'CampaignsCreative', 'creative_id'),
			'category' => array(self::BELONGS_TO, 'CampaignsCreativeYandexCategory', 'category_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'creative_id' => 'Creative',
			'category_id' => 'Category'
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
		$criteria->compare('creative_id',$this->creative_id,true);
		$criteria->compare('category_id',$this->category_id,true);
		$criteria->compare('type_id',$this->type_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}