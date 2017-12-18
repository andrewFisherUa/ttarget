<?php

/**
 * Формирует отчет по всем внешним площадкам на заданный период
 */
class ExcelReportPlatformsExternal extends ExcelReportPlatformByPeriod
{
    private $externals = 1;
    protected $platformTitle;

    public function __construct($dateFrom, $dateTo, $externals)
    {
        $this->externals     = (int) ($externals == 1);
        $this->title         = 'Отчет для '.($this->externals ? 'внешних' : 'внутренних').' сетей';
        $this->platformTitle = ($this->externals ? 'внешние' : 'внутренние') . ' сети';

        $this->setPeriod($dateFrom, $dateTo);
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        return ReportDailyByCampaignAndPlatform::model()->getForPlatformsByPeriod(
            $this->dateFrom,
            $this->dateTo,
            $this->externals
        );
    }

    /**
     * Устанавливает заголовок первой страницы
     *
     * @param PHPExcel $objPHPExcel
     * @param array $reportData
     */
    protected function setHeader(PHPExcel $objPHPExcel, array &$reportData)
    {
        $objPHPExcel->getActiveSheet()->setTitle('Отчёт');
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);

        $activeSheet
            ->setCellValue('A4', 'Партнерская площадка:')
            ->setCellValue('D4', $this->platformTitle)

            ->setCellValue('A5', 'Период отчета по трафику:')
            ->setCellValue('D5', date('d.m.Y', strtotime($this->dateFrom)) . '-' . date('d.m.Y', strtotime($this->dateTo)))

            ->setCellValue('A6', 'Количество переходов на момент отчета(по периоду):')
            ->setCellValue('D6', $reportData['total']['clicks']);

        $activeSheet->getStyle('D6')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'd7e4bc')
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );
    }

    /**
     * Устанавливает заголовок таблицы
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setTableHeader(PHPExcel $objPHPExcel)
    {
        $objPHPExcel
            ->setActiveSheetIndex(0)
            ->setCellValue('A8', 'сеть')
            ->setCellValue('B8', 'рекламная кампания')
            ->setCellValue('C8', 'количество переходов')
            ->setCellValue('D8', 'процент скликивания');

        $objPHPExcel->getActiveSheet()->getStyle('A8:D8')->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'd7e4bc')
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );
    }

    /**
     * Добавляет таблицу со списком
     *
     * @param PHPExcel $objPHPExcel
     * @param array $reportData
     */
    protected function addDataTable(PHPExcel $objPHPExcel, array $reportData)
    {
        $activeSheet = $objPHPExcel->getActiveSheet();

        $rowc = 9;
        foreach ($reportData['rows'] as $row) {
            $activeSheet
                ->setCellValue('A'.$rowc, $row['platform_name'])
                ->setCellValue('B'.$rowc, $row['campaign_name'])
                ->setCellValue('C'.$rowc, $row['clicks'])
                ->setCellValue('D'.$rowc, $row['clickfraud']);

            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 8, 'D', $rowc);

        $activeSheet->getStyle('D9:D'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getColumnDimension('A')->setWidth(18.33 + .83);
        $activeSheet->getColumnDimension('B')->setWidth(25);
        $activeSheet->getColumnDimension('C')->setWidth(12.33 + .83 );
        $activeSheet->getColumnDimension('D')->setWidth(14.83);
    }

    /**
     * Добавляет в таблицу сводные данные по отчету
     *
     * @param PHPExcel_Worksheet $activeSheet
     * @param array $reportData
     * @param $rowc
     */
    protected function addTableTotal(PHPExcel_Worksheet $activeSheet, array $reportData, $rowc)
    {
        $activeSheet
            ->setCellValue('A'.$rowc, 'Итог')
            ->setCellValue('C'.$rowc, $reportData['total']['clicks'])
            ->setCellValue('D'.$rowc, $reportData['total']['clickfraud']);

        $activeSheet->getStyle('A'.$rowc.':D'.$rowc)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
    }
}
