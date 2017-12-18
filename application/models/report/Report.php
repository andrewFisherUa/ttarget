<?php

/**
 * Абстрактный класс для всех моделей отчетов
 */
abstract class Report extends CActiveRecord
{
    private $accumulated = array();

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Report the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
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
        return 1;
    }

    public function getRtbMaxCpcSql()
    {
        $maxCpcSql = <<<EOF
            SELECT cpc_s.date
                FROM platforms_rtb_cpc cpc_s
                WHERE cpc_s.platform_id = r.platform_id AND cpc_s.date <= r.date
                ORDER BY cpc_s.date DESC
                LIMIT 1
EOF;
        return $maxCpcSql;
    }


    public function addCounter($counter, $params, $amount, $throwException = true)
    {
        if($this->hasAttribute($counter)){
            $params['date'] = isset($params['date']) ? $params['date'] : date('Y-m-d');
            $keys = array();

            foreach($this->primaryKey() as $key){
                if(!isset($params[$key])){
                    if($throwException) {
                        throw new CException(sprintf('Parameters key %s required by report %s missing', $key, get_class($this)));
                    }else{
                        return;
                    }
                }
                $keys[] = $params[$key];
            }

            $key = "'".join("', '", $keys)."'";

            $t = &$this->accumulated[$counter];
            if(!isset($t[$key])){
                $t[$key] = 0;
            }

            $t[$key] += $amount;
        }
    }

    public function createUpdateCounterSql($counter, $asArray = false)
    {
        if($asArray){
            $result = array();
        }else{
            $result = '';
        }
        if(isset($this->accumulated[$counter])){
            foreach($this->accumulated[$counter] as $key => $amount){
                $sql = "INSERT INTO `" . $this->getTableName() . "` (" . join(', ', $this->primaryKey()) .", {$counter}) ";
                $sql .= "VALUES ({$key}, {$amount}) ";
                $sql .= "ON DUPLICATE KEY UPDATE {$counter} = {$counter} + {$amount};";
                if($asArray){
                    $result[] = $sql;
                }else{
                    $result .= $sql;
                }
            }
            unset($this->accumulated[$counter]);
        }
        return $result;
    }

    /**
     * @return array Возвращает период, за который нужно предоставить данные
     */
    static public function getPeriod( $period = null )
    {
        //$_period = !is_null($period) ? $period : (isset($_GET['period']) ? $_GET['period'] : 'today');
        
        if (isset($_GET['period'])) {
            $period = $_GET['period'];
        } elseif(empty($period)){
            $period = 'today';
        }

        switch ($period) {
            case 'yesterday':
                $dateFrom = date('Y-m-d', strtotime('-1 day'));
                $dateTo = $dateFrom;
                break;

            case 'month':
                $dateFrom = date('Y-m-d', strtotime('-1 month'));
                $dateTo = date('Y-m-d');
                break;

            case 'custom':
                $dateFrom = strtotime($_GET['date_from']);
                $dateTo = strtotime($_GET['date_to']);
                if($dateFrom > $dateTo){
                    $dateFrom = $dateTo;
                }
                $dateFrom = date('Y-m-d', $dateFrom ? $dateFrom : time());
                $dateTo = date('Y-m-d', $dateTo ? $dateTo : time());
                break;

            case 'today':
            case 'all':
                $dateFrom = date('Y-m-d');
                $dateTo = $dateFrom;
                break;
        }

        return array($period, $dateFrom, $dateTo);
    }
}