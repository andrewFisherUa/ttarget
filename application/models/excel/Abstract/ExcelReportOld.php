<?php
/**
 * Created by PhpStorm.
 * User: 1
 * Date: 30.12.13
 * Time: 17:33
 */
abstract class ExcelReportOld
{
    /**
     * @var Campaigns
     */
    protected $campaign;

    /**
     * @var string Y-m-d
     */
    protected $dateFrom;

    /**
     * @var string Y-m-d
     */
    protected $dateTo;

    /**
     * @var PHPExcel
     */
    protected $objPHPExcel;

    /**
     * Формирует отчет
     * @return $this
     */
    abstract public function build();

    /**
     * @return string Возвращает название файла отчета
     */
    abstract public function getFileName();


    public function __construct(Campaigns $campaign, $dateFrom, $dateTo)
    {
        $this->campaign = $campaign;

        $this->setPeriod($dateFrom, $dateTo);

        if (strtotime($dateFrom) < strtotime($campaign->date_start))
        {
            $this->dateFrom = $campaign->date_start;
        }

        if (strtotime($dateTo) > strtotime($campaign->date_end))
        {
            $this->dateTo = $campaign->date_end;
        }

        $this->objPHPExcel = new PHPExcel();
    }

    /**
     * @param $reportName
     * @param Campaigns $campaign
     * @param integer $platform_id
     * @param $dateFrom
     * @param $dateTo
     * 
     * @return ExcelReport
     * @throws Exception
     */
    public static function create($reportName, Campaigns $campaign, $platform_id, $dateFrom, $dateTo)
    {
        switch ($reportName) {

            case 'ExcelReportByPeriodForClient':
                return new ExcelReportByPeriodForClient($campaign, $dateFrom, $dateTo);

            case 'ExcelReportByPeriod':
                return new ExcelReportByPeriod($campaign, $dateFrom, $dateTo);

            case 'ExcelReportFull':
                return new ExcelReportFull($campaign, $dateFrom, $dateTo);

            case 'ExcelReportForPartner':
                $platform = Platforms::model()->findByPk($platform_id);
                if (!$platform) {
                    throw new Exception('Report not found');
                }
                return new ExcelReportForPartner($campaign, $platform, $dateFrom, $dateTo);

            default:
                throw new Exception('Report not found');
        }
    }

    /**
     * Выводит отчет
     */
    public function render()
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->getFileName() . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * Сохраняет отчет
     */
    public function save($file)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save($file);
    }

    /**
     * Добавляет лого на страницу
     *
     * @param PHPExcel_Worksheet $activeSheet
     */
    protected function addLogo(PHPExcel_Worksheet $activeSheet)
    {
        $logo_path = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . 'htdocs/images' . DIRECTORY_SEPARATOR . 'logo.jpg';
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setWorksheet($activeSheet);
        $objDrawing->setName("Лого");
        $objDrawing->setPath($logo_path);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setOffsetX(1);
        $objDrawing->setOffsetY(5);
        $activeSheet->getRowDimension(3)->setRowHeight(40);
    }

    /**
     * Добавляет метаданые к файлу отчета
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setMetadata(PHPExcel $objPHPExcel)
    {
        $objPHPExcel->getProperties()
            ->setCreator("Отчёт по Рекламной кампании")
            ->setLastModifiedBy("Отчёт по Рекламной кампании")
            ->setTitle("Отчёт по Рекламной кампании")
            ->setSubject("Отчёт по Рекламной кампании")
            ->setDescription("Отчёт по Рекламной кампании")
            ->setKeywords("Отчёт по Рекламной кампании")
            ->setCategory("Отчёт по Рекламной кампании");
    }

    /**
     * Устанавливает шрифт отчета
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setFont(PHPExcel $objPHPExcel)
    {
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(8);
    }

    /**
     * Устанавливает базовый заголовок
     *
     * @param PHPExcel_Worksheet $activeSheet
     */
    protected function setBaseHeader(PHPExcel_Worksheet $activeSheet)
    {
        $activeSheet
            ->setCellValue('A4', 'Клиент:')
            ->setCellValue('C4', $this->campaign->client->login)

            ->setCellValue('A5', 'Общий период проведения кампании:')
            ->setCellValue('C5', date('d.m.Y', strtotime($this->campaign->date_start)) . '-' . date('d.m.Y', strtotime($this->campaign->date_end)))

            ->setCellValue('A6', 'Период промежуточного отчета по кампании:')
            ->setCellValue('C6', date('d.m.Y', strtotime($this->dateFrom)) . '-' . date('d.m.Y', strtotime($this->dateTo)));
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

    /**
     * Возвращает значение колонки для гео-таргетинга новости
     *
     * @param Campaigns $campaign
     * @return string
     */
    protected function getCampaignGeoTargeting(Campaigns $campaign)
    {
        $geoTargeting = $campaign->getExceptedCities();
        return ($geoTargeting) ? ('Исключить '. $geoTargeting) : 'Показывать всем';
    }

    /**
     * Устанавливает период отчета
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    protected function setPeriod($dateFrom, $dateTo)
    {
        $timeTo = strtotime($dateTo);
        $timeFrom = strtotime($dateFrom);

        if($timeTo === false){
            $timeTo = time();
        }
        if($timeFrom === false || $timeFrom > $timeTo){
            $timeFrom=$timeTo;
        }

        $this->dateFrom = date('Y-m-d', $timeFrom);
        $this->dateTo   = date('Y-m-d', $timeTo);
    }
}