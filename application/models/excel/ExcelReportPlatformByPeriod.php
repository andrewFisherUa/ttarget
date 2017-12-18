<?php

/**
 * Формирует отчет по площадке на заданный период
 */
class ExcelReportPlatformByPeriod extends ExcelReportOld
{
    /**
     * @var Platforms
     */
    protected $platform;

    protected $title;


    public function __construct(Platforms $platform, $dateFrom, $dateTo)
    {
        $this->platform = $platform;
        $this->title        = 'Отчет по партнеру ' . $this->platform->server;

        $this->setPeriod($dateFrom, $dateTo);
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
        $this->addLogo($this->objPHPExcel->getActiveSheet());
        $this->setHeader($this->objPHPExcel, $reportData);

        $this->setTableHeader($this->objPHPExcel);
        $this->addDataTable($this->objPHPExcel, $reportData);

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }

    /**
     * @return array Возвращает данные для отчета
     */
    protected function getReportData()
    {
        return ReportDailyByPlatform::model()->getByPeriod(
            $this->platform->id,
            $this->dateFrom,
            $this->dateTo
        );
    }

    /**
     * Добавляет метаданые к файлу отчета
     *
     * @param PHPExcel $objPHPExcel
     */
    protected function setMetadata(PHPExcel $objPHPExcel)
    {
        $objPHPExcel->getProperties()
            ->setCreator($this->title)
            ->setLastModifiedBy($this->title)
            ->setTitle($this->title)
            ->setSubject($this->title)
            ->setDescription($this->title)
            ->setKeywords($this->title)
            ->setCategory($this->title);
    }

    /**
     * @return string Возвращает название файла отчета
     */
    public function getFileName()
    {
        return $this->title;
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
            ->setCellValue('E4', $this->platform->server)

            ->setCellValue('A5', 'Период отчета по трафику:')
            ->setCellValue('E5', date('d.m.Y', strtotime($this->dateFrom)) . '-' . date('d.m.Y', strtotime($this->dateTo)))

            ->setCellValue('A6', 'Количество переходов на момент отчета(по периоду):')
            ->setCellValue('E6', $reportData['total']['clicks'])

            ->setCellValue('A7', 'Ставка за переход::')
            ->setCellValue('E7', $reportData['total']['cost'] . ' ' . PlatformsCpc::getCurrency($this->platform->currency))

            ->setCellValue('A8', 'Выплаты за трафик:')
            ->setCellValue('E8', $reportData['total']['price'] . ' ' . PlatformsCpc::getCurrency($this->platform->currency));

        $activeSheet->getStyle('E6:E8')->applyFromArray(
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
            ->setCellValue('A10', 'дата')
            ->setCellValue('B10', 'Показы')
            ->setCellValue('C10', 'Общее количество переходов')
            ->setCellValue('D10', 'CTR')
            ->setCellValue('E10', 'Бюджет')
            ->setCellValue('F10', 'Скликивания');

        $objPHPExcel->getActiveSheet()->getStyle('A10:F10')->applyFromArray(
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

        $rowc = 11;
        foreach ($reportData['rows'] as $row) {
            $activeSheet
                ->setCellValue('A'.$rowc, $row['date'])
                ->setCellValue('B'.$rowc, $row['shows'])
                ->setCellValue('C'.$rowc, $row['clicks'])
                ->setCellValue('D'.$rowc, $row['ctr'])
                ->setCellValue('E'.$rowc, $row['price'])
                ->setCellValue('F'.$rowc, $row['clickfraud']);

            ++$rowc;
        }

        $this->addTableTotal($activeSheet, $reportData, $rowc);
        $this->formatTable($activeSheet, 'A', 10, 'F', $rowc);

        $activeSheet->getStyle('D10:E'.$rowc)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $activeSheet->getColumnDimension('A')->setWidth(10);
        $activeSheet->getColumnDimension('B')->setWidth(13);
        $activeSheet->getColumnDimension('C')->setWidth(25);
        $activeSheet->getColumnDimension('D')->setWidth(20);
        $activeSheet->getColumnDimension('E')->setWidth(20);
        $activeSheet->getColumnDimension('F')->setWidth(20);
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
            ->setCellValue('E'.$rowc, $reportData['total']['price'])
            ->setCellValue('F'.$rowc, $reportData['total']['clickfraud']);

        $activeSheet->getStyle('A'.$rowc.':F'.$rowc)->applyFromArray(
            array(
                'font' => array(
                    'bold' => true
                ),
            )
        );
    }

    /**
     * Форматирует таблицу с данными
     *
     * @param PHPExcel_Worksheet $activeSheet
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     */
    protected function formatTable(PHPExcel_Worksheet $activeSheet, $x1, $y1, $x2, $y2)
    {
        $activeSheet->getStyle($x1.$y1.':'.$x2.$y1)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '528ed6')
                ),
                'font' => array(
                    'size' => 10,
                    'color' => array('rgb' => 'FFFFFF'),
                ),
                'alignment' => array(
                    'wrap' => true,
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
            )
        );

        $activeSheet->getStyle($x1.$y1.':'.$x2.$y2)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                )
            )
        );
    }
}
