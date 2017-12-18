<?php

/**
 * This is the model class for table "countries".
 *
 * The followings are the available columns in table 'country':
 * @property string $id
 * @property string $name
 * @property string $code
 *
 * The followings are the available model relations:
 * @property Cities[] $cities
 */
class Countries extends CActiveRecord
{
    /**
     * Используется, если не удалось определить город и страну пользователя
     */
    const DEFAULT_COUNTRY = 'ZZ';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Countries the static model class
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
		return 'countries';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('name, code', 'required'),
			array('name', 'length', 'max'=>250),
			array('code', 'length', 'max'=>2),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'cities' => array(self::HAS_MANY, 'Cities', 'country_id'),
		);
	}

    /**
     * Возвращает коды всех стран
     *
     * @param bool $with_default добавляет виртуальную страну по умолчанию
     * @return array
     */
    public function getAllCodes($with_default = true)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('code');
        $command->from($this->tableName());

        $result =  $command->queryColumn();
        if($with_default){
            array_push($result, self::DEFAULT_COUNTRY);
        }
        return $result;
    }

    /**
     * Возвращает идентификаторы всех стран
     * @return array
     */
    public function getAllIds()
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('id');
        $command->from($this->tableName());
        return $command->queryColumn();
    }

    /**
     * Возвращает коды всех стран кампании
     *
     * @param int $campaign_id
     *
     * @return array
     */
    public function getAllCodesCampaignId($campaign_id)
    {
        $countries = $this->getAllCodesCampaignIdUnchecked($campaign_id);
        /** Если ничего не выбрано возвращаем полный список */
        if(empty($countries)){
            $cities = Cities::model()->getAllByCampaignIdUnchecked($campaign_id);
            if(empty($cities)){
                $countries = $this->getAllCodes();
            }
        }
        return $countries;
    }

    public function getAllCodesCampaignIdUnchecked($campaign_id)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('c.code');
        $command->from($this->tableName() . ' c');
        $command->join('campaigns_countries cc', 'c.id = cc.country_id');
        $command->where('cc.campaign_id = :c_id', array('c_id' => $campaign_id));
        return $command->queryColumn();
    }

    /**
     * Возвращает коды стран по переданным идентификаторам
     *
     * @param array $ids
     *
     * @return array
     */
    public function getAllCodesByIds(array $ids)
    {
        if (empty($ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select('c.code');
        $command->from($this->tableName() . ' c');
        $command->where('c.id IN ('. implode(', ', $ids) . ')');

        return $command->queryColumn();
    }
}