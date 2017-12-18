<?php
class CampaignCorrection{
    const PLATFORMS_EXCLUDE_EXTERNAL = 'without_external';

    public $campaign;
    public $dataFields = array(
        'date' => 'Дата',
        'teaser_title' => 'Тизер',
        'platform_server' => 'Площадка',
        'action_name' => 'Действие',
        'shows' => 'Показы',
        'clicks' => 'Переходы',
        'actions' => 'Действия',
    );

    private $geoDefaults = array(
        'city_id' => 0,
        'country_code' => 'ZZ'
    );

    public $counter; 
    public $maxRows = 1000;
    public $dateFrom;
    public $dateTo;
    public $method;
    public $hideEmpty;
    public $selectedTeasers = array();
    public $selectedPlatforms = array();
    public $correction;
    public $corrected = 0;
    public $correctedCount = 0;
    public $error;

    private $report;
    private $reportGrouped;
    private $dateRange;
    private $dateRangeCount;
    private $data;
    private $dataCount;

    public function __construct(Campaigns $campaign)
    {
        $this->campaign = $campaign;
    }

    public function set($counter, $dateFrom, $dateTo, $correction, $method = '', $hideEmpty = 0, $selectedTeaserId = null, $selectedPlatformId = null){
        $this->counter = $counter;
        list($this->dateFrom, $this->dateTo) = DateHelper::parseDate($dateFrom, $dateTo, $this->campaign->date_start, $this->campaign->date_end);
        $this->correction = $correction;
        $this->hideEmpty = $hideEmpty;
        $this->setSelectedTeasers($selectedTeaserId);
        $this->setSelectedPlatforms($selectedPlatformId);
        $this->setMethod($method);
    }

    public function getDataCount(){
        if(!isset($this->dataCount)){
            $this->dataCount = count($this->getData());
        }
        return $this->dataCount;
    }

    /**
     * Расчитывает таблицу корректировки по заданным настройкам
     *
     * @return array
     */
    public function getData()
    {
        if(!isset($this->data)) {
            $report = $this->getReport();
            $correction = $this->correction;
            $result = array();

            if ($this->method != 'period' && $correction < 0) {
                $this->error = 'Отрицательная корректировка возможна только в режиме за период.';
            } elseif ($report['total'][$this->counter] == 0 && $correction < 0) {
                $this->error = 'Нет данных за период. Отрицательная корректировка не возможна.';
            } else {
                if ($this->method == 'period') {
                    $result = $this->methodPeriod();
                } elseif ($this->method == 'campaign') {
                    $result = $this->methodCampaign();
                } elseif ($this->method == 'simple') {
                    $result = $this->methodSimple();
                }

                if ($this->hideEmpty) {
                    foreach ($result as $pos => $resultRow) {
                        if ($resultRow['correction'] == 0) {
                            unset($result[$pos]);
                        }
                    }
                }
            }

            $this->data = $result;
        }
        return $this->data;
    }

    /**
     * Простой метод расчета. Данные по кампании не учитываются.
     *
     * @return array
     */
    private function methodSimple()
    {
        $result = array();
        $correction = $this->correction;
        if (!empty($this->selectedTeasers)) {
            $teasers = array();
            foreach ($this->selectedTeasers as $teaserId) {
                /** @var Teasers[] $teasers */
                $teasers[] = Teasers::model()->findByPK($teaserId);
            }
        } else {
            foreach ($this->campaign->news as $news) {
                foreach ($news->teasers as $teaser) {
                    $teasers[] = $teaser;
                }
            }
        }
        //preload
        $teaserPlatformsIds = array();
        $platforms = array();
        $count = 0;
        foreach ($teasers as $teaser) {
            if (!isset($teaserPlatformsIds[$teaser->id])) {
                $teaserPlatformsIds[$teaser->id] = Platforms::model()->getAllActiveByTeaserId($teaser->id, true);
            }
            if (!empty($this->selectedPlatforms)) {
                foreach ($teaserPlatformsIds[$teaser->id] as $pos => $platformId) {
                    if (!in_array($platformId, $this->selectedPlatforms)) {
                        unset($teaserPlatformsIds[$teaser->id][$pos]);
                    }
                }
            }
            foreach ($teaserPlatformsIds[$teaser->id] as $platformId) {
                if (!isset($platforms[$platformId])) {
                    $platforms[$platformId] = Platforms::model()->findByPk($platformId);
                }
                $count++;
            }
        }
        //math
        $count = $count * $this->getDateRangeCount();
        if ($correction > $this->maxRows && $count > $this->maxRows) {
            $this->error = 'Слишком много данных. Ограничьте фильтры.';
        } else {
            if ($count > $this->maxRows) {
                $this->error = 'Слишком много данных. Пустые строки не отображаются.';
                $this->hideEmpty = true;
            }
            foreach ($this->getDateRange() as $date) {
                foreach ($teasers as $teaser) {
                    foreach ($teaserPlatformsIds[$teaser->id] as $platformId) {
                        $resultRow = array(
                            'date' => $date->format('Y-m-d'),
                            'news_id' => $teaser->news_id,
                            'teaser_id' => $teaser->id,
                            'platform_id' => $platformId,
                            'teaser_title' => $teaser->title,
                            'platform_server' => $platforms[$platformId]->server,
                            'correction' => $this->calcCorrection($correction, $count)
                        );

                        $result[] = $resultRow;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Пропорциональный метод расчета по кампании.
     * Показатели корректировки расчитываются пропорционально существующим данным по кампании в целом.
     *
     * @return array
     */
    private function methodCampaign()
    {
        $result = array();
        $correction = $this->correction;
        $reportGrouped = $this->getReport(true);
        $dateCount = $this->getDateRangeCount();
        foreach ($this->getDateRange() as $date) {
            $dailyCorrection = round($correction / $dateCount);
            $dailyTotal = $reportGrouped['total'];
            foreach ($reportGrouped['rows'] as $teaserId => $teaser) {
                foreach ($teaser['platforms'] as $platformId => $row) {
                    $resultRow = array(
                        'date' => $date->format('Y-m-d'),
                        'platform_server' => $row['platform_server'],
                        'platform_id' => $platformId,
                    );
                    if($this->counter == 'actions') {
                        $resultRow = array_merge($resultRow, array(
                            'action_name' => $teaser['action_name'],
                            'action_id' => $teaserId,
                        ));
                    }else{
                        $resultRow = array_merge($resultRow, array(
                            'teaser_title' => $teaser['teaser_title'],
                            'teaser_id' => $teaserId,
                            'news_id' => $teaser['news_id'],
                        ));
                    }

                    if ($dailyCorrection != 0) {
                        $resultRow['correction'] = $this->calcCorrection(
                            $dailyCorrection,
                            $dailyTotal[$this->counter],
                            $row[$this->counter]
                        );
                        $correction -= $resultRow['correction'];
                    } else {
                        $resultRow['correction'] = 0;
                    }
                    $result[] = $resultRow;
                }
            }
            $dateCount--;
        }
        return $result;
    }

    /**
     * Пропорциональный метод расчета за период.
     * Показатели корректировки расчитываются пропорционально существующим данным за период кампании.
     *
     * @return array
     */
    private function methodPeriod()
    {
        $result = array();
        $correction = $this->correction;
        $report = $this->getReport();
        if ($report['total'][$this->counter] + $correction < 0) {
            $correction = -$report['total'][$this->counter];
        }

        foreach ($report['rows'] as $row) {
            $resultRow = $row;
            if ($correction != 0) {
                $resultRow['correction'] = $this->calcCorrection(
                    $correction,
                    $report['total'][$this->counter],
                    $row[$this->counter]
                );
            } else {
                $resultRow['correction'] = 0;
            }
            $result[] = $resultRow;
        }

        return $result;
    }

    /**
     * Расчитывает корректировку и обновляет счетчики.
     *
     * @param $correction оставшаяся необходимая корректировка
     * @param $total всего данных в отчете, или счетчик если counter = null
     * @param null $counter текущие показатели отчета
     * @return int
     */
    private function calcCorrection(&$correction, &$total, $counter = null)
    {

        if($counter !== null ){
            $result = (int) round($counter * ($correction / $total));
            $total -= $counter;
        }else{
            $result = (int) round($correction / $total);
            $total--;
        }
        if($result != 0){
            $correction -= $result;
            $this->corrected += $result;
            $this->correctedCount++;
        }
        return $result;
    }

    /**
     * Сохраняет корректировки
     *
     * @param $data
     * @throws CDbException
     * @throws CException
     */
    public function adjust($data)
    {
        $minTime = PHP_INT_MAX;
        $maxTime = 0;
        $totalCorrection = 0;
        $sumByDateAndPlatform = array();
        foreach($data as $row){
            if($row['value'] != 0){
                $time = strtotime($row['date']);
                $minTime = min($minTime, $time);
                $maxTime = max($maxTime, $time);
                if (!isset($sumByDateAndPlatform[$row['date']][$row['platform_id']])) {
                    $sumByDateAndPlatform[$row['date']][$row['platform_id']] = 0;
                }
                $sumByDateAndPlatform[$row['date']][$row['platform_id']] += $row['value'];
                $totalCorrection += $row['value'];

                foreach(ReportHandler::$reports as $report){
                    $report::model()->addCounter(
                        $this->counter,
                        array_merge(
                            array_diff_key($row, array('value' => '')),
                            array(
                                'campaign_id' => $this->campaign->id
                            )
                        ),
                        $row['value'],
                        false
                    );
                }
            }
        }

        // отдельно расчитываем пропорции для гео, потому как они хранятся отдельно
        foreach ($this->getForGeoProportions('city_id', $sumByDateAndPlatform, $minTime, $maxTime) as $row) {
            ReportDailyByCampaignAndPlatformAndCity::model()->addCounter(
                $this->counter,
                array_diff_key($row, array('correction' => '')),
                $row['correction']
            );
        }
        foreach ($this->getForGeoProportions('country_code', $sumByDateAndPlatform, $minTime, $maxTime) as $row) {
            ReportDailyByCampaignAndPlatformAndCountry::model()->addCounter(
                $this->counter,
                array_diff_key($row, array('correction' => '')),
                $row['correction']
            );
        }

        $sql = ReportHandler::createUpdateCounterSql($this->counter, true);
        $sql[] = Campaigns::createUpdateSql($this->campaign->id, $this->counter, $totalCorrection);
        foreach($sql as $query){
            try {
                Yii::app()->getDb()->createCommand($query)->execute();
            }catch (CDbException $e){
            }
        }
    }

    /**
     * @param string $key
     * @param $byDateAndPlatform
     * @param $minDate
     * @param $maxDate
     * @return array
     * @throws CException
     */
    public function getForGeoProportions($key = 'city_id', $byDateAndPlatform, $minDate, $maxDate)
    {
        if($key == 'city_id') {
            $report = ReportDailyByCampaignAndPlatformAndCity::model()->getForCorrection($this->campaign->id, date('Y-m-d', $minDate), date('Y-m-d', $maxDate));
        }else{
            $report = ReportDailyByCampaignAndPlatformAndCountry::model()->getForCorrection($this->campaign->id, date('Y-m-d', $minDate), date('Y-m-d', $maxDate));
        }
        $result = array();
        foreach($byDateAndPlatform as $date => $platforms){
            foreach($platforms as $platformId => $correction) {
                $resultRow = array(
                    'date' => $date,
                    'campaign_id' => $this->campaign->id,
                    'platform_id' => $platformId,
                );
                if($correction < 0) {
                    foreach ($report[$date][$platformId][$key] as $keyId => $stats) {
                        if($stats[$this->counter] != 0) {
                            $resultRow[$key] = $keyId;
                            $resultRow['correction'] = round(
                                $stats[$this->counter] * ($correction / $report[$date][$platformId][$this->counter])
                            );
                            $report[$date][$platformId][$this->counter] -= $stats[$this->counter];
                            if($resultRow['correction'] != 0) {
                                $correction -= $resultRow['correction'];
                                $result[] = $resultRow;
                            }
                        }
                    }
                    if($correction != 0){
                        throw new CException('Cant recalculate proportions for geo. Not corrected: '.$correction);
                    }
                }else{
                    $resultRow[$key] = $this->geoDefaults[$key];
                    $resultRow['correction'] = $correction;
                    $result[] = $resultRow;
                }
            }
        }
        return $result;
    }

    /**
     * Доступные методы расчета изменений
     *
     * @return array
     */
    public function getAvailableMethods()
    {
        return array(
            'period' => 'Пропорциональный (период)',
            'campaign' => 'Пропорциональный (кампания)',
            'simple' => 'Простой',
        );
    }


    /**
     * Поля присутвующие в расчетных данных. Используется для формирования таблицы
     *
     * @return array
     */
    public function getAvailableDataFields()
    {
        $row = $this->getData();
        $row = reset($row);
        return array_intersect_key($this->dataFields, $row);
    }

    /**
     * Доступные для изменения счетчики
     *
     * @return array
     */
    public function getAvailableCounters()
    {
        $result = array(
            'clicks' => 'Переходы',
            'shows' => 'Показы',
        );
        if($this->campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $result['actions'] = 'Действия';
        }
        return $result;
    }

    /**
     * Возможные ключи для формирования таблицы корректировки
     *
     * @return array
     */
    public function getAvailableKeys()
    {
        return array('date', 'news_id', 'teaser_id', 'platform_id', 'action_id');
    }

    /**
     * Доступные для расчетов платформы
     *
     * @return Platforms[]
     */
    public function getAvailablePlatforms($withExternals = true)
    {
        $criteria = new CDbCriteria();
        $criteria->addInCondition('id', Platforms::model()->getAllActiveByCampaignId($this->campaign->id, $withExternals, false));
        $criteria->order = 'server ASC';
        return Platforms::model()->findAll($criteria);
    }

    /**
     * Доступные для расчетов тизеры
     *
     * @return Teasers[]
     */
    public function getAvailableTeasers()
    {
        return Teasers::model()
            ->with('news:notDeleted')
            ->notDeleted()
            ->findAll('news.campaign_id = :campaign_id', array(':campaign_id' => $this->campaign->id));
    }

    private function getDateRange()
    {
        if(!isset($this->dateRange)) {
            $dateFrom = new DateTime($this->dateFrom);
            $dateTo = new DateTime($this->dateTo);
            $dateTo->modify('+1 day');
            $dateInt = new DateInterval('P1D');
            $this->dateRange = new DatePeriod($dateFrom, $dateInt, $dateTo);
        }
        return $this->dateRange;
    }

    private function getDateRangeCount()
    {
        if(!isset($this->dateRangeCount)){
            $this->dateRangeCount = iterator_count($this->getDateRange());
        }
        return $this->dateRangeCount;
    }

    /**
     * Возвращает отчеты для расчета пропорциональной корректировки по кампании или периоду
     *
     * @param bool $isGrouped
     * @return array
     */
    private function getReport($isGrouped = false){
        if($isGrouped){
            if(!isset($this->reportGrouped)) {
                if($this->counter == 'actions') {
                    $this->reportGrouped = ReportDailyByCampaignAndPlatformAndAction::model()->getForCorrection(
                        $this->campaign->id,
                        $this->campaign->date_start,
                        $this->campaign->date_end,
                        $this->selectedPlatforms,
                        true
                    );
                }else{
                    $this->reportGrouped = ReportDailyByTeaserAndPlatform::model()->getForCorrection(
                        $this->campaign->id,
                        $this->campaign->date_start,
                        $this->campaign->date_end,
                        $this->selectedTeasers,
                        $this->selectedPlatforms,
                        true
                    );
                }
            }
            return $this->reportGrouped;
        }else{
            if(!isset($this->report)) {
                if($this->counter == 'actions') {
                    $this->report = ReportDailyByCampaignAndPlatformAndAction::model()->getForCorrection(
                        $this->campaign->id,
                        $this->dateFrom,
                        $this->dateTo,
                        $this->selectedPlatforms
                    );
                }else{
                    $this->report = ReportDailyByTeaserAndPlatform::model()->getForCorrection(
                        $this->campaign->id,
                        $this->dateFrom,
                        $this->dateTo,
                        $this->selectedTeasers,
                        $this->selectedPlatforms
                    );
                }
            }
            return $this->report;
        }
    }

    /**
     * @param string $method
     */
    public function setMethod($method){
        if(empty($method)){
            $report = $this->getReport();
            if($this->correction < 0 || $report['total'][$this->counter] != 0){
                $this->method = 'period';
            }else{
                $reportGrouped = $this->getReport(true);
                if ($reportGrouped['total'][$this->counter] != 0) {
                    $this->method = 'campaign';
                } else {
                    $this->method = 'simple';
                }
            }
        }else{
            $this->method = $method;
        }
    }

    /**
     * @param array|int|Teasers $teasers
     */
    public function setSelectedTeasers($teasers)
    {
        if(!empty($teasers)) {
            if (!is_array($teasers)) {
                $teasers = array($teasers);
            }
            foreach ($teasers as $teaser) {
                if ($teaser instanceof Teaser) {
                    $this->selectedTeasers[] = $teaser->id;
                } else {
                    $teaser = (int) $teaser;
                    if($teaser > 0){
                        $this->selectedTeasers[] = $teaser;
                    }
                }
            }
        }
    }

    /**
     * @param array|int|Platforms $Platforms
     */
    public function setSelectedPlatforms($Platforms)
    {
        if(!empty($Platforms)) {
            if($Platforms == self::PLATFORMS_EXCLUDE_EXTERNAL){
                $Platforms = $this->getAvailablePlatforms(false);
            }
            if (!is_array($Platforms)) {
                $Platforms = array($Platforms);
            }
            foreach ($Platforms as $platform) {
                if ($platform instanceof Platforms) {
                    $this->selectedPlatforms[] = $platform->id;
                } else {
                    $platform = (int) $platform;
                    if($platform > 0){
                        $this->selectedPlatforms[] = $platform;
                    }
                }
            }
        }
    }
}