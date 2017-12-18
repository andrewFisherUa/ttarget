<?php

/**
 * Формирует обзорный отчет по площадкам
 */
class ExcelReportPlatformsOverview extends ExcelReport
{
    public function __construct()
    {
        $this->title = 'Обзорный отчет по площадкам на '.Yii::app()->dateFormatter->formatDateTime(time());
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        $model = new Platforms('search');
        $model->unsetAttributes();
        $provider = $model->search(array(), null);
        return $provider->getData();
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
            ->setCellValue('A8', 'ID')
            ->setCellValue('B8', 'Сервер')
            ->setCellValue('C8', 'Контакт')
            ->setCellValue('D8', 'Активность')
            ->setCellValue('E8', 'Доступность кода')
            ->setCellValue('F8', 'Посещаемость')
            ->setCellValue('G8', 'Внешняя сеть')
            ->setCellValue('H8', 'Бюджет за сегодня')
            ->setCellValue('I8', 'К выводу')
            ->setCellValue('J8', 'Сегмент');

        $objPHPExcel->getActiveSheet()->getStyle('A8:J8')->applyFromArray(
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
        /** @var Platforms $row */
        foreach ($reportData as $row) {
            $activeSheet
                ->setCellValue('A'.$rowc, $row->id)
                ->setCellValue('B'.$rowc, $row->server)
                //
                ->setCellValue('D'.$rowc, $row->is_active ? 'активна' : 'не активна')
                ->setCellValue('E'.$rowc, $row->is_code_active ? 'Да' : 'Нет')
                ->setCellValue('F'.$rowc, $row->visits_count)
                ->setCellValue('G'.$rowc, $row->is_external ? 'Да' : 'Нет')
                ->setCellValue('H'.$rowc, $row->getDailyProfit())
                ->setCellValue('I'.$rowc, $row->billing_debit)
                ->setCellValue('J'.$rowc, $row->tag_names);


            $activeSheet->getStyle('D'.$rowc)->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $row->is_active ? 'eaf1dd' : 'f2dddc')
            ));
            $activeSheet->getStyle('E'.$rowc)->getFill()->applyFromArray(array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => $row->is_code_active ? 'eaf1dd' : 'f2dddc')
            ));

            if(isset($row->user)){
                $activeSheet->setCellValue('C'.$rowc, $row->user->login);
                $activeSheet->getCell('C'.$rowc)->getHyperlink()->setUrl('mailto:'.$row->user->email);
            }

            ++$rowc;
        }

        --$rowc;

//        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 8, 'J', $rowc, array('headerRowHeight' => 27, 'formatTotal' => false));

        $activeSheet->getStyle('H9:I'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getColumnDimension('A')->setWidth(4.29 + .71);
        $activeSheet->getColumnDimension('B')->setWidth(25);
        $activeSheet->getColumnDimension('C')->setWidth(31 + .71);
        $activeSheet->getColumnDimension('D')->setWidth(12.17 + .71);
        $activeSheet->getColumnDimension('E')->setWidth(12.17 + .71);
        $activeSheet->getColumnDimension('F')->setWidth(15 + .71);
        $activeSheet->getColumnDimension('G')->setWidth(8.57 + .71);
        $activeSheet->getColumnDimension('H')->setWidth(10 + .71);
        $activeSheet->getColumnDimension('I')->setWidth(10 + .71);
        $activeSheet->getColumnDimension('J')->setWidth(50 + .71);
    }

    protected function formatTable(PHPExcel_Worksheet $activeSheet, $x1, $y1, $x2, $y2, $customParameters = array())
    {
        parent::formatTable($activeSheet, $x1, $y1, $x2, $y2, $customParameters);

        $activeSheet->getStyle('H9:I'.$y2)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        ));

        $activeSheet->getStyle('J8:J'.$y2)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        ));
    }

    /**
     * Заголовок страницы
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setHeader(PHPExcel $objPHPExcel)
    {
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        $activeSheet->setTitle('Отчёт');
    }

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

        $this->setTableHeader($this->objPHPExcel);
        $this->addDataTable($this->objPHPExcel, $reportData);

        $this->addLogo($this->objPHPExcel->getActiveSheet());
        $this->setHeader($this->objPHPExcel, $reportData);

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }
}
