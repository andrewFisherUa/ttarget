<?php

/**
 * Модель отчета по показам и кликам по новости и площадке за день
 *
 * The followings are the available columns in table 'report_daily_by_news_and_platform':
 * @property string $news_id
 * @property string $platform_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 *
 * The followings are the available model relations:
 * @property News $news
 * @property Platforms $platform
 */
class ReportDailyByNewsAndPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByNewsAndPlatform the static model class
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
        return array('news_id', 'platform_id', 'date');
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
		return 'report_daily_by_news_and_platform';
	}

    /**
     * Возвращает отчет для всех новостей компании по платформе на заданный период дней
     *
     * @param array   $news_ids
     * @param integer $platform_id
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array  array('date' => array('news_id' => dbRow))
     */
    public function getAllByPeriod(array $news_ids, $platform_id, $date_from, $date_to)
    {
        if (empty($news_ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->from($this->tableName());
        $command->andWhere('news_id IN (' . implode(',', $news_ids) . ')');
        $command->andWhere('platform_id = :platform_id', array(':platform_id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->order('news_id, date');
        $dataReader = $command->query();

        $result = array();
        foreach ($dataReader as $dbRow) {
            if (!isset($result[$dbRow['date']])) {
                $result[$dbRow['date']] = array();
            }

            $result[$dbRow['date']][$dbRow['news_id']] = $dbRow;
        }

        return $result;
    }
}