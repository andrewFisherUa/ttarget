<?php

/**
 * This is the model class for table "segments".
 *
 * The followings are the available columns in table 'segments':
 * @property string $id
 * @property string $parent_id
 * @property string $name
 * @property string $path
 *
 * The followings are the available model relations:
 * @property CampaignsCreative[] $campaignsCreatives
 * @property Segments $parent
 * @property Segments[] $segments
 */
class Segments extends CActiveRecord
{
    private $_updatePath = false;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Segments the static model class
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
		return 'segments';
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
			array('parent_id', 'length', 'max'=>10),
			array('name', 'length', 'max'=>255),
			array('path', 'length', 'max'=>2048),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, parent_id, name, path', 'safe', 'on'=>'search'),
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
			'campaignsCreatives' => array(self::MANY_MANY, 'CampaignsCreative', 'creative_segments(segment_id, creative_id)'),
			'parent' => array(self::BELONGS_TO, 'Segments', 'parent_id'),
			'segments' => array(self::HAS_MANY, 'Segments', 'parent_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'parent_id' => 'Родитель',
			'name' => 'Название',
			'path' => 'Path',
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
		$criteria->compare('parent_id',$this->parent_id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('path',$this->path,true);
        $criteria->order = 'path';

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
            'pagination' => false,
		));
	}

    protected function beforeSave()
    {
        if(!empty($this->parent_id)){
            $path = $this->parent->path . '/' . str_replace(' ', '_', $this->name);
        }else{
            $path = str_replace(' ', '_', $this->name);
            $this->parent_id = null;
        }
        if(!$this->isNewRecord && $this->path != $path){
           $this->_updatePath = true;
        }
        $this->path = $path;

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if($this->_updatePath){
            foreach($this->segments as $segment){
                $segment->save(false);
            }
        }
        return parent::afterSave();
    }

    public static function getTreeHtml($values)
    {
        $result = '';
        $oldLvl = -1;
        foreach(Segments::model()->getOrderedSegments() as $segment){
            $lvl = $segment->getLvl();
            if($lvl == $oldLvl){
                $result .= "</li>\n";
            }
            if($lvl > $oldLvl){
                $result .= "<ul>\n";
            }elseif($lvl < $oldLvl){
                $result .= str_repeat('</li></ul>', $oldLvl - $lvl)."\n";
            }
            $oldLvl = $lvl;
            $result .= '<li data-id="'.$segment->id.'" '
                . (in_array($segment->id, $values) ? "data-jstree='{\"selected\" : true}'" : "")
                . '>'.$segment->name;
        }

        $result .= '</ul>';

        return $result;
    }

    /**
     * @return Segments[]
     */
    public function getOrderedSegments($path = null)
    {
        $criteria = new CDbCriteria(array('order' => 'path'));
        if(null !== $path){
            $criteria->condition = 'path LIKE :path';
            $criteria->params = array(':path' => $path . '%');
        }
        return $this->findAll($criteria);
    }

    public function getLvl()
    {
        return substr_count($this->path, '/');
    }

    public function getPaddedName()
    {
        return str_repeat('----', $this->getLvl()) . $this->name;
    }
}