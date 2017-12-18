<?php
/** Общий клас для новых отчетов */
class ExcelReportBilling extends ExcelReportPeriod{

    protected $is_active;

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        $this->objPHPExcel = new PHPExcel();

        $reportData = $this->getReportData();

        $this->setMetadata($this->objPHPExcel);
        $this->setFont($this->objPHPExcel);
        $activeSheet = $this->objPHPExcel->getActiveSheet();
        $activeSheet->setTitle('Отчёт');

        $this->setTableHeader($this->objPHPExcel);
        $this->addDataTable($this->objPHPExcel, $reportData);

        $this->addLogo($activeSheet);
        $this->setHeader($activeSheet, $this->getHeaders());

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }

    protected function getHeaders()
    {
        return array(
            'Период отчета:' =>
                Yii::app()->dateFormatter->formatDateTime($this->dateFrom, 'short', null) . ' - '
                . Yii::app()->dateFormatter->formatDateTime($this->dateTo, 'short', null)
        );
    }

    protected function formatTable(PHPExcel_Worksheet $activeSheet, $x1, $y1, $x2, $y2, $customParameters = array())
    {
        parent::formatTable($activeSheet, $x1, $y1, $x2, $y2, $customParameters);

        $activeSheet->getStyle($x2.$y1.':'.$x2.$y2)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        ));
    }
}