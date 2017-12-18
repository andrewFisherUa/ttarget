<?php
abstract class ExcelReport
{
    /**
     * Варианты размещения страницы. @see setPageFit
     */
    const FIT_TO_PAGE = 1;
    const FIT_TO_WIDTH = 2;
    const FIT_TO_HEIGHT = 3;

    /**
     * Формат с разделением порядков, без десятичной части
     */
    const FORMAT_NUMBER_SEPARATED = '#,##0';


    /**
     * @var PHPExcel
     */
    protected $objPHPExcel;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * Формирует отчет
     * @return $this
     */
    abstract public function build();

    /**
     * @return string Возвращает название файла отчета
     */
    protected function getFileName()
    {
        return $this->title;
    }

    public function __construct()
    {
        $this->objPHPExcel = new PHPExcel();
    }

    /**
     * @param $reportName
     *
     * @return ExcelReport
     * @throws Exception
     */
    public static function create($reportName, $args)
    {
        if(class_exists($reportName) && (is_subclass_of($reportName, __CLASS__) || is_subclass_of($reportName, 'ExcelReportOld'))){
            $args = func_get_args();
            array_shift($args);

            $reflection = new ReflectionClass($reportName);
            return $reflection->newInstanceArgs($args);
        }else{
            throw new Exception('Report not found');
        }
    }

    /**
     * Выводит отчет
     */
    public function render()
    {
        $this->objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->getFileName() . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * Сохраняет отчет
     * @param string $file
     */
    public function save($file)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        $objWriter->save($file);
    }

    /**
     * Добавляет лого на страницу
     *
     * @param PHPExcel_Worksheet $activeSheet
     * @param string|null $rightColumn
     */
    protected function addLogo(PHPExcel_Worksheet $activeSheet, $rightColumn = null)
    {
        $logo_path = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . 'htdocs/images' . DIRECTORY_SEPARATOR . 'logo2.png';
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setWorksheet($activeSheet);
        $objDrawing->setName("Лого");
        $objDrawing->setPath($logo_path);
        $objDrawing->setCoordinates('A1');
        $objDrawing->setWidthAndHeight(308.3, 75.9);


        if(null === $rightColumn){
            $rightColumn = $activeSheet->getHighestDataColumn();
        }
        $activeSheet->mergeCells('A5:'.$rightColumn.'5');

        $activeSheet
            ->setCellValue('A5', $this->title);

        $activeSheet->getStyle('A5')->applyFromArray(array(
            'font' => array(
                'color' => array('rgb' => '376091'),
                'bold' => true,
            ),
            'alignment' => array(
                'indent' => 10,
            )
        ));

        $activeSheet->getStyle('A1:'.$rightColumn.'6')->applyFromArray(array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_NONE,
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'FFFFFF'),
            )
        ));

        $activeSheet->getStyle('A6:'.$rightColumn.'6')->applyFromArray(array(
            'borders' => array(
                'bottom' => array(
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array('rgb' => '316194')
                )
            )
        ));
    }

    protected function setHeader(PHPExcel_Worksheet $activeSheet, $headers, $rightColumn = null)
    {
        if (null === $rightColumn) {
            $rightColumn = $activeSheet->getHighestDataColumn();
        }

        $i = 8;
        foreach ($headers as $title => $value) {
            $activeSheet
                ->setCellValue('A' . $i, $title)
                ->setCellValue($rightColumn . $i, $value);
            $i++;
        }

        $this->formatHeader($activeSheet, 'A', '8', $rightColumn, $i - 1);
        return $rightColumn;
    }

    protected function formatHeader($activeSheet, $x1, $y1, $x2, $y2)
    {
        $activeSheet->getStyle($x1.$y1.':'.$x2.$y2)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'dbe5f1')
                ),
                'borders' => array(
                    'horizontal' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '7f7f7f')
                    ),
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '7f7f7f')
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '7f7f7f')
                    )
                )
            )
        );

        $activeSheet->getStyle($x2.$y1.':'.$x2.$y2)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        ));
    }

    /**
     * Добавляет метаданые к файлу отчета
     */
    protected function setMetadata()
    {
        $this->objPHPExcel->getProperties()
            ->setCreator($this->title)
            ->setLastModifiedBy($this->title)
            ->setTitle($this->title)
            ->setSubject($this->title)
            ->setDescription($this->title)
            ->setKeywords($this->title)
            ->setCategory($this->title);
    }

    /**
     * Устанавливает шрифт отчета
     */
    protected function setFont()
    {
        $this->objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
    }

    protected function formatTableHeader(PHPExcel_Worksheet $activeSheet, $x1, $y1, $x2, $y2)
    {
        $activeSheet->getStyle($x1.$y1.':'.$x2.$y2)->applyFromArray(
            array(
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'dbe5f1')
                ),
                'font' => array(
                    'bold' => true,
                    'color' => array('rgb' => '000000'),
                ),
                'alignment' => array(
                    'wrap' => true,
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
                ),
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '7f7f7f')
                    ),
                    'vertical' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '7f7f7f')
                    )
                )
            )
        );
    }

    protected function formatTable(PHPExcel_Worksheet $activeSheet, $x1, $y1, $x2, $y2, $customParameters = array())
    {
        $params = array_merge(
            array(
                'headerRowHeight' => 20.25,
                'innerRowHeight' => 19.5,
                'alignmentHorizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'alignmentVertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'leftColumnAlignment' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'formatTotal' => true
            ),
            $customParameters
        );

        $this->formatTableHeader($activeSheet, $x1, $y1, $x2, $y1);

        $activeSheet->getRowDimension($y1)->setRowHeight($params['headerRowHeight']);
        for($i = $y1+1; $i<$y2; $i++){
            $activeSheet->getRowDimension($i)->setRowHeight($params['innerRowHeight']);
        }

        $activeSheet->getStyle($x1.$y1.':'.$x2.$y2)->applyFromArray(
            array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '7f7f7f')
                    ),
                    'allborders' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '7f7f7f'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => $params['alignmentHorizontal'],
                    'vertical' => $params['alignmentVertical']
                ),
            )
        );

        if($params['formatTotal']) {
            $activeSheet->getStyle($x1 . $y2 . ':' . $x2 . $y2)->applyFromArray(
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
        }

        $activeSheet->getStyle($x1.($y1+1).':'.$x1.$y2)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => $params['leftColumnAlignment'],
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        ));
    }

    /**
     * @param PHPExcel_Worksheet $activeSheet
     * @param $fitTo
     * @param string $orientation
     */
    protected function setPageFit(PHPExcel_Worksheet $activeSheet, $fitTo, $orientation = PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT)
    {
        $page = $activeSheet->getPageSetup();
        $page
            ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
            ->setOrientation($orientation)
            ->setFitToPage(true);
        if($fitTo == self::FIT_TO_PAGE){
            $page
                ->setFitToWidth(1)
                ->setFitToHeight(1);
        }elseif($fitTo = self::FIT_TO_WIDTH){
            $page
                ->setFitToWidth(1)
                ->setFitToHeight(0);
        }else{
            $page
                ->setFitToWidth(0)
                ->setFitToHeight(1);
        }
    }
}