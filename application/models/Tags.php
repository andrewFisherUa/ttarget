<?php

/**
 * This is the model class for table "tags".
 *
 * The followings are the available columns in table 'tags':
 * @property string $id
 * @property string $name
 * @property integer $is_public
 *
 * The followings are the available model relations:
 * @property Teasers[] $teasers
 * @property Platforms[] $platforms
 */
class Tags extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tags the static model class
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
		return 'tags';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>32),
            array('is_public', 'length', 'max'=>3),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
            'platforms' => array(self::MANY_MANY, 'Platforms', 'platforms_tags(tag_id, platform_id)'),
            'teasers'   => array(self::MANY_MANY, 'Teasers', 'teasers_tags(tag_id, teaser_id)'),
			'offers'    => array(self::MANY_MANY, 'Offers', 'offers_tags(tag_id, offer_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название',
            'is_public' => 'Публичный'
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
		$criteria->compare('name',$this->name,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function canDelete($tag_id){
        $cmd = $this->getDbConnection()->createCommand();
        $cmd->select('count(tag_id)');
        $cmd->from('platforms_tags');
        $cmd->where('tag_id=:tag_id', array('tag_id' => $tag_id));
        $p_count = $cmd->queryScalar();

        $cmd = $this->getDbConnection()->createCommand();
        $cmd->select('count(tag_id)');
        $cmd->from('teasers_tags');
        $cmd->where('tag_id=:tag_id', array('tag_id' => $tag_id));
        $t_count = $cmd->queryScalar();

        return ($p_count == 0 && $t_count == 0);
	}

	public function findTagsUsedByPlatforms()
	{
		$_tags = array();
		
		$criteria = new CDbCriteria();
		$criteria -> addCondition('id IN (SELECT DISTINCT tag_id FROM platforms_tags)');
		$criteria -> order = 'name ASC';
		$_tagsList = $this->findAll($criteria);
		foreach($_tagsList as $_tag){
			$_tags[$_tag->id] = $_tag->name;
		}
		return $_tags;
	}
}