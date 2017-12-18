<?php
abstract class ExcelReportCampaign extends ExcelReportPeriod
{
    /**
     * @var Campaigns
     */
    protected $campaign;

    public function __construct(Campaigns $campaign, $dateFrom, $dateTo)
    {
        parent::__construct();
        $this->campaign = $campaign;
        $this->setPeriod($dateFrom, $dateTo);
    }

    /**
     * Возвращает идентификаторы новостей кампании
     *
     * @return array
     */
    protected function getCampaignNewsIds()
    {
        $newsIds = array();
        if ($this->campaign->news) {
            foreach ($this->campaign->news as $news) {
                array_push($newsIds, $news->id);
            }
        }

        return $newsIds;
    }

    /**
     * Возвращает значение колонки для гео-таргетинга новости
     *
     * @param Campaigns $campaign
     * @return string
     */
    protected function getCampaignGeoTargeting(Campaigns $campaign)
    {
        $geoTargeting = $campaign->getExceptedCities();
        return ($geoTargeting) ? ('Исключить '. $geoTargeting) : 'Показывать всем';
    }

    protected function setPeriod($dateFrom, $dateTo)
    {
        parent::setPeriod($dateFrom, $dateTo);
        if (strtotime($this->dateFrom) < strtotime($this->campaign->date_start)){
            $this->dateFrom = $this->campaign->date_start;
        }
        if (strtotime($this->dateTo) > strtotime($this->campaign->date_end)){
            $this->dateTo = $this->campaign->date_end;
        }
    }


    protected function getHeaders()
    {
        return array(
            'Рекламная кампания:' => $this->campaign->name,
            'Период проведения кампании:' =>
                Yii::app()->dateFormatter->formatDateTime($this->campaign->date_start, 'short', null) . '-'
                . Yii::app()->dateFormatter->formatDateTime($this->campaign->date_end, 'short', null),
            'Период отчета:' =>
                Yii::app()->dateFormatter->formatDateTime($this->dateFrom, 'short', null) . '-'
                . Yii::app()->dateFormatter->formatDateTime($this->dateTo, 'short', null),
        );
    }
}