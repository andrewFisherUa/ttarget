<?php

/**
 * Формирует отчет по всем площадкам пользователя на заданный период
 */
class ExcelReportPlatformsFullForPlatform extends ExcelReportPlatformsExternal
{
    protected $user_id;
    public function __construct($user_id, $dateFrom, $dateTo)
    {
        $this->user_id = $user_id;
        $this->title = 'Полный отчет по площадкам';
        $this->platformTitle = 'полный отчет по площадкам';
        $this->setPeriod($dateFrom, $dateTo);
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        return ReportDailyByPlatform::model()->getTotalsByUserId(
            $this->user_id,
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
            ->setCellValue('B8', 'показы')
            ->setCellValue('C8', 'количество переходов')
            ->setCellValue('D8', 'CTR')
            ->setCellValue('E8', 'процент скликивания')
            ->setCellValue('F8', 'бюджет, у.е.')
            ->setCellValue('G8', 'валюта');

        $objPHPExcel->getActiveSheet()->getStyle('A8:G8')->applyFromArray(
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
                ->setCellValue('B'.$rowc, $row['shows'])
                ->setCellValue('C'.$rowc, $row['clicks'])
                ->setCellValue('D'.$rowc, $row['ctr'])
                ->setCellValue('E'.$rowc, $row['clickfraud'])
                ->setCellValue('F'.$rowc, $row['price'])
                ->setCellValue('G'.$rowc, PlatformsCpc::getCurrency($row['currency']));

            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 8, 'G', $rowc);

        $activeSheet->getStyle('E9:F'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getColumnDimension('A')->setWidth(12.33 + .83);
        $activeSheet->getColumnDimension('B')->setWidth(14 + .83 );
        $activeSheet->getColumnDimension('C')->setWidth(12.17 + .83);
        $activeSheet->getColumnDimension('D')->setWidth(10.5 + .83);
        $activeSheet->getColumnDimension('E')->setWidth(12.5 + .83);
        $activeSheet->getColumnDimension('F')->setWidth(11.5 + .83);
        $activeSheet->getColumnDimension('G')->setWidth(7.17 + .83);
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
            ->setCellValue('B'.$rowc, $reportData['total']['shows'])
            ->setCellValue('C'.$rowc, $reportData['total']['clicks'])
            ->setCellValue('D'.$rowc, $reportData['total']['ctr'])
            ->setCellValue('E'.$rowc, $reportData['total']['clickfraud'])
            ->setCellValue('F'.$rowc, $reportData['total']['price']);

        $activeSheet->getStyle('A'.$rowc.':C'.$rowc)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
    }
}
