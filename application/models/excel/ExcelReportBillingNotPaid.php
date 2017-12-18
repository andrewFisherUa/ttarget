<?php

/**
 * Формирует отчет по всем площадкам на заданный период
 */
class ExcelReportBillingNotPaid extends ExcelReportBilling
{
    public function __construct($dateFrom, $dateTo, $is_active = null)
    {
        $this->title = 'Биллинг неоплаченного трафика';
        $this->setPeriod($dateFrom, $dateTo);
        if($is_active == '1' || $is_active == '0') {
            $this->is_active = $is_active;
        }
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        return ReportDailyByPlatform::model()->getTotalsForBilling(
            $this->dateFrom,
            $this->dateTo,
            $this->is_active
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
            ->setCellValue('A10', 'Площадка')
            ->setCellValue('B10', 'Кол-во перех.')
            ->setCellValue('C10', 'Стоимость пер.')
            ->setCellValue('D10', 'Сумма без НДС')
            ->setCellValue('E10', 'НДС')
            ->setCellValue('F10', 'Сумма с НДС')
            ->setCellValue('G10', 'К оплате, без НДС')
            ->setCellValue('H10', 'К оплате, с НДС')
            ->setCellValue('I10', 'НДС')
            ->setCellValue('J10', 'Активность площ')
            ->setCellValue('K10', 'Реквизиты');
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

        $rowc = 11;
        foreach ($reportData['rows'] as $row) {
            $activeSheet
                ->setCellValue('A'.$rowc, $row['platform_name'])
                ->setCellValue('B'.$rowc, $row['clicks'])
                ->setCellValue('C'.$rowc, $row['cost'])
                ->setCellValue('D'.$rowc, $row['price'])
                ->setCellValue('E'.$rowc, ($row['is_vat'] ? Yii::app()->params->VAT/100 : 0))
                ->setCellValue('F'.$rowc, $row['price_with_vat'])
                ->setCellValue('G'.$rowc, $row['debit'])
                ->setCellValue('H'.$rowc, $row['debit_with_vat'])
                ->setCellValue('I'.$rowc, $row['debit_vat'])
                ->setCellValue('J'.$rowc, $row['is_active'] ? 'активна' : 'неактивна')
                ->getStyle('J'.$rowc)->applyFromArray(array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => $row['is_active'] ? 'eaf1dd' : 'f2dddc')
                    )
                ));
            if(empty($row['billing_details_type'])){
                $activeSheet->setCellValue('K'.$rowc, 'нет данных');
                $activeSheet->getStyle('K'.$rowc)->getFont()->getColor()->setRGB('bfbfbf');
            }else{
                $activeSheet->setCellValue('K'.$rowc, $row['billing_details_type'].': '.$row['billing_details_text']);
            }
            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 10, 'K', $rowc);

        $activeSheet->getStyle('C11:I'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getStyle('E11:E'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
        $activeSheet->getColumnDimension('A')->setWidth(17.43 * 1.05);
        $activeSheet->getColumnDimension('B')->setWidth(13.29 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(14.71 * 1.05);
        $activeSheet->getColumnDimension('D')->setWidth(14.71 * 1.05);
        $activeSheet->getColumnDimension('E')->setWidth( 5.71 * 1.05);
        $activeSheet->getColumnDimension('F')->setWidth(13.71 * 1.05);
        $activeSheet->getColumnDimension('G')->setWidth(16.43 * 1.05);
        $activeSheet->getColumnDimension('H')->setWidth(14.29 * 1.05);
        $activeSheet->getColumnDimension('I')->setWidth(10.00 * 1.05);
        $activeSheet->getColumnDimension('J')->setWidth(16.14 * 1.05);
        $activeSheet->getColumnDimension('K')->setWidth(35.71 * 1.05);
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
            ->setCellValue('B'.$rowc, $reportData['total']['clicks'])
            ->setCellValue('D'.$rowc, $reportData['total']['price'])
            ->setCellValue('F'.$rowc, $reportData['total']['price_with_vat'])
            ->setCellValue('G'.$rowc, $reportData['total']['debit'])
            ->setCellValue('H'.$rowc, $reportData['total']['debit_with_vat'])
            ->setCellValue('I'.$rowc, $reportData['total']['debit_vat']);
    }
}
