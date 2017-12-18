<?php

/**
 * Возвращает данные для графиков excel
 */
class ExcelDataProvider extends ChartDataProvider
{
    /**
     * @var string Y-m-d
     */
    private $dateFrom;

    /**
     * @var string Y-m-d
     */
    private $dateTo;

    /**
     * @var array Список дат
     */
    private $dates = array();

    public function __construct(Campaigns $campaign, $dateFrom, $dateTo)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo   = $dateTo;

        parent::__construct($campaign);
    }

    /**
     * @return string Дата начала периода
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @return string Дата окончания периода
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @return string Возвращает показы для графика
     */
    public function getShowsChartData()
    {
        return $this->getChartData(function(&$item) { return $item['shows']; });
    }

    /**
     * @return string Возвращает клики для графика
     */
    public function getClicksChartData()
    {
        return $this->getChartData(function(&$item) { return $item['clicks'] + $item['fake_clicks']; });
    }

    /**
     * @return string Возвращает CTR для графика
     */
    public function getCtrChartData()
    {
        return $this->getChartData(function(&$item) {
            $ctr = !$item['shows']
                        ? 0
                        : ($item['clicks_without_externals'] + $item['fake_clicks']) / $item['shows'] * 100;

            return round($ctr, 2);
        });
    }

    /**
     * Возвращает список дат за период
     *
     * @return array
     */
    public function getDates()
    {
        if (empty($this->dates)) {

            $date = strtotime($this->dateFrom);
            if ($date < strtotime($this->campaign->date_start)) {
                $date = strtotime($this->campaign->date_start);
            }

            $dateTo = strtotime($this->dateTo);
            if ($dateTo > strtotime($this->campaign->date_end)) {
                $dateTo = strtotime($this->campaign->date_end);
            }

            while ($date <= $dateTo) {
                $this->dates[date('Y-m-d', $date)] = DateHelper::getGrathDate($date);
                // Увеличиваем дату на один день
                $date += 3600 * 24;
            }
        }

        return $this->dates;
    }

    /**
     * Возвращает данные для графиков
     *
     * В качестве параметра принимает функцию, которая должна вернуть значение для графика
     *
     * @param $callback
     *
     * @return array array(date => array(date, value[, value]))
     */
    private function getChartData($callback)
    {
        $reportData = $this->getReportData();
        $dates      = $this->getDates();

        $chartData = array();
        foreach ($this->getNewsList() as $news_id => $name) {

            if (!isset($chartData[$news_id])) {
                $chartData[$news_id] = array();
            }

            foreach ($dates as $humanDate => $displayingDate) {
                $chartData[$news_id][] = isset($reportData[$humanDate][$news_id])
                                            ? $callback($reportData[$humanDate][$news_id])
                                            : 0;
            }
        }

        return $chartData;
    }
}