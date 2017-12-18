<?php

/**
 * Класс-обработчик счетчиков кликов и показов в отчетах
 */
class ReportHandler
{
    public static $reports = array(
        'ReportDailyByCampaign',
        'ReportDailyByCampaignAndPlatform',
        'ReportDailyByCampaignAndPlatformAndCity',
        'ReportDailyByCampaignAndPlatformAndCountry',
        'ReportDailyByNews',
        'ReportDailyByNewsAndPlatform',
        'ReportDailyByPlatform',
        'ReportDailyByTeaserAndPlatform',
        'ReportDailyByCampaignAndPlatformAndAction',
//        'ReportDaily',
        'ReportDailyByOfferUser',
        'ReportDailyByOffer',
        'ReportTotalByOfferUser',
        'ReportTotalByOffer'
    );

    /**
     * Возвращает список отчетов имеющих указанный каунтер
     *
     * @param $counter
     * @return array
     */
    public static function hasCounter($counter)
    {
        $reports = array();
        foreach(self::$reports as $report){
            if(call_user_func(array($report, 'model'))->hasAttribute($counter)){
                $reports[] = $report;
            }
        }
        return $reports;
    }

    /**
     * Накапливает изменения по счетчику $counter для отчетов.
     *
     * (В разных отчетах изменения с одними и теми-же параметрами
     * могут сгруппироваться в разное количество запросов к бд)
     *
     * @param $counter
     * @param $params
     * @param int $amount
     * @param null $reports
     */
    public static function addCounter($counter, $params, $amount = 1, $reports = null)
    {
        foreach ($reports === null ? self::$reports : $reports as $report) {
            call_user_func(array($report, 'model'))->addCounter($counter, $params, $amount, false);
        }
    }

    /**
     * Возвращает накопленные изменения по счетчику $counter в виде sql
     *
     * @param $counter
     * @param bool $asArray
     * @return array|string
     */
    public static function createUpdateCounterSql($counter, $asArray = false)
    {
        if($asArray){
            $result = array();
        }else{
            $result = '';
        }
        foreach (self::$reports as $report) {
            $queries = call_user_func(array($report, 'model'))->createUpdateCounterSql($counter, $asArray);
            if($asArray){
                foreach($queries as $query){
                    $result[] = $query;
                }
            }else{
                $result .= $queries;
            }
        }
        return $result;
    }


}