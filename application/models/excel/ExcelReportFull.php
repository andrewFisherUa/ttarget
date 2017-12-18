<?php

/**
 * Формирует отчет по компании за весь период
 */
class ExcelReportFull extends ExcelReportByPeriod
{
    public function __construct(Campaigns $campaign,$dateFrom, $dateTo)
    {
        parent::__construct($campaign,$dateFrom, $dateTo);
        $this->title = 'Полный отчет по рекламной кампании';
    }

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        parent::build();

        $this->addPlacementExamples($this->objPHPExcel);
        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }


    /**
     * Добавляет страницу примеров размещения
     *
     * @param PHPExcel $objPHPExcel
     */
    private function addPlacementExamples(PHPExcel $objPHPExcel)
    {
        $objPHPExcel->createSheet(1);
        $activeSheet = $objPHPExcel->setActiveSheetIndex(1);

        $this->addLogo($activeSheet, 'C');
        $this->setHeader($activeSheet, $this->getHeaders(), 'C');
        $activeSheet->setTitle('Примеры размещений');

        $activeSheet
            ->setCellValue('A14', 'Дата создания')
            ->setCellValue('B14', 'Изображение')
            ->setCellValue('C14', 'Заголовок');

        $row = 15;
        $linkColor = new PHPExcel_Style_Color( 'FF538ed5' );
        foreach ($this->campaign->news as $news) {
            foreach($news->teasers as $teaser){
                if($teaser->is_external){
                    continue;
                }
                $activeSheet->setCellValue('A'.($row+2), Yii::app()->dateFormatter->formatDateTime($teaser->create_date, 'short', null));

                $img = $this->getTeaserImage($teaser);
                $img->setWorksheet($activeSheet);
                $img->setCoordinates('B'.$row);
                $activeSheet->mergeCells('B'.$row.':B'.($row+4));
                $activeSheet->getStyle('B'.$row.':B'.($row+4))->applyFromArray(array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        )
                    ),
                ));

                $activeSheet
                        ->setCellValue('C'.($row+1), $teaser->title)
                        ->setCellValue('C'.($row+2), $teaser->getEncryptedAbsoluteUrl())
                        ->setCellValue('C'.($row+3), 'Сегмент: '.implode(' | ',$teaser->getTagNames()))
                        ->setCellValue('C'.($row+4), $news->name);

                $activeSheet->getStyle('C'.($row+1))->getFont()->setBold(true);
                $activeSheet->getStyle('C'.($row+2))->getFont()->setColor($linkColor);
                $activeSheet->getCell('C'.($row+2))->getHyperlink()->setUrl($teaser->getEncryptedAbsoluteUrl());
                $activeSheet->getStyle('C'.($row+4))->applyFromArray(array(
                    'font' => array( 'size' => 8 ),
                    'alignment' => array( 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER )
                ));

                $activeSheet->getStyle('A'.$row.':C'.($row+4))->applyFromArray(array(
                    'borders' => array(
                        'outline' => array(
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => array( 'rgb' => '7f7f7f' )
                        )
                    )
                ));

                $row += 6;
            }
        }

        $this->formatTableHeader($activeSheet, 'A', '14', 'C', '14');
        $activeSheet->getStyle('A15:C'.($row-2))->applyFromArray(array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('rgb' => '7f7f7f')
                )
            ),
        ));

        $activeSheet->getColumnDimension('A')->setWidth(10.29 * 1.05);
        $activeSheet->getColumnDimension('B')->setWidth(15.71 * 1.05);
        $activeSheet->getColumnDimension('C')->setWidth(71 * 1.05);
    }

    private function getTeaserImage(Teasers $teaser)
    {
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setName($teaser->title);
        $path = Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . $teaser->picture;
        if(!file_exists($path)){
            $path = $path = Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . 'notfound.png';
        }
        $objDrawing->setPath($path);
        $objDrawing->setWidthAndHeight(95, 95);
        $objDrawing->setOffsetX(10);
        $objDrawing->setOffsetY(2);
        return $objDrawing;
    }
}
