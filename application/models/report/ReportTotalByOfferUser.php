<?php

/**
 * Модель отчета по действиям и кликам по предложению-пользователю в целом
 *
 * The followings are the available columns in table 'offers_users':
 * @property string $id
 * @property integer $clicks
 * @property integer $offers_actions
 * @property integer $offers_declined_actions
 * @property integer $offers_moderation_actions
 */
class ReportTotalByOfferUser extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportTotalByOfferUser the static model class
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
		return 'offers_users';
	}

    public function addCounter($counter, $params, $amount, $throwException = true)
    {
        if(isset($params['offer_user_id'])) {
            $params['id'] = $params['offer_user_id'];
        }
        parent::addCounter($counter, $params, $amount, $throwException);
    }

    public function getRewardSum($userId)
    {
        return (float) $this->getDbConnection()->createCommand()
            ->select('SUM(r.offers_actions * o.reward)')
            ->from($this->tableName() . ' r')
            ->join(Offers::model()->tableName() . ' o', 'o.id = r.offer_id')
            ->where('user_id = :user_id', array(':user_id' => $userId))
            ->queryScalar();
    }
}