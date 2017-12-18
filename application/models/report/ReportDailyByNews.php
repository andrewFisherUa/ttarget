<?php

/**
 * Модель отчета по показам и кликам по новости за день
 *
 * The followings are the available columns in table 'report_daily_by_news':
 * @property string $news_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 * @property integer $fake_clicks
 * @property integer $clicks_without_externals
 */
class ReportDailyByNews extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByNews the static model class
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
        return array('news_id', 'date');
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
		return 'report_daily_by_news';
	}

    /**
     * Метод увеличивающий поддельные клики
     *
     * @param News $news
     * @param Platforms $platform
     * @param int $amount 1
     * @param string $date 'YYYY-mm-dd'
     *
     * @return int
     */
    public function incrFakeClicks(News $news, Platforms $platform, $amount = 1, $date = null)
    {
        if($amount > 0){
            $this->getDbConnection()->createCommand(
                "INSERT INTO `" . $this->tableName() . "` (news_id, date, fake_clicks) "
                ."VALUES (:news_id, :date, :amount) ON DUPLICATE KEY UPDATE fake_clicks = fake_clicks + :amount;"
            )->execute(array(
                    ':news_id' => $news->id,
                    ':date'    => $date ? $date : date('Y-m-d'),
                    ':amount'  => $amount,
            ));
        }else{
            $rep = $this->getDbConnection()
                ->createCommand('SELECT clicks, fake_clicks FROM `'.$this->tableName().'` WHERE `news_id` = :news_id AND `date` = :date')
                ->queryRow(true, array(
                    ':news_id' => $news->id,
                    ':date'    => $date ? $date : date('Y-m-d'),
                ));

            if(!isset($rep['clicks']) || $rep['clicks'] + $rep['fake_clicks'] + $amount < 0) return false;

            $this->getDbConnection()->createCommand(
                "UPDATE `".$this->tableName()."` SET fake_clicks = fake_clicks + :amount "
                ."WHERE `news_id` = :news_id AND `date` = :date"
            )->execute(array(
                    ':news_id'  => $news->id,
                    ':date'         => $date ? $date : date('Y-m-d'),
                    ':amount'       => $amount,
                ));

        }
        return true;
    }

    /**
     * @return string Возвращает количество кликов за день, с учетом поддельных
     */
    public function totalClicks()
    {
        return $this->clicks + $this->fake_clicks;
    }

    /**
     * Возвращает отчет для всех новостей компании на заданный период дней
     *
     * @param array   $news_ids
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array  array('date' => array('news_id' => dbRow))
     */
    public function getAllByPeriod(array $news_ids, $date_from, $date_to)
    {
        if (empty($news_ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->from($this->tableName());
        $command->andWhere('news_id IN (' . implode(',', $news_ids) . ')');
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

    /**
     * Возвращает суммарный отчет для всех новостей компании на заданный период дней
     *
     * @param array   $news_ids
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array  array('news_id' => dbRow)
     */
    public function getAllTotalByPeriod(array $news_ids, $date_from, $date_to)
    {
        if (empty($news_ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'news_id',
            'SUM(shows) AS shows',
            'SUM(clicks) AS clicks',
            'SUM(clicks_without_externals) AS clicks_without_externals',
            'SUM(fake_clicks) as fake_clicks'
        ));
        $command->from($this->tableName());
        $command->andWhere('news_id IN (' . implode(',', $news_ids) . ')');
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('news_id');
        $dataReader = $command->query();

        $result = array();
        foreach ($dataReader as $dbRow) {
            $result[$dbRow['news_id']] = $dbRow;
        }

        return $result;
    }
}