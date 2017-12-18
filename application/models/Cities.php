<?php

/**
 * This is the model class for table "cities".
 *
 * The followings are the available columns in table 'cities':
 * @property string $id
 * @property string $name
 * @property integer $country_id
 *
 * The followings are the available model relations:
 * @property Countries $country
 */
class Cities extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Cities the static model class
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
		return 'cities';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name', 'required'),
			array('name', 'length', 'max'=>250),
			array('id, name', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'country' => array(self::BELONGS_TO, 'Coutries', 'country_id', 'joinType' => 'INNER JOIN'),
		);
	}

    /**
     * Возвращает список городов кампании
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function getAllByCampaignId($campaign_id)
    {
        $cities = $this->getAllByCampaignIdUnchecked($campaign_id);
        /** Если ничего не выбрано возвращаем полный список */
        if(empty($cities)){
            $countries = Countries::model()->getAllCodesCampaignIdUnchecked($campaign_id);
            if(empty($countries)){
                $cities = $this->getAllIds();
            }
        }
        return $cities;
    }

    public function getAllByCampaignIdUnchecked($campaign_id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('c.id');
        $command->from($this->tableName() . ' c');
        $command->join('campaigns_cities cc', 'c.id = cc.city_id');
        $command->where('cc.campaign_id = :c_id', array('c_id' => $campaign_id));
        return $command->queryColumn();
    }

    public function getAllIds()
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('c.id');
        $command->from($this->tableName() . ' c');
        return $command->queryColumn();
    }

    /**
     * Возвращает идентификатор города по его имени
     *
     * @param string $name
     *
     * @return array
     */
    public function getIdByName($name, $insert_country_id = false)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('id');
        $command->from($this->tableName());
        $command->where('LOWER(name) = LOWER(:name)', array(':name' => $name));
        $id = $command->queryScalar();
        if($id === false && $insert_country_id !== false){
            $command = $this->getDbConnection()->createCommand();
            $command->insert($this->tableName(), array(
                'name' => $name,
                'country_id' => $insert_country_id,
            ));
            $id = $this->getDbConnection()->getLastInsertID();
        }
        return $id;
    }
}