<?php

/**
 * Модель фиксирующая отказы
 *
 * The followings are the available columns in table 'bounce_log':
 * @property string $campaign_id
 * @property string $unix_timestamp
 * @property integer $total
 * @property integer $verified
 */
class BounceLog extends CActiveRecord
{
    /**
     * Количество данных для расчета коэфицента.
     * Время для расчета: от INTERVAL секунд при наличии данных и до бесконечности при их недостатке.
     */
    const RATE_MINIMAL_INTERVAL = 60;

    /**
     * Время через которое данные считаются устаревшими и могут быть удалены из БД
     */
    const OBSOLESCENCE = '3 hours';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return BounceLog the static model class
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
		return self::getTableName();
	}

    protected static function getTableName()
    {
        return 'bounce_log';
    }

    /**
     * Добавляет данные о переходе / подтверждении
     *
     * @param $campaignId
     * @param $timestamp
     * @param bool $verified
     */
	public static function add($campaignId, $timestamp, $verified = false)
    {
        $sql = 'INSERT INTO `'.self::getTableName().'` (`campaign_id`, `unix_timestamp`, `total`, `verified`)'
            .' VALUES (:campaign_id, :unix_timestamp, :total, :verified)'
            .' ON DUPLICATE KEY UPDATE `total` = `total` + :total, `verified` = `verified` + :verified';
        self::$db->createCommand($sql)->execute(array(
            ':campaign_id' => $campaignId,
            ':unix_timestamp' => $timestamp,
            ':total' => $verified ? 0 : 1,
            ':verified' => $verified ? 1 : 0,
        ));
    }

    /**
     * Расчитывает показатель отказов
     *
     * @param $campaignId
     * @param int $bounceCheckInterval
     * @return float
     */
    public static function getRate($campaignId, $bounceCheckInterval = 0)
    {
        $sql = 'SELECT'
            .'(SELECT SUM(`total`) FROM `'.self::getTableName().'`'
                .' WHERE `campaign_id` = :campaign_id'
                .' AND `unix_timestamp` <= :total_to AND `unix_timestamp` > :total_from'
            .') AS `total`,'
            .'(SELECT SUM(`verified`) FROM `'.self::getTableName().'`'
                .' WHERE `campaign_id` = :campaign_id'
                .' AND `unix_timestamp` <= :verified_to AND `unix_timestamp` > :verified_from'
            .') AS `verified`';

        $current = time();
        $row = self::$db->createCommand($sql)->queryRow(true, array(
            ':campaign_id' => $campaignId,
            ':verified_from' => $current - self::RATE_MINIMAL_INTERVAL,
            ':verified_to' => $current,
            ':total_from' => $current - self::RATE_MINIMAL_INTERVAL - $bounceCheckInterval,
            ':total_to' => $current - $bounceCheckInterval,
        ));

        return $row['total'] > 0 ? 100 - (100 / $row['total'] * $row['verified']) : 0;
    }

    /**
     * Удаляет устаревшие данные
     */
    public static function flush()
    {
        self::$db->createCommand()->delete(
            self::getTableName(),
            'unix_timestamp < :obsolescence',
            array(':obsolescence' => strtotime('-' . self::OBSOLESCENCE))
        );
    }
}