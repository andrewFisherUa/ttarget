<?php

/**
 * This is the model class for table "pages".
 *
 * The followings are the available columns in table '[ages':
 * @property string $id
 * @property string $url
 * @property array $segmentsIds
 *
 * Behaviors
 * @property IdsBehavior $ids
 * @property DirtyObjectBehavior $dirty
 *
 * The followings are the available model relations:
 * @property Segments[] $segments
 */
class Pages extends CActiveRecord
{

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Pages the static model class
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
		return 'pages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('url', 'required'),
			array('url', 'length', 'max'=>2048),
            array('url', 'url', 'defaultScheme' => 'http://', 'validateIDN' => false),
            array('segmentsIds', 'type', 'type' => 'array'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, url', 'safe', 'on'=>'search'),
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
			'segments' => array(self::MANY_MANY, 'Segments', 'pages_segments(page_id, segment_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'url' => 'Url',
            'segments' => 'Сегменты'
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
		$criteria->compare('url',$this->url,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function behaviors()
    {
        return array(
            'ids' => array(
                'class' => 'application.components.behaviors.IdsBehavior',
                'attributes' => array('segments')
            ),
            'dirty' => array(
                'class' => 'application.components.behaviors.DirtyObjectBehavior'
            ),
            'relations' => array(
                'class' => 'ext.yiiext.behaviors.EActiveRecordRelationBehavior'
            ),
        );
    }

    public function getSegmentsIds()
    {
        return $this->ids->segmentsIds;
    }

    public function setSegmentsIds($value)
    {
        $this->ids->segmentsIds = $value;
    }

    protected function afterSave()
    {
        if($this->dirty->isAttributeChanged('url')){
            RedisPages::instance()->addPage($this);
        }
        return parent::afterSave();
    }

    protected function afterDelete()
    {
        RedisPages::instance()->delPage($this);
        return parent::afterDelete();
    }

    protected function beforeValidate()
    {
        $this->url = IDN::encodeURL($this->url);
        return parent::beforeValidate();
    }

    public function getSegmentsHtml()
    {
        $result = array();
        foreach($this->segments as $segment){
            $result[] = $segment->name;
        }
        return implode(' | ', $result);
    }

}