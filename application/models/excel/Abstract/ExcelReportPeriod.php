<?php


abstract class ExcelReportPeriod extends ExcelReport{
    /**
     * @var string Y-m-d
     */
    protected $dateFrom;

    /**
     * @var string Y-m-d
     */
    protected $dateTo;

    /**
     * Устанавливает период отчета
     *
     * @param string $dateFrom
     * @param string $dateTo
     */
    protected function setPeriod($dateFrom, $dateTo)
    {
        $timeTo = strtotime($dateTo);
        $timeFrom = strtotime($dateFrom);

        if($timeTo === false){
            $timeTo = time();
        }
        if($timeFrom === false || $timeFrom > $timeTo){
            $timeFrom=$timeTo;
        }

        $this->dateFrom = date('Y-m-d', $timeFrom);
        $this->dateTo   = date('Y-m-d', $timeTo);
    }
} 