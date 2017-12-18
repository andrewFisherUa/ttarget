<?php

/**
 * Строит графики для excel
 */
class ExcelChart
{
    /**
     * @var ExcelDataProvider
     */
    private $dataProvider;

    public function __construct(ExcelDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Рисует график показов
     * @return string путь к файлу графика
     */
    public function drawShowsChart()
    {
        return $this->draw($this->dataProvider->getShowsChartData());
    }

    /**
     * Рисует график переходов
     * @return string путь к файлу графика
     */
    public function drawClicksChart()
    {
        return $this->draw($this->dataProvider->getClicksChartData());
    }

    /**
     * Рисует график CTR
     * @return string путь к файлу графика
     */
    public function drawCtrChart()
    {
        return $this->draw($this->dataProvider->getCtrChartData());
    }

    /**
     * Рисует график по переданным данным
     *
     * @param array $points
     * @return string путь к файлу графика
     */
    private function draw(array $points)
    {
        $errLevel = error_reporting();
        error_reporting(0);

        $newsList = $this->dataProvider->getNewsList();

        $classLoader    = new CPChart();
        $DataSet        = new pData;

        foreach ($points as $news_id => $set) {
            $DataSet->AddPoint($set, "news" . $news_id);
            $DataSet->SetSerieName($newsList[$news_id], "news" . $news_id);
        }

        $DataSet->AddAllSeries();
        $DataSet->AddPoint(array_values($this->dataProvider->getDates()), "dates");
        $DataSet->SetAbsciseLabelSerie('dates');

        // Initialise the graph
        $chart = new pChart(700, 250);
        $chart->setFontProperties($classLoader->Cpath("Fonts/tahoma.ttf"), 7);
        $chart->setGraphArea(60, 30, 570, 200);
        $chart->drawFilledRoundedRectangle(7, 7, 693, 253, 5, 240, 240, 240);
        $chart->drawRoundedRectangle(5, 5, 695, 255, 5, 230, 230, 230);
        $chart->drawGraphArea(255, 255, 255, true);

        $data = $DataSet->GetData();
        if(!empty($data)){
            $chart->drawScale(
                $data,
                $DataSet->GetDataDescription(),
                SCALE_NORMAL,
                150,
                150,
                150,
                true,
                45,
                2,
                true,
                3
            );

            $chart->drawGrid(4, true, 230, 230, 230, 50);
            $chart->drawCubicCurve($DataSet->GetData(), $DataSet->GetDataDescription());

            // Finish the graph
            $chart->setFontProperties($classLoader->Cpath("Fonts/tahoma.ttf"), 8);
            $chart->drawLegend(590, 20, $DataSet->GetDataDescription(), 255, 255, 255);
        }

        // Restore reporting level
        error_reporting($errLevel);

        return $this->render($chart, $points);
    }

    /**
     * Рендерит график и сохраняет его на диск
     *
     * @param pChart $chart
     * @param array $points
     *
     * @return string
     */
    private function render(pChart $chart, array $points)
    {
        $fileName = md5($this->dataProvider->getCampaign()->id .
                $this->dataProvider->getDateFrom() .
                $this->dataProvider->getDateTo() .
                serialize($points)) .
            '.png';

        // Render the chart
        $path = Yii::app()->params['tmpPath'] . DIRECTORY_SEPARATOR . $fileName;
        $chart->render($path);

        return $path;
    }
}