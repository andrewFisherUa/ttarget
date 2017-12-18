<?php

/**
 * This is the model class for table "tracks".
 *
 * The followings are the available columns in table 'tracks':
 * @property string $id
 * @property string $campaign_id
 * @property string $platform_id
 * @property integer $is_deleted
 * @property string $created_date
 * @property string $revoked_date
 * @property string $offer_user_id
 * @property string $action_eid
 * @property string $referrer_url
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 * @property Platforms $platform
 *
 * @method Tracks findByPk()
 */
class Tracks extends CActiveRecord
{
    private $_custom_attributes;
    private $_custom_attributes_changed = false;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tracks the static model class
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
		return 'tracks';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, campaign_id, created_date', 'required'),
			array('is_deleted', 'numerical', 'integerOnly'=>true),
			array('id, campaign_id, platform_id, offer_user_id', 'length', 'max'=>10),
			array('revoked_date', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaign_id, platform_id, is_deleted, created_date, revoked_date', 'safe', 'on'=>'search'),
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
			'campaign' => array(self::BELONGS_TO, 'Campaigns', 'campaign_id'),
//			'platform' => array(self::BELONGS_TO, 'Platforms', 'platform_id'),
//            'news' => array(self::BELONGS_TO, 'News', 'news_id'),
//            'teaser' => array(self::BELONGS_TO, 'Teasers', 'teaser_id'),
//            'city' => array(self::BELONGS_TO, 'Cities', 'city_id'),
//            'country' => array(self::BELONGS_TO, 'Countries', 'country_code'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'campaign_id' => 'Campaign',
			'platform_id' => 'Platform',
			'is_deleted' => 'Is Deleted',
			'created_date' => 'Created Date',
			'revoked_date' => 'Revoked Date',
		);
	}

    /**
     * @return Tracks Именованная группа для выборки не удаленных трэков
     */
    public function notDeleted()
    {
        $alias = $this->getTableAlias(false,false);
        $this->getDbCriteria()->mergeWith(array(
            'condition' => $alias . '.is_deleted = 0',
        ));

        return $this;
    }

    public function getSequence(){
        $seq = $this->getDbConnection()->createCommand()
            ->select('MAX(id)')
            ->from($this->tableName())
            ->queryScalar();
        return (int)$seq;
    }
    
    public static function getById($trackId)
    {
        $track = RedisTrack::instance()->getTrack($trackId);
        if (!$track) {
            $track = self::model()->notDeleted()->findByPk($trackId);
            if($track === null){
                throw new CException('Cant find track by id: '.$trackId);
            }
            RedisTrack::instance()->addTrack($track);
        }

        return $track;
    }

    /**
     * Возвращает sql для добавления track с указаными параметрами
     *
     * @param $args
     * @return string
     */
    public static function createSql($args)
    {
        if(isset($args['created_date']) && ((string) (int) $args['created_date'] === $args['created_date'])){
            $args['created_date'] = date('Y-m-d H:i:s', $args['created_date']);
        }

        foreach($args as $k => $v){
            if(self::model()->hasAttribute($k) && $v !== null){
                $args[$k] = Yii::app()->db->quoteValue($v);
            }else{
                unset($args[$k]);
            }
        }

        $sql = "INSERT INTO `" . self::model()->tableName() . "` "
            ."(" . implode(', ', array_keys($args)) . ") "
            ."VALUES (" . implode(", ", array_values($args)) . ");";
        return $sql;
    }

    public static function revokeSql($args)
    {
        return "UPDATE `tracks` SET revoked_date = '".$args['revoked_date']."' WHERE id = ".(int)$args['id'].";";
    }
}