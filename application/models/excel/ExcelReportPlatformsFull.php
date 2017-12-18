<?php

/**
 * Формирует отчет по всем площадкам на заданный период
 */
class ExcelReportPlatformsFull extends ExcelReportPlatformsExternal
{
    public function __construct($dateFrom, $dateTo)
    {
        $this->title = 'Полный отчет по площадкам';
        $this->platformTitle = 'полный отчет по площадкам';
        $this->setPeriod($dateFrom, $dateTo);
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        return ReportDailyByCampaignAndPlatform::model()->getFullByPlatforms(
            $this->dateFrom,
            $this->dateTo
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
            ->setCellValue('A8', 'площадка')
            ->setCellValue('B8', 'рекламная кампания')
            ->setCellValue('C8', 'показы')
            ->setCellValue('D8', 'количество переходов')
            ->setCellValue('E8', 'CTR')
            ->setCellValue('F8', 'процент скликивания')
            ->setCellValue('G8', 'бюджет, у.е.')
            ->setCellValue('H8', 'валюта');

        $objPHPExcel->getActiveSheet()->getStyle('A8:H8')->applyFromArray(
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
                ->setCellValue('C'.$rowc, $row['shows'])
                ->setCellValue('D'.$rowc, $row['clicks'])
                ->setCellValue('E'.$rowc, $row['ctr'])
                ->setCellValue('F'.$rowc, $row['clickfraud'])
                ->setCellValue('G'.$rowc, $row['price'])
                ->setCellValue('H'.$rowc, PlatformsCpc::getCurrency($row['currency']));

            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 8, 'H', $rowc);

        $activeSheet->getStyle('E9:G'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getColumnDimension('A')->setWidth(12.33 + .83);
        $activeSheet->getColumnDimension('B')->setWidth(25);
        $activeSheet->getColumnDimension('C')->setWidth(14 + .83 );
        $activeSheet->getColumnDimension('D')->setWidth(12.17 + .83);
        $activeSheet->getColumnDimension('E')->setWidth(10.5 + .83);
        $activeSheet->getColumnDimension('F')->setWidth(12.5 + .83);
        $activeSheet->getColumnDimension('G')->setWidth(11.5 + .83);
        $activeSheet->getColumnDimension('H')->setWidth(7.17 + .83);
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
            ->setCellValue('C'.$rowc, $reportData['total']['shows'])
            ->setCellValue('D'.$rowc, $reportData['total']['clicks'])
            ->setCellValue('E'.$rowc, $reportData['total']['ctr'])
            ->setCellValue('F'.$rowc, $reportData['total']['clickfraud'])
            ->setCellValue('G'.$rowc, $reportData['total']['price']);

        $activeSheet->getStyle('A'.$rowc.':D'.$rowc)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
    }
}
