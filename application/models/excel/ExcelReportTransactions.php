<?php

/**
 * Формирует отчет по компании на заданный период
 */
class ExcelReportTransactions extends ExcelReportCampaign
{
    protected $transactionData;

    public function __construct(Campaigns $campaign, $dateFrom, $dateTo, $actionsLog)
    {
        parent::__construct($campaign, $dateFrom, $dateTo);
        $this->transactionData = $actionsLog;
        $this->title = 'Отчет по транзакциям кампании';
    }

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        $this->setMetadata();
        $this->setFont();
        $this->addTransactionPage($this->objPHPExcel->getActiveSheet());

        return $this;
    }

    private function addTransactionPage(PHPExcel_Worksheet $activeSheet)
    {
        $activeSheet
            ->setTitle('Транзакции')
            ->setCellValue('A12', 'Статус')
            ->setCellValue('B12', 'Тип')
            ->setCellValue('C12', 'ID')
            ->setCellValue('D12', 'Дата')
            ->setCellValue('E12', 'IP')
            ->setCellValue('F12', 'ГЕО')
            ->setCellValue('G12', 'URL цели')
            ->setCellValue('H12', 'Источник')
            ->setCellValue('I12', 'Материал')
            ->setCellValue('J12', 'Выплата')
            ->setCellValue('K12', 'Вознаграждение')
            ->setCellValue('L12', 'Зароботок')
            ->setCellValue('M12', 'Цель');

        $row = 13;
        $availableStatuses = ActionsLog::getAvailableStatuses();
        foreach($this->transactionData['rows'] as $tr){
            $activeSheet
                ->setCellValue('A'.$row, $availableStatuses[$tr['status']])
                ->setCellValue('B'.$row, $tr['source_type_name'])
                ->setCellValue('C'.$row, $tr['id'])
                ->setCellValue('D'.$row, Yii::app()->dateFormatter->formatDateTime($tr['date']))
                ->setCellValue('E'.$row, $tr['ip'])
                ->setCellValue('F'.$row, $tr['geo'])
                ->setCellValue('G'.$row, $tr['target_url_decoded'])
                ->setCellValue('H'.$row, $tr['source_name'])
                ->setCellValue('I'.$row, $tr['target_name'])
                ->setCellValue('J'.$row, $tr['payment'])
                ->setCellValue('K'.$row, $tr['reward'])
                ->setCellValue('L'.$row, $tr['debit'])
                ->setCellValue('M'.$row, $tr['action_name']);
            $row++;
        }
        $activeSheet
            ->setCellValue('J'.$row, $this->transactionData['total']['payment'])
            ->setCellValue('K'.$row, $this->transactionData['total']['reward'])
            ->setCellValue('L'.$row, $this->transactionData['total']['debit']);

        $activeSheet->getColumnDimension('A')->setWidth(16.30 * 1.05);
        $activeSheet->getColumnDimension('B')->setWidth(16.43 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(5 * 1.05);
        $activeSheet->getColumnDimension('D')->setWidth(17.86 * 1.05);
        $activeSheet->getColumnDimension('E')->setWidth(14.14 * 1.05);
        $activeSheet->getColumnDimension('F')->setWidth(34 * 1.05);
        $activeSheet->getColumnDimension('G')->setWidth(31 * 1.05);
        $activeSheet->getColumnDimension('H')->setWidth(30.86 * 1.05);
        $activeSheet->getColumnDimension('I')->setWidth(19.14 * 1.05);
        $activeSheet->getColumnDimension('J')->setWidth(8.57 * 1.05);
        $activeSheet->getColumnDimension('K')->setWidth(8.57 * 1.05);
        $activeSheet->getColumnDimension('L')->setWidth(8.57 * 1.05);
        $activeSheet->getColumnDimension('M')->setWidth(30.70 * 1.05);

        $activeSheet->getStyle('A12:M'.$row)->getAlignment()->setWrapText(true);

        $this->formatTable($activeSheet,'A','12','M', $row,
            array('formatTotal' => true, 'innerRowHeight' => -1, 'headerRowHeight' => 27)
        );

        $this->addLogo($activeSheet);
        $this->setHeader($activeSheet, $this->getHeaders());

        $this->setPageFit($activeSheet, self::FIT_TO_WIDTH, PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
    }
}
