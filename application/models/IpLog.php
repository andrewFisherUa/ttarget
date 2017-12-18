<?php

/**
 * Модель для фиксации скликиваний
 *
 * The followings are the available columns in table 'ip_log':
 * @property string $id
 * @property string $ip
 * @property string $unix_timestamp
 * @property integer $interval
 * @property string $date
 * @property integer $news_id
 * @property integer $platform_id
 */
class IpLog extends CActiveRecord
{
    const CACHE_KEY = 'ttarget:ip:%s';

    /**
     * Продолжительность блокировки ip-адреса
     */
    const LOCKOUT_DURATION = '1 hour';

    /**
     * Время через, которое данные по ip считаются устаревшими и могут быть удалены из БД
     */
    const OBSOLESCENCE = '3 hours';

    /**
     * Верхняя граница среднего интервала между кликами с одного ip адреса (секунды),
     * если наберется ACTIONS_LIMIT записей в БД, то ip будет заблочен на LOCKOUT_DURATION
     */
    const INTERVAL_UPPER_LIMIT = 30;

    /**
     * Количество совершенных действий с одного ip, после которого
     * будет проводиться проверка на скликивания
     */
    const ACTIONS_LIMIT = 10;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return IpLog the static model class
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

    /**
     * @return string Возвращает название таблицы отчета
     */
    protected static function getTableName()
    {
        return 'ip_log';
    }

    /**
     * Удаляет все записи старше self::OBSOLESCENCE секунд
     *
     * @return int
     */
    public function deleteOld()
    {
        return $this->deleteAll('unix_timestamp < :obsolescence', array(
            ':obsolescence' => strtotime('-' . self::OBSOLESCENCE)
        ));
    }

    /**
     * Добавляет ip адрес для последующего выявления скликиваний
     *
     * @param string  $ip
     * @param integer $timestamp
     * @param integer $news_id
     * @param integer $platform_id
     */
    public static function add($ip, $timestamp, $news_id, $platform_id)
    {
        $long_ip = sprintf('%u', ip2long($ip));
        if (!$long_ip) {
            return;
        }

        $sql  = "INSERT INTO `" . self::tableName() . "` (`ip`, `unix_timestamp`, `interval`, `date`, `news_id`, `platform_id`) ";
        $sql .= "VALUES (:ip, :unix_timestamp, :interval, :date, :news_id, :platform_id);";

        $interval = self::calcInterval($ip, $long_ip, $timestamp);

        $command = self::$db->createCommand($sql);
        $affected = $command->execute(array(
            ':ip'               => $long_ip,
            ':unix_timestamp'   => $timestamp,
            ':interval'         => $interval,
            ':date'             => date('Y-m-d', $timestamp),
            ':news_id'          => $news_id,
            ':platform_id'      => $platform_id,
        ));

        self::updateCache($ip, array(
            'count'             => 1,
            'avg_interval'      => $interval,
            'last_timestamp'    => $timestamp,
            'is_pilferer'       => 0,
        ));

        if ($affected && self::isPilferer($ip)) {
            self::moveLogToReport($long_ip);
        }
    }

    /**
     * Обновляет данные по ip-адресу в кэше
     *
     * @param $ip
     * @param array $new_data
     */
    private static function updateCache($ip, array $new_data)
    {
        $key = self::getCacheKey($ip);

        $result = self::redis()->hGetAll($key);
        if ($result) {

            if ($result['is_pilferer']) {
                return;
            }

            if (!empty($result['count']) && $result['count'] < self::ACTIONS_LIMIT) {

                // Вычисляет сумму интервалов кликов
                $interval_sum = $result['avg_interval'] * $result['count'] + $new_data['avg_interval'];

                $new_data['count'] += $result['count'];
                $new_data['avg_interval'] = $interval_sum / $new_data['count'];

                // Проверяем не является ли ip скликивальщиком
                if ($new_data['count'] == self::ACTIONS_LIMIT &&
                    $new_data['avg_interval'] <= self::INTERVAL_UPPER_LIMIT) {

                    $new_data['is_pilferer'] = 1;
                }
            }
        }

        $expire = strtotime('+ ' . self::LOCKOUT_DURATION) - time();

        self::redis()->multi(Redis::PIPELINE);
            self::redis()->hMset($key, $new_data);
            self::redis()->expire($key, $expire);
        self::redis()->exec();
    }

    /**
     * Переносит данные из лога ip в отчет по скликиванию
     * @param $ip
     */
    private static function moveLogToReport($ip)
    {
        foreach (self::getClickfrauds($ip) as $dbRow) {
            ReportDailyClickfraud::model()->incrClicks(
                $dbRow['ip'],
                $dbRow['news_id'],
                $dbRow['platform_id'],
                $dbRow['clicks'],
                $dbRow['date']
            );
        }
    }

    /**
     * Возвращает скликивания для ip за последние 2 часа
     *
     * @param string   $ip
     *
     * @return array
     */
    private static function getClickfrauds($ip)
    {
        $command = self::$db->createCommand();
        $command->select('ip, date, news_id, platform_id, COUNT(*) as clicks');
        $command->from(self::getTableName());
        $command->andWhere('ip = :ip' , array(':ip' => $ip));
        $command->andWhere('unix_timestamp >= :time_limit', array(
            ':time_limit' => strtotime('-' . self::LOCKOUT_DURATION)
        ));
        $command->group('ip, date, news_id, platform_id');

        return $command->queryAll();
    }

    /**
     * Вычисляет интервал времени между последним зафиксированным действием
     * и текущим
     *
     * @param string $ip
     * @param int $long_ip
     * @param int $timestamp
     *
     * @return int
     */
    private static function calcInterval($ip, $long_ip, $timestamp)
    {
        $lastTimestamp = (int) self::redis()->hGet(self::getCacheKey($ip), 'last_timestamp');
        if (!$lastTimestamp) {

            $command = self::$db->createCommand();
            $command->select('unix_timestamp');
            $command->from(self::getTableName());
            $command->where('ip = :ip AND unix_timestamp >= :time', array(
                'ip'    => $long_ip,
                'time'  => strtotime('-' . self::LOCKOUT_DURATION)
            ));
            $command->order('unix_timestamp DESC');
            $command->limit(1);

            $lastTimestamp = (int) $command->queryScalar();
        }

        return $lastTimestamp ? $timestamp - $lastTimestamp : 0;
    }

    /**
     * Проверяет, не скликивают ли по данному ip-адресу
     *
     * @param string $ip
     *
     * @return boolean
     */
    private static function isPilferer($ip)
    {
        $isPilferer = self::redis()->hGet(self::getCacheKey($ip), 'is_pilferer');
        if ($isPilferer !== false) {
            return $isPilferer;
        }

        $command = self::$db->createCommand();
        $command->select("COUNT(`interval`) as count, AVG(`interval`) as avg_interval");
        $command->from(self::getTableName());
        $command->andWhere('ip = :ip' , array(':ip' => $ip));
        $command->andWhere('unix_timestamp >= :time_limit', array(
            ':time_limit' => strtotime('-' . self::LOCKOUT_DURATION)
        ));
        $command->order('unix_timestamp DESC');
        $command->limit(self::ACTIONS_LIMIT);

        if (!($dbRow = $command->queryRow())) {
            return false;
        }

        return $dbRow['count'] == self::ACTIONS_LIMIT &&
        $dbRow['avg_interval'] <= self::INTERVAL_UPPER_LIMIT;
    }

    /**
     * Возвращает ключ в кэше
     *
     * @param $ip
     * @return string
     */
    private static function getCacheKey($ip)
    {
        return sprintf(self::CACHE_KEY, $ip);
    }

    /**
     * @return Redis
     */
    private static function redis()
    {
        return Yii::app()->redis;
    }
}