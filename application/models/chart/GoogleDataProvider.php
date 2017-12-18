<?php

/**
 * Возвращает данные для графиков гугл, выводимых на сайте
 */
class GoogleDataProvider extends ChartDataProvider
{
    /**
     * @var array Карта расположения данных array(news_id => position)
     */
    private $newsDataMap;

    /**
     * @var string Заголовок графика в формате json
     */
    private $chartHeader;

    /**
     * @var array Список цветов новостей
     */
    private $colors;

    /**
     * @return string Возвращает показы для графика
     */
    public function getShowsChartData()
    {
        $chartData = $this->getChartData(function(&$item) { return (int) $item['shows']; });
        return json_encode(array_merge($this->getChartHeader(), array_values($chartData)));
    }

    /**
     * @return string Возвращает клики для графика
     */
    public function getClicksChartData()
    {
        $chartData = $this->getChartData(function(&$item) { return $item['clicks'] + $item['fake_clicks']; });
        return json_encode(array_merge($this->getChartHeader(), array_values($chartData)));
    }

    /**
     * @return string Возвращает CTR для графика
     */
    public function getCtrChartData()
    {
        $chartData = $this->getChartData(function(&$item) {
            $ctr = !$item['shows']
                        ? 0
                        : ($item['clicks_without_externals'] + $item['fake_clicks']) / $item['shows'] * 100;

            return round($ctr, 2);
        });
        return json_encode(array_merge($this->getChartHeader(), array_values($chartData)));
    }

    /**
     * Возвращает цвета новостей
     * Если передать идентификатор новостей, то будет возвращен цвет одной новости
     *
     * @param int $news_id null
     *
     * @return string|array
     */
    public function getColors($news_id = null)
    {
        if (!isset($this->colors)) {
            foreach ($this->getNewsList() as $id => $title) {
                $this->colors[$id] = '#' . substr(md5($id), 0, 6);
            }
        }

        return $news_id ? $this->colors[$news_id] : json_encode(array_values($this->colors));
    }

    /**
     * @return array Возвращает заголовок графика
     */
    private function getChartHeader()
    {
        if (!isset($this->chartHeader)) {
            $this->chartHeader = array(array_merge(array('Дата'), array_values($this->getNewsList())));
        }

        return $this->chartHeader;
    }

    /**
     * Возвращает карту расположения данных новостей в массиве
     *
     * @return array
     */
    private function getNewsDataMap()
    {
        if (!isset($this->newsDataMap)) {

            $news_ids = array_keys($this->getNewsList());

            // Меняем местами идентификаторы и позиции новостей в массиве,
            // Добавляем смещение на 1 позицию, так как нулевой индекс для даты
            $this->newsDataMap = array_map(function($item) { return $item + 1; }, array_flip($news_ids));
        }

        return $this->newsDataMap;
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
        $newsMap    = $this->getNewsDataMap();

        $date   = strtotime($this->campaign->date_start);
        $dateTo = (strtotime($this->campaign->date_end) < strtotime('today'))
                        ? strtotime($this->campaign->date_end)
                        : strtotime('today');

        $chartData = array();
        while ($date <= $dateTo) {

            $humanDate = date('Y-m-d', $date);
            $chartData[$humanDate] = array(DateHelper::getGrathDate($date));

            // Данных за дату нет, тогда заполняем нулями
            if (!isset($reportData[$humanDate])) {
                $chartData[$humanDate] = array_merge($chartData[$humanDate], array_fill(1, count($newsMap), 0));
            } else {
                foreach ($newsMap as $news_id => $position) {
                    // Если данные для новости за дату есть, тогда вызываем $callback, иначе присваиваем 0
                    $chartData[$humanDate][$position] = isset($reportData[$humanDate][$news_id])
                                                            ? $callback($reportData[$humanDate][$news_id])
                                                            : 0;
                }
            }

            // Увеличиваем дату на один день
            $date += 3600 * 24;
        }

        return $chartData;
    }
}