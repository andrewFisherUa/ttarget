<?php

/**
 * This is the model class for table "platforms_cpc".
 *
 * The followings are the available columns in table 'click_cost':
 * @property string $id
 * @property string $date
 * @property string $platform_id
 * @property string $cost
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 */
class PlatformsCpc extends CActiveRecord
{
    const CURRENCY_RUB = 'RUB';
    const CURRENCY_USD = 'USD';

    private static $currencies = array(
        self::CURRENCY_RUB => 'руб',
        self::CURRENCY_USD => '$',
    );

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return PlatformsCpc the static model class
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
        return 'platforms_cpc';
    }

    /**
     * @return array Возвращает метку валюты
     */
    public static function getCurrency($currency)
    {
        return self::$currencies[$currency];
    }

    /**
     * @return array Возвращает доступные валюты с метками
     */
    public static function getCurrencies()
    {
        return self::$currencies;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('date, cost', 'required'),
            array('date', 'filter', 'filter' => function($value) { return date('Y-m-d', strtotime($value)); }),
            array('date', 'compare', 'compareValue' => '1970-01-01', 'operator' => '!='),
            array('cost', 'numerical'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'platform' => array(self::BELONGS_TO, 'Platforms', 'platform_id'),
        );
    }

    public function behaviors()
    {
        return array(
            'relations' => array(
                'class' => 'ext.yiiext.behaviors.EActiveRecordRelationBehavior'
            ),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'date' => 'дата установки стоимости',
            'cost' => 'стоимость пер.',
        );
    }

    /**
     * Возвращает последнюю установленную дату
     *
     * @param integer $platform_id
     * @param string  $date null 'Y-m-d'
     * @return PlatformsCpc
     */
    public function findLast($platform_id, $date = null)
    {
        $model = $this->findBySql(
            'SELECT * FROM `'.$this->tableName().'`'
            .' WHERE `platform_id` = :platform_id'
            .($date ? " AND date <= '" . date('Y-m-d', strtotime($date))."'" : '')
            .' ORDER BY date DESC LIMIT 1',
            array(':platform_id' => $platform_id)
        );

        return $model;
    }

    /**
     * Возвращает цену клика на заданную дату
     *
     * Возвращает массив, а не объект AR
     *
     * @param integer $platform_id
     * @param string  $date
     *
     * @return double
     */
    public function getCostPerClick($platform_id, $date)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('cost');
        $command->from($this->tableName());
        $command->andWhere('platform_id = :id', array(':id' => $platform_id));
        $command->andWhere('date <= :date', array(':date' => date('Y-m-d', strtotime($date))));
        $command->order('date DESC');
        $command->limit(1);

        return (double) $command->queryScalar();
    }

    /**
     * Возвращает стоимость кликов по площадке на заданный период
     *
     * Первой записью идет максимально близкая дата к нижней границе периода
     *
     * @param integer $platform_id
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array
     */
    public function getAllByPeriod($platform_id, $date_from, $date_to)
    {
        $result = array();

        $command = $this->getDbConnection()->createCommand();
        $command->from($this->tableName());
        $command->andWhere('platform_id = :id', array(':id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->order('date ASC');

        $dataReader = $command->query();

        foreach ($dataReader as $dbRow) {
            $result[$dbRow['date']] = $dbRow['cost'];
        }

        return $result;
    }

    /**
     * Возвращает подзапрос для выбора соответствующей даты для связи со стоимостью клика
     *
     * @return string
     */
    public static function getMaxCpcSql()
    {
        $maxCpcSql = <<<EOF
            SELECT cpc_s.date
                FROM platforms_cpc cpc_s
                WHERE cpc_s.platform_id = r.platform_id AND cpc_s.date <= r.date
                ORDER BY cpc_s.date DESC
                LIMIT 1
EOF;
        return $maxCpcSql;
    }
}