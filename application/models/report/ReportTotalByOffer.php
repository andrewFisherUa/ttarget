<?php

/**
 * Модель отчета по действиям и кликам по предложению-пользователю в целом
 *
 * The followings are the available columns in table 'offers':
 * @property string $id
 * @property integer $clicks
 * @property integer $actions
 */
class ReportTotalByOffer extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportTotalByOffer the static model class
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
        return array('id');
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
		return 'offers';
	}

    public function addCounter($counter, $params, $amount, $throwException = true)
    {
        unset($params['id']);
        if(isset($params['offer_id'])) {
            $params['id'] = $params['offer_id'];
        }
        parent::addCounter($counter, $params, $amount, $throwException);
    }
}