<?php

/**
 * Формирует отчет по компании за весь период
 */
class ExcelReportForPartner extends ExcelReportOld
{
    /**
     * @var Platforms
     */
    private $platform;

    /**
     * @var News[] Список новостей
     */
    private $newsList;

    /**
     * @var array Количество скликиваний
     */
    private $countOfClickFraud;

    public function __construct(Campaigns $campaign, Platforms $platform, $dateFrom, $dateTo)
    {
        $this->platform = $platform;
        parent::__construct($campaign, $dateFrom, $dateTo);
    }

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        $this->objPHPExcel = new PHPExcel();

        $newsReportData         = $this->getNewsReportData();
        $campaignReportData     = $this->getCampaignReportData();

        $this->setMetadata($this->objPHPExcel);
        $this->setFont($this->objPHPExcel);
        $activeSheet = $this->objPHPExcel->getActiveSheet();
        $this->addLogo($activeSheet, 'F');
        $this->setHeader($this->objPHPExcel, $campaignReportData, 'F');

        $this->setNewsTableHeader($this->objPHPExcel);
        $this->addNewsTable($this->objPHPExcel, $newsReportData, $campaignReportData);

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }

    /**
     * @return string Возвращает название файла отчета
     */
    public function getFileName()
    {
        return 'Отчет для партнеров';
    }

    /**
     * Устанавливает базовый заголовок
     *
     * @param PHPExcel_Worksheet $activeSheet
     */
    protected function setBaseHeader(PHPExcel_Worksheet $activeSheet)
    {
        $activeSheet
            ->setCellValue('A4', 'Партнерская площадка:')
            ->setCellValue('F4', $this->platform->server)

            ->setCellValue('A4', 'Клиент:')
            ->setCellValue('F4', $this->campaign->client->login)

            ->setCellValue('A5', 'Общий период проведения кампании:')
            ->setCellValue('F5', date('d.m.Y', strtotime($this->campaign->date_start)) . '-' . date('d.m.Y', strtotime($this->campaign->date_end)))

            ->setCellValue('A6', 'Период промежуточного отчета по кампании:')
            ->setCellValue('F6', date('d.m.Y', strtotime($this->dateFrom)) . '-' . date('d.m.Y', strtotime($this->dateTo)));
    }

    /**
     * Устанавливает заголовок первой страницы
     *
     * @param PHPExcel $objPHPExcel
     * @param array $campaignReportData
     * @param string $pos
     */
    private function setHeader(PHPExcel $objPHPExcel, array $campaignReportData, $pos = 'D')
    {
        $objPHPExcel->getActiveSheet()->setTitle('Отчёт');
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);

        $this->setBaseHeader($activeSheet);

        $activeSheet
            ->setCellValue('A7', 'Количество переходов по кампании на момент промежуточного отчета(по периоду):')
            ->setCellValue($pos . '7', @$campaignReportData['clicks']);

        $objPHPExcel->getActiveSheet()->getStyle($pos . '7')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ff0000')
                )
            )
        );
    }

    /**
     * Устанавливает заголовок таблицы новостей
     *
     * @param PHPExcel $objPHPExcel
     */
    private function setNewsTableHeader(PHPExcel $objPHPExcel)
    {
        $objPHPExcel
            ->setActiveSheetIndex(0)
            ->setCellValue('A9', 'дата')
            ->setCellValue('A9', 'дата')
            ->setCellValue('B9', 'Новость')
            ->setCellValue('C9', 'Ссылка на новость')
            ->setCellValue('D9', 'Сегмент')
            ->setCellValue('E9', 'Гео')
            ->setCellValue('F9', 'Показы')
            ->setCellValue('G9', 'Общее количество переходов')
            ->setCellValue('H9', 'процент скликивания')
            ->setCellValue('I9', 'процент отказа общий');

        $objPHPExcel->getActiveSheet()->getStyle('A9:I9')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '528ed6')
                ),
                'font' => array(
                    'size' => 12,
                    'color' => array('rgb' => 'FFFFFF'),
                    'bold' => true
                ),
                'alignment' => array(
                    'wrap' => true,
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            )
        );
    }

    /**
     * Возвращает список новостей
     * @return News[]
     */
    private function getNewsList()
    {
        if (!isset($this->newsList)) {

            $this->newsList = array();
            foreach ($this->campaign->news as $news)
            {
                $this->newsList[$news->id] = $news;
            }
        }

        return $this->newsList;
    }

    /**
     * Добавляет таблицу со списком новостей
     *
     * @param PHPExcel $objPHPExcel
     * @param array $newsReportData
     * @param array $campaignReportData
     */
    private function addNewsTable(PHPExcel $objPHPExcel, array $newsReportData, array $campaignReportData)
    {
        $activeSheet = $objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Отчёт');
        $newsList = $this->getNewsList();
        $geoTargeting = $this->getCampaignGeoTargeting($this->campaign);

        $rowc = 10;
        foreach ($newsReportData as $news_data) {
            foreach ($news_data as $news_id => $data) {
                $activeSheet
                    ->setCellValue('A'.$rowc, $data['date'])
                    ->setCellValue('B'.$rowc, $newsList[$news_id]->name)
                    ->setCellValue('C'.$rowc, $newsList[$news_id]->url)
                /** @todo: у новостей больше нет тегов. что же делать, как же быть? */
                    ->setCellValue('D'.$rowc, '') /*$newsList[$news_id]->tag->name*/
                    ->setCellValue('E'.$rowc, $geoTargeting)
                    ->setCellValue('F'.$rowc, $data['shows'])
                    ->setCellValue('G'.$rowc, $data['clicks'])
                    ->setCellValue('H'.$rowc, $this->getPercentOfClickFraud($newsList[$news_id], $data))
                    ->setCellValue('I'.$rowc, $this->getPercentOfFailures($newsList[$news_id], $data['clicks']));

                ++$rowc;
            }
        }

        $activeSheet->getStyle('A'.$rowc.':I'.$rowc)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb' => 'c6dbf7'),
                    ),
                ),
            )
        );

        $activeSheet
            ->setCellValue('A'.$rowc, 'Итог:')
            ->setCellValue('F'.$rowc, @$campaignReportData['shows'])
            ->setCellValue('G'.$rowc, @$campaignReportData['clicks']);

        $activeSheet->getColumnDimension('A')->setWidth(10);
        $activeSheet->getColumnDimension('B')->setWidth(13);
        $activeSheet->getColumnDimension('C')->setWidth(26);
        $activeSheet->getColumnDimension('D')->setWidth(15);
        $activeSheet->getColumnDimension('E')->setWidth(15);
        $activeSheet->getColumnDimension('F')->setWidth(13);
        $activeSheet->getColumnDimension('G')->setWidth(25);
        $activeSheet->getColumnDimension('H')->setWidth(20);
        $activeSheet->getColumnDimension('I')->setWidth(20);
        $activeSheet->getRowDimension(3)->setRowHeight(40);
    }

    /**
     * Возвращает процент отклоненных кликов для новости
     *
     * @param News $news
     * @param $clicks
     * @return int
     */
    private function getPercentOfFailures(News $news, $clicks)
    {
        $clicks = (int) $clicks;
        if ($clicks > 0) {
            return sprintf('%.2f', ($news->failures * 100 / $clicks));
        }

        return ((int) $news->failures) > 0 ? 100 : 0;
    }

    /**
     * Возвращает процент скликиваний для новости
     *
     * @param News $news
     * @param array $data
     * @return int
     */
    private function getPercentOfClickFraud(News $news, array $data)
    {
        if ($data['clicks'] > 0) {
            $countOfClickFraud = $this->getCountOfClickFraud($news, $data['date']);
            return sprintf('%.2f', ($countOfClickFraud * 100 / $data['clicks']));
        }

        return 0;
    }

    /**
     * Возвращает количество скликиваний для новости и площадки за опредленный день
     *
     * @param News $news
     * @param string $date YYYY-mm-dd
     *
     * @return integer
     */
    private function getCountOfClickFraud(News $news, $date)
    {
        if (!isset($this->countOfClickFraud)) {

            $this->countOfClickFraud = ReportDailyClickfraud::model()->countByPeriod(
                $this->getCampaignNewsIds(),
                $this->platform->id,
                $this->dateFrom,
                $this->dateTo
            );
        }

        return isset($this->countOfClickFraud[$date][$news->id])
                    ? (int) $this->countOfClickFraud[$date][$news->id]['clicks']
                    : 0;
    }

    /**
     * @return array Возвращает отчет по новостям кампании за период
     */
    protected function getNewsReportData()
    {
        return ReportDailyByNewsAndPlatform::model()->getAllByPeriod(
            $this->getCampaignNewsIds(),
            $this->platform->id,
            $this->dateFrom,
            $this->dateTo
        );
    }

    /**
     * @return array Возвращает отчет по кампании за период
     */
    protected function getCampaignReportData()
    {
        return ReportDailyByCampaignAndPlatform::model()->getTotalByPeriod(
            $this->campaign->id,
            $this->platform->id,
            $this->dateFrom,
            $this->dateTo
        );
    }
}
