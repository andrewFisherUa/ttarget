<?php

/**
 * Абстрактный класс возвращает данные для графиков
 */
abstract class ChartDataProvider
{
    /**
     * @var Campaigns $campaign
     */
    protected $campaign;

    /**
     * @var array Данные отчета по новостям компании
     */
    private $reportData;

    /**
     * @var array Список новостей
     *            array(news_id => news_name)
     */
    private $newsList;

    public function __construct(Campaigns $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * @return string Возвращает показы для графика
     */
    abstract public function getShowsChartData();

    /**
     * @return string Возвращает клики для графика
     */
    abstract public function getClicksChartData();

    /**
     * @return string Возвращает CTR для графика
     */
    abstract public function getCtrChartData();

    /**
     * Возвращает список новостей
     * @return array
     */
    public function getNewsList()
    {
        if (!isset($this->newsList)) {

            $this->newsList = array();
            foreach ($this->campaign->news as $news)
            {
                $this->newsList[$news->id] = $news->name;
            }
        }

        return $this->newsList;
    }

    /**
     * Проверяет имеются ли данные для построения графиков
     *
     * @return bool
     */
    public function hasReportData()
    {
        return (bool) $this->getReportData();
    }

    /**
     * @return Campaigns Возвращает объект кампании
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Возвращает данные отчета по новостям кампании за период
     * с начала кампании по сегодняшний день
     *
     * @return array
     */
    protected function getReportData()
    {
        if (!isset($this->reportData)) {

            $dateFrom   = $this->campaign->date_start;
            $dateTo     = (strtotime($this->campaign->date_end) < strtotime('today'))
                ? $this->campaign->date_end
                : date('Y-m-d');

            $this->reportData = ReportDailyByNews::model()
                ->getAllByPeriod($this->getCampaignNewsIds(), $dateFrom, $dateTo);
        }

        return $this->reportData;
    }

    /**
     * Возвращает идентификаторы новостей кампании
     *
     * @return array
     */
    protected function getCampaignNewsIds()
    {
        $newsIds = array();
        if ($this->campaign->news) {
            foreach ($this->campaign->news as $news) {
                array_push($newsIds, $news->id);
            }
        }

        return $newsIds;
    }
}