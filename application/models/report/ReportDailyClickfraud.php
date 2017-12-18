<?php

/**
 * Отчет по накруткам кликов
 *
 * The followings are the available columns in table 'report_daily_clickfraud':
 * @property string $id
 * @property string $ip
 * @property string $news_id
 * @property string $platform_id
 * @property string $date
 * @property string $clicks
 */
class ReportDailyClickfraud extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return ReportDailyClickfraud the static model class
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
        return 'report_daily_clickfraud';
    }

    /**
     * Метод увеличивающий клики за день по новости
     *
     * @param integer $ip
     * @param integer $news_id
     * @param integer $platform_id
     * @param integer $amount 1
     * @param string  $date 'YYYY-mm-dd'
     *
     * @return integer
     */
    public function incrClicks($ip, $news_id, $platform_id, $amount = 1, $date = null)
    {
        $sql  = "INSERT INTO `" . $this->tableName() . "` (ip, news_id, platform_id, date, clicks) ";
        $sql .= "VALUES (:ip, :news_id, :platform_id, :date, :amount) ON DUPLICATE KEY UPDATE clicks = clicks + :amount;";

        $command = $this->getDbConnection()->createCommand($sql);
        return $command->execute(array(
            ':ip'           => $ip,
            ':news_id'      => $news_id,
            ':platform_id'  => $platform_id,
            ':date'         => ($date) ?: date('Y-m-d'),
            ':amount'       => $amount
        ));
    }

    /**
     * Возвращает количество скликиваний на заданный период для
     * платформы и новостей
     *
     * @param array   $news_ids
     * @param integer $platform_id
     * @param string  $date_from  YYYY-mm-dd
     * @param string  $date_to YYYY-mm-dd
     *
     * @return array
     */
    public function countByPeriod(array $news_ids, $platform_id, $date_from, $date_to)
    {
        if (empty($news_ids)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select('SUM(clicks) as clicks, date, news_id, platform_id');
        $command->from($this->tableName());
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->andWhere('news_id IN (' . implode(',', $news_ids) . ')');
        $command->andWhere('platform_id = :platform_id', array(':platform_id' => $platform_id));
        $command->group('date, news_id, platform_id');

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
     * Возвращает количество скликиваний на заданный период для
     * платформы
     *
     * @param integer $platform_id
     * @param string  $date_from  YYYY-mm-dd
     * @param string  $date_to YYYY-mm-dd
     *
     * @return array
     */
    public function countByPeriodAndPlatform($platform_id, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('date, SUM(clicks) as clicks');
        $command->from($this->tableName());
        $command->andWhere('platform_id = :id', array(':id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('date');
        $command->order('date ASC');

        $dataReader = $command->query();

        $result = array();
        foreach ($dataReader as $dbRow) {
            $result[$dbRow['date']] = $dbRow['clicks'];
        }

        return $result;
    }

    /**
     * Возвращает количество скликиваний на заданный период для
     * платформ
     *
     * @param array   $platforms
     * @param string  $date_from  YYYY-mm-dd
     * @param string  $date_to YYYY-mm-dd
     *
     * @return array
     */
    public function countTotalByPlatforms($platforms, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();

        $command->from($this->tableName());
        $command->select('platform_id, SUM(clicks) as clicks');
        $command->andWhere('platform_id in ('.implode(',',$platforms).')');
        $command->group('platform_id');
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));

        $dataReader = $command->query();

        $result = array();
        foreach ($dataReader as $dbRow) {
            $result[$dbRow['platform_id']] = $dbRow['clicks'];
        }

        return $result;
    }

    /**
     * Возвращает количество скликиваний на заданный период для
     * всех кампаний и площадок сети
     *
     * @param string  $date_from  YYYY-mm-dd
     * @param string  $date_to YYYY-mm-dd
     *
     * @return array
     */
    public function countForCampaignsAndPlatformsByPeriod($date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select('n.campaign_id, r.platform_id, SUM(r.clicks) as clicks');
        $command->from($this->tableName() . ' r');
        $command->join(News::model()->tableName() . ' n', 'r.news_id = n.id');
        $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('r.platform_id, n.campaign_id');

        $dataReader = $command->query();

        $result = array();
        foreach ($dataReader as $dbRow) {
            if (!isset($result[$dbRow['platform_id']])) {
                $result[$dbRow['platform_id']] = array();
            }

            $result[$dbRow['platform_id']][$dbRow['campaign_id']] = $dbRow['clicks'];
        }

        return $result;
    }

}