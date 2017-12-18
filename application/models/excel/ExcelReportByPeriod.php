<?php

/**
 * Формирует отчет по компании на заданный период
 */
class ExcelReportByPeriod extends ExcelReportCampaign
{
    protected $campaignReportData;

    public function __construct(Campaigns $campaign,$dateFrom, $dateTo)
    {
        parent::__construct($campaign,$dateFrom, $dateTo);
        $this->title = 'Промежуточный отчет по рекламной кампании';
    }

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        $newsReportData           = $this->getNewsReportData();
        $campaignReportDataByDate = $this->getCampaignReportDataByDate();
        $this->campaignReportData = $this->getCampaignReportData($campaignReportDataByDate);

        $this->setMetadata($this->objPHPExcel);
        $this->setFont($this->objPHPExcel);

        $this->setNewsTableHeader($this->objPHPExcel);
        $this->addNewsTable($this->objPHPExcel, $newsReportData);

        $this->addStats($this->objPHPExcel, $campaignReportDataByDate);
        if($this->campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $this->addActionsStats($this->objPHPExcel, $this->getActionsReportData());
        }
        $this->addIndependentAnalyst($this->objPHPExcel);

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }

    protected function setHeader(PHPExcel_Worksheet $activeSheet, $headers, $rightColumn = null)
    {
        $rightColumn = parent::setHeader($activeSheet, $headers, $rightColumn);
        $activeSheet->getStyle($rightColumn.'11:'.$rightColumn.'12')->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '00b050')
            ),
            'numberformat' => array(
                'code' => self::FORMAT_NUMBER_SEPARATED
            )
        ));
    }

    protected function getHeaders()
    {
        $headers = parent::getHeaders();
        $headers['Количество переходов (по периоду):'] = $this->campaignReportData['clicks'] + $this->campaignReportData['fake_clicks'];
        if($this->campaign->cost_type == Campaigns::COST_TYPE_ACTION) {
            $headers['Количество действий (по периоду):'] = $this->campaignReportData['actions'];
        }
        return $headers;
    }

    /**
     * Устанавливает заголовок таблицы новостей
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setNewsTableHeader(PHPExcel $objPHPExcel)
    {
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0) ;
        $activeSheet->setTitle('Общий');
        $activeSheet
            ->setCellValue('A14', 'Дата')
            ->setCellValue('B14', 'Новости и ссылки')
            ->setCellValue('C14', 'Кол-во переходов');
    }

    /**
     * Добавляет таблицу со списком новостей
     *
     * @param PHPExcel $objPHPExcel
     * @param array $newsReportData
     */
    protected function addNewsTable(PHPExcel $objPHPExcel, array $newsReportData)
    {
        $activeSheet = $objPHPExcel->getActiveSheet();

        $row = 15;
        $linkColor = new PHPExcel_Style_Color( 'FF538ed5' );
        foreach ($this->campaign->news as $news) {

            $objRichText = new PHPExcel_RichText();
            $objRichText->createTextRun($news->name."\n")->getFont()->setSize(10);
            $objRichText->createTextRun($news->url)->getFont()->setColor($linkColor)->setSize(10);

            $activeSheet
                ->setCellValue('A'.$row, Yii::app()->dateFormatter->formatDateTime($news->create_date, 'short', null))
                ->setCellValue('B'.$row, $objRichText)
                ->setCellValue('C'.$row, isset($newsReportData[$news->id]) ? $newsReportData[$news->id]['clicks'] + $newsReportData[$news->id]['fake_clicks'] : 0);

//            $activeSheet->getCell('B'.$row)->getHyperlink()->setUrl($news->url);

            $row++;
        }

        $activeSheet
            ->setCellValue('A'.$row, 'Итого:')
            ->setCellValue('B'.$row, count($this->campaign->news).' новостей')
            ->setCellValue('C'.$row, $this->campaignReportData ? $this->campaignReportData['clicks'] + $this->campaignReportData['fake_clicks'] : 0);

        $this->formatTable($activeSheet,'A','14','C', $row, array(
            'innerRowHeight' => 27.75 * 1.05,
        ));

        $activeSheet->getStyle('B15:B'.($row-1))->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrap' => true
            ),
        ));

        $activeSheet->getStyle('C15:C'.$row)->getNumberFormat()->setFormatCode(self::FORMAT_NUMBER_SEPARATED);

        $activeSheet->getColumnDimension('A')->setWidth(11.29 * 1.05);
        $activeSheet->getColumnDimension('B')->setWidth(56.14 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(17.43 * 1.05);

        $this->setPageFit($activeSheet, self::FIT_TO_WIDTH);

        $this->addLogo($activeSheet);
        $this->setHeader($activeSheet, $this->getHeaders());
    }

    /**
     * Добавляет страницу качества
     *
     * @param PHPExcel $objPHPExcel
     * @param array $campaignReportDataByDate
     */
    protected function addStats(PHPExcel $objPHPExcel, $campaignReportDataByDate)
    {
        $objPHPExcel->createSheet(NULL, 1);
        $activeSheet = $objPHPExcel->setActiveSheetIndex(1);
        $this->addLogo($activeSheet, 'E');
        $this->setHeader($activeSheet, $this->getHeaders(), 'E');
        $activeSheet->setTitle('Статистика');

        $activeSheet
            ->setCellValue('B14', 'Дата')
            ->setCellValue('C14', 'Кол-во переходов');

        $row = 15;
        foreach($campaignReportDataByDate as $date){
            $activeSheet
                ->setCellValue('B'.$row, Yii::app()->dateFormatter->formatDateTime($date['date'], 'short', null))
                ->setCellValue('C'.$row, $date['clicks'] + $date['fake_clicks']);

            $row++;
        }

        $activeSheet
            ->setCellValue('B'.$row, 'Итого')
            ->setCellValue('C'.$row, $this->campaignReportData ? $this->campaignReportData['clicks'] + $this->campaignReportData['fake_clicks'] : 0);

        $this->formatTable($activeSheet, 'B','14','C',$row, array(
            'innerRowHeight' => 15,
            'headerRowHeight' => 27,
            'leftColumnAlignment' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ));

        $activeSheet->getStyle('C15:C'.$row)->getNumberFormat()->setFormatCode(self::FORMAT_NUMBER_SEPARATED);

        $activeSheet->getColumnDimension('B')->setWidth(13.43 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(16.29 * 1.05);
    }

    /**
     * Добавляет страницу качества с целями
     *
     * @param PHPExcel $objPHPExcel
     * @param array $actionsReportData
     */
    protected function addActionsStats(PHPExcel $objPHPExcel, $actionsReportData)
    {
        $activeSheet = $objPHPExcel->createSheet(null,2);
        $activeSheet = $objPHPExcel->setActiveSheetIndex(2);
        $this->addLogo($activeSheet, 'D');
        $this->setHeader($activeSheet,$this->getHeaders(), 'D');
        $activeSheet->setTitle('Статистика по целям');

        $activeSheet
            ->setCellValue('B14', 'Цель')
            ->setCellValue('C14', 'Кол-во действий');

        $row = 15;
        foreach($actionsReportData['rows'] as $data){
            $activeSheet
                ->setCellValue('B'.$row, $data['name'])
                ->setCellValue('C'.$row, $data['actions']);

            $row++;
        }

        $activeSheet
            ->setCellValue('B'.$row, 'Итого')
            ->setCellValue('C'.$row, $actionsReportData['total']['actions']);

        $this->formatTable($activeSheet, 'B','14','C',$row, array(
            'innerRowHeight' => 15,
            'leftColumnAlignment' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ));

        $activeSheet->getColumnDimension('B')->setWidth(50 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(15 * 1.05);
    }

    /**
     * Добавляет страницу с независимой аналитикой
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function addIndependentAnalyst(PHPExcel $objPHPExcel)
    {
        $objPHPExcel->createSheet(NULL);
        $activeSheet = $objPHPExcel->setActiveSheetIndex($objPHPExcel->getSheetCount()-1);

        $this->addLogo($activeSheet, 'E');
        $this->setHeader($activeSheet, $this->getHeaders(),'E');
        $activeSheet->setTitle('Сторонняя аналитика');

        $activeSheet->mergeCells('A14:E14');
        $activeSheet->getRowDimension(14)->setRowHeight(255);
        $activeSheet->getStyle('A14')->getAlignment()
            ->setWrapText(true)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $activeSheet->setCellValue('A14',
            "В период с ______________ средняя глубина просмотра составила ______ страниц, ср.время на сайте _____ секунду.\n"
            . "\n"
            . "Примечания по сбору статистики и возможным расхождениям между статистикой tTarget и статистикой Google Analytics : \n"
            . "- часть браузеров не передает адрес страницы, с которой совершается переход. Такие просмотры Google Analytics отнесет к прямым заходам на сайт (например, из закладок) - (direct) / (none)\n"
            . "- часть браузеров не передает промежуточное звено между источником трафика и входной страницей (т.е. в данном случае систему ttarget) Такие источники в отчете  отмечаются параметром /referral\n"
            . "- посетители (входы) учитываются в Google Analytics, только если у них в браузере включены файлы cookie\n"
            . "- если отсутствует обращение в Google Analytics, т.е. не выполнен код отслеживания, данные о просмотрах страниц представлены не будут"
        );

        $activeSheet->getColumnDimension('A')->setWidth(35.14 * 1.05);

        $this->setPageFit($activeSheet, self::FIT_TO_PAGE, PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    }

    /**
     * Возвращает показатели по действиям для кампании
     * @return array
     */
    protected function getActionsReportData()
    {
        return ReportDailyByCampaignAndPlatformAndAction::model()->getForCampaignByAction(
            $this->campaign->id,
            true,
            $this->dateFrom,
            $this->dateTo
        );
    }

    /**
     * Возвращает показатели по дате для кампании за период
     * @return array
     */
    protected function getCampaignReportDataByDate()
    {
        return ReportDailyByCampaign::model()->getByPeriod(
            $this->campaign->id,
            $this->dateFrom,
            $this->dateTo
        );
    }

    /**
     * Возвращает итоговые показатели по кампании за период
     * @param null $campaignReportDataByDate
     * @return array
     */
    protected function getCampaignReportData($campaignReportDataByDate = null)
    {
        return ReportDailyByCampaign::model()->getTotalByPeriod(
            $this->campaign->id,
            $this->dateFrom,
            $this->dateTo,
            $campaignReportDataByDate
        );
    }

    /**
     * @return array Возвращает отчет по новостям кампании за период
     */
    protected function getNewsReportData()
    {
        return ReportDailyByNews::model()->getAllTotalByPeriod(
            $this->getCampaignNewsIds(),
            $this->dateFrom,
            $this->dateTo
        );
    }
}
