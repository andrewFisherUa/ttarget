<?php

/**
 * Формирует отчет по компании на заданный период для клиента
 */
class ExcelReportByPeriodForClient extends ExcelReportByPeriod
{
    public function __construct(Campaigns $campaign,$dateFrom, $dateTo)
    {
        parent::__construct($campaign,$dateFrom, $dateTo);
        $this->title = 'Отчет для самостоятельной выгрузки';
    }

    /**
     * Формирует отчет
     * @return $this
     */
    public function build()
    {
        $newsReportData           = $this->getNewsReportData();
        $campaignReportDataByDate = $this->getCampaignReportDataByDate();
        $this->campaignReportData = $this->getCampaignReportData($campaignReportDataByDate);

        $this->setMetadata($this->objPHPExcel);
        $this->setFont($this->objPHPExcel);
        $this->setNewsTableHeader($this->objPHPExcel);
        $this->addNewsTable($this->objPHPExcel, $newsReportData);

        $this->addStats($this->objPHPExcel, $campaignReportDataByDate);

        $this->objPHPExcel->setActiveSheetIndex(0);

        return $this;
    }
}
