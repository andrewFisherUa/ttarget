<?php

/**
 * Модель отчета по всем данным
 *
 * The followings are the available columns in table 'report_daily':
 * @property string $date
 * @property integer $campaign_id
 * @property integer $news_id
 * @property integer $teaser_id
 * @property integer $platform_id
 * @property integer $city_id
 * @property string $country_code
 * @property integer $action_id
 * @property integer $shows
 * @property integer $clicks
 * @property integer $actions
 */
class ReportDaily extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDaily the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array Первичный ключ таблицы
     */
    public function primaryKey()
    {
        return array(
            'date',
            'campaign_id',
            'news_id',
            'teaser_id',
            'platform_id',
            'city_id',
            'country_code',
            'action_id',
            'offer_id',
            'offer_user_id'
        );
    }

    /**
     * @return string Возвращает название таблицы
     */
    public function tableName()
    {
        return self::getTableName();
    }

    /**
     * @return string Возвращает название таблицы отчета
     */
    protected static function getTableName()
	{
		return 'report_daily';
	}

    /**
     * Нам нужны все данные, поэтому добиваем недостоющие нулями.
     *
     * {@inheritdoc}
     */
    public function addCounter($counter, $params, $amount, $throwException = true)
    {
        foreach($this->primaryKey() as $attr){
            if($attr == 'date'){ continue; }
            if(!array_key_exists($attr, $params)){
                $params[$attr] = 0;
            }
        }
        return parent::addCounter( $counter, $params, $amount, $throwException );
    }
}