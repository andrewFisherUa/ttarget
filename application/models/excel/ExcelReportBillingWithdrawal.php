<?php
class ExcelReportBillingWithdrawal extends ExcelReportBilling{
    public function __construct($dateFrom, $dateTo, $is_active = null)
    {
        $this->title = 'Запросы на вывод средств';
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
        return ReportDailyByPlatform::model()->getWithdrawalReport(
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
            ->setCellValue('A10', '№ Счёта')
            ->setCellValue('B10', 'Дата выставления')
            ->setCellValue('C10', 'Дата оплаты')
            ->setCellValue('D10', 'Площадка')
            ->setCellValue('E10', 'Баланс')
            ->setCellValue('F10', 'Сумма запроса')
            ->setCellValue('G10', 'НДС')
            ->setCellValue('H10', 'Сумма с НДС')
            ->setCellValue('I10', 'Статус')
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
                ->setCellValue('A'.$rowc, $row['number'])
                ->setCellValue('B'.$rowc, date('d.m.Y', strtotime($row['issuing_date'])))
                ->setCellValue('C'.$rowc, $row['paid_date'] ? date('d.m.Y', strtotime($row['paid_date'])) : '')
                ->setCellValue('D'.$rowc, $row['platform_name'])
                ->setCellValue('E'.$rowc, $row['debit'])
                ->setCellValue('F'.$rowc, $row['sum'])
                ->setCellValue('G'.$rowc, ($row['is_vat'] ? Yii::app()->params->VAT/100 : 0))
                ->setCellValue('H'.$rowc, $row['sum_with_vat'])
                ->setCellValue('I'.$rowc, $row['is_paid'] ? 'оплачен' : 'не оплачен')
                ->setCellValue('J'.$rowc, $row['is_active'] ? 'активна' : 'неактивна');

            $activeSheet->getStyle('I'.$rowc)->getFill()->applyFromArray(array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => $row['is_paid'] ? 'eaf1dd' : 'f2dddc')
            ));
            $activeSheet->getStyle('J'.$rowc)->getFill()->applyFromArray(array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => $row['is_active'] ? 'eaf1dd' : 'f2dddc')
            ));
            if($row['billing_details_type'] == null){
                $activeSheet->setCellValue('K'.$rowc, 'нет данных');
                $activeSheet->getStyle('K'.$rowc)->getFont()->getColor()->setRGB('bfbfbf');
            }else{
                $activeSheet->setCellValue('K'.$rowc, $row['billing_details_type'].': '.$row['billing_details_text']);
            }
            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 10, 'K', $rowc, array('headerRowHeight' => 27));

        $activeSheet->getStyle('E11:G'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getStyle('G11:F'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
        $activeSheet->getColumnDimension('A')->setWidth(10.71 * 1.05);
        $activeSheet->getColumnDimension('B')->setWidth(10.71 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(10.71 * 1.05);
        $activeSheet->getColumnDimension('D')->setWidth(13.29 * 1.05);
        $activeSheet->getColumnDimension('E')->setWidth(14.71 * 1.05);
        $activeSheet->getColumnDimension('F')->setWidth(14.43 * 1.05);
        $activeSheet->getColumnDimension('G')->setWidth(13.71 * 1.05);
        $activeSheet->getColumnDimension('H')->setWidth(13.71 * 1.05);
        $activeSheet->getColumnDimension('I')->setWidth(16.14 * 1.05);
        $activeSheet->getColumnDimension('J')->setWidth(19.57 * 1.05);
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
            ->setCellValue('E'.$rowc, $reportData['total']['debit'])
            ->setCellValue('F'.$rowc, $reportData['total']['sum'])
            ->setCellValue('H'.$rowc, $reportData['total']['sum_with_vat']);

        $activeSheet->getStyle('A'.$rowc.':K'.$rowc)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'dbe5f1')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => '000000'),
                ),
            )
        );

        $activeSheet
            ->setCellValue('A'.($rowc+2), 'Не оплачено (без учета ндс):')
            ->setCellValue('E'.($rowc+2), $reportData['total']['not_paid'])
            ->setCellValue('A'.($rowc+3), 'Оплачено (без учета ндс):')
            ->setCellValue('E'.($rowc+3), $reportData['total']['paid']);
        $activeSheet->getStyle('A'.($rowc+2).':E'.($rowc+2))->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'f2dddc')
        ));
        $activeSheet->getStyle('A'.($rowc+3).':E'.($rowc+3))->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' =>  'eaf1dd')
        ));
    }
} 