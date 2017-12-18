<?php
class CampaignGoogleAnalytics
{
    const CLIENT_ID = '535559564690-umaiao98gh7fgm42vip3n27vpa4sak9u.apps.googleusercontent.com';
    const CLIENT_SECRET = 'Or1zVuyvgp9JwVKJF4e5IwSv';
    public $redirect_uri = '';
    const CACHE_PREFIX = 'GA_';
    const CACHE_EXPIRE = 300;

    private $client;
    private $clientConfig; //GA config filename
    private $service;
    private $campaign;
    private $dateFrom;
    private $dateTo;

    /**
    *	Set client configuration filename
    **/
    public function setClientConfig($filename)
    {
    	try{
    		if(is_file($filename) && is_readable($filename)){
    			$this->clientConfig = $filename;
    		} else {
    			throw new Exception('Google config file \''.$filename.'\' is not exists or not readable');
    		}
    	} catch(Excepton $e) {
    		Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
    	}
    }
    
    /**
     * Аутентификация полученого кода
     * @param $code
     */
    public function authenticate($code)
    {
        $this->client->authenticate($code);
        $this->updateToken();
    }

    /**
     * Авторизация, с обновлением ключей в случе необходимости. Если ключей нет возвращает ссылку для авторизации.
     *
     * @return string
     */
    public function authorize()
    {
        if ($this->campaign->ga_access_token) {
            $this->client->setAccessToken($this->campaign->ga_access_token);
            if($this->client->isAccessTokenExpired()){
                $oldToken = json_decode($this->client->getAccessToken());
                if(!isset($oldToken->refresh_token)){
                    // refresh token пропал. надо повторить авторизацию
                    $this->reset();
                    return $this->client->createAuthUrl();
                }
                $this->client->refreshToken($oldToken->refresh_token);
                $this->updateToken();
            }
        } else {
            return $this->client->createAuthUrl();
        }
    }

    /**
     * Обновляет токен связанный с кампанией
     */
    private function updateToken()
    {
        $this->campaign->ga_access_token = $this->client->getAccessToken();
        $this->campaign->update(array('ga_access_token'));
    }

    /**
     * обновляет ID представления связанный с кампанией
     * @param $profile
     */
    public function updateProfile($profile)
    {
        $this->campaign->ga_profile_id = $profile;
        $this->campaign->update(array('ga_profile_id'));
    }

    /**
     * Отвязывает кампанию от аккаунта и представления GA
     */
    public function reset()
    {
        $this->campaign->ga_access_token = $this->campaign->ga_profile_id = null;
        $this->campaign->update(array('ga_access_token', 'ga_profile_id'));
    }

    /**
     * Отчет по кампаниям GA (площадки в локальном понимании)
     * @return array
     */
    public function getByCampaigns()
    {
        $result = $this->queryReport(
            'ga:campaign', // для будующего локального кеша можно использовать ga:date
            'ga:sessions,ga:percentNewSessions,ga:newUsers,ga:bounceRate,ga:pageviewsPerSession,ga:avgSessionDuration'
        );

        $platforms = array();
        foreach($result['rows'] as $row){
            $platformId = (int) $row['ga:campaign'];
            if($platformId > 0){
                $platforms[] = $platformId;
            }
        }
        $platforms = Platforms::model()->getServersByIds($platforms);

        return $this->formatResult($result, array('ga:campaign' => $platforms));
    }

    /**
     * Отчет по ключевым словам GA (тизеры в локальном понимании)
     * @return array
     */
    public function getByKeyword()
    {
        $result = $this->queryReport(
            'ga:keyword',
            'ga:pageviews,ga:uniquePageviews,ga:avgTimeOnPage'
        );

        $teasers = array();
        foreach($result['rows'] as $row){
            $teaserId = (int) $row['ga:keyword'];
            if($teaserId > 0){
                $teasers[] = $teaserId;
            }
        }

        $teasers = Teasers::model()->getTitlesByIds($teasers);
        return $this->formatResult($result, array('ga:keyword' => $teasers));
    }

    /**
     * Отчет по странам
     * @return array
     */
    public function getByCountry()
    {
        $result = $this->queryReport(
            'ga:country',
            'ga:sessions,ga:percentNewSessions,ga:newUsers,ga:bounceRate,ga:pageviewsPerSession,ga:avgSessionDuration'
        );

        return $this->formatResult($result);
    }

    /**
     * Отчет по городам
     * @return array
     */
    public function getByCity()
    {
        $result = $this->queryReport(
            'ga:city',
            'ga:sessions,ga:percentNewSessions,ga:newUsers,ga:bounceRate,ga:pageviewsPerSession,ga:avgSessionDuration'
        );

        return $this->formatResult($result);
    }

    public function getTotal($cacheExpire = 0)
    {
        $result = $this->queryReport(
            '',
            'ga:avgSessionDuration,ga:pageviewsPerSession',
            $cacheExpire
        );

        return $this->formatRow($result['total']);
    }
    

    /**
     * Форматирует отчет по типу полей, и заменяет ID из указаных колононок данными
     *
     * @param $result
     * @param array $replace
     * @return array
     */
    private function formatResult($result, $replace = array())
    {
        foreach($result['rows'] as &$row){
            $row = $this->formatRow($row);
            foreach($replace as $replaceKey => $replaceValues){
                if(isset($row[$replaceKey])){
                    $row[$replaceKey] = isset($replaceValues[$row[$replaceKey]]) ?
                        $replaceValues[$row[$replaceKey]] : 'Неизвестный ID: '.$row[$replaceKey];
                }
            }
        }
        $result['total'] = $this->formatRow($result['total']);
        return $result;
    }

    /**
     * Форматирует строку отчета по типу полей
     *
     * @param array $row
     * @return array
     */
    private function formatRow($row)
    {
        foreach($row as $colName => &$col){
            switch($colName){
                case 'ga:percentNewSessions':
                case 'ga:pageviewsPerSession':
                case 'ga:bounceRate':
                    $col = sprintf('%.2f', round($col, 2));
                break;
                case 'ga:avgSessionDuration':
                case 'ga:avgTimeOnPage':
                    $col = $this->formatTime($col);
                break;
            }
        }
        return $row;
    }

    private function formatTime($seconds)
    {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours*3600)) / 60);
        $secs = floor($seconds % 60);
        return sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
    }

    /**
     * Общий метод для запроса отчета от GA или из кеша.
     * @param $dimensions
     * @param $metrics
     * @param int $cacheExpire
     * @return array
     */
    private function queryReport($dimensions, $metrics, $cacheExpire = 0)
    {
        $_cacheExpire = $cacheExpire ? $cacheExpire : self::CACHE_EXPIRE;
        
        $cacheKey = $this->getCacheKey($dimensions, $metrics);
        $cached = Yii::app()->cache->get($cacheKey);
        if($cached){
            return json_decode($cached, true);
        }

        if($this->client->getAccessToken()){
//            try{
                $result = $this->service->data_ga->get(
                    'ga:' . $this->campaign->ga_profile_id,
                    $this->dateFrom,
                    $this->dateTo,
                    $metrics,
                    array(
                        'segment' => 'dynamic::ga:source==Ttarget',
                        'filters' => 'ga:medium=='.$this->campaign->id,
                        'dimensions' => $dimensions,
                        'max-results' => 10000,
                    )
                );
//            } catch (Google_Service_Exception $e) {
//                throw new Exception("Query failed.", 0 ,$e);
//            }
            $result = $this->getResultAssoc($result);
            Yii::app()->cache->set($cacheKey, json_encode($result), $_cacheExpire);
//            Yii::app()->cache->set($cacheKey, json_encode($result, JSON_UNESCAPED_UNICODE), self::CACHE_EXPIRE);
            return $result;
        }
    }

    /**
     * Получает список доступных представлений для связанного аккаунта
     * @return array
     */
    public function getAvailableProfiles()
    {
        $result = array();
        $accounts = $this->service->management_accounts->listManagementAccounts();
        foreach($accounts->getItems() as $account){

            $webProperties = $this->service->management_webproperties
                ->listManagementWebproperties($account->getId());
            foreach($webProperties->getItems() as $webProperty){

                $profiles = $this->service->management_profiles
                    ->listManagementProfiles($account->getId(), $webProperty->getId(), array('quotaUser' => uniqid('', true)));
                foreach($profiles->getItems() as $profile){
                    $result[$profile->getId()] = $account->getName() . ' / ' . $webProperty->getName() . ' / ' . $profile->getName();
                }
            }
        }
        return $result;
    }

    /**
     * Ключ для хранение отчета в кеше
     * @param $dimensions
     * @param $metrics
     * @return string
     */
    private function getCacheKey($dimensions, $metrics)
    {
        return self::CACHE_PREFIX . $this->campaign->ga_profile_id
            . '_' .$dimensions
            . '_' .$metrics
            . '_' . $this->dateFrom
            . '_' . $this->dateTo;
    }


    /**
     * Преобразует отчет GA в массив
     * @param Google_Service_Analytics_GaData $result
     * @return array
     */
    public function getResultAssoc(Google_Service_Analytics_GaData $result)
    {
        $report = array(
            'rows' => array()
        );

        $rows = $result->getRows();
        if(count($rows) > 0){
            foreach($result->getRows() as $rowIdx => $row){
                /** @var Google_Service_Analytics_GaDataColumnHeaders $column */
                foreach($result->getColumnHeaders() as $colIdx => $column){
                    $report['rows'][$rowIdx][$column->getName()] = $row[$colIdx];
                }
            }
        }
        $report['total'] = $result->getTotalsForAllResults();
        return $report;
    }

    /**
     * Устаналивает диапазон дат для отчетов
     * @param null $dateFrom
     * @param null $dateTo
     */
    public function setDateRange($dateFrom = null, $dateTo = null){
        $this->dateFrom = $dateFrom == null ? $this->campaign->date_start : $dateFrom;
        $this->dateTo = $dateTo == null ? $this->campaign->date_end : $dateTo;
    }

    
    
    public function __construct(Campaigns $campaign, $dateFrom = null, $dateTo = null, $clientConfig = null)
    {
        $this->campaign = $campaign;
        $this->setDateRange($dateFrom, $dateTo);
        if($clientConfig) {
            $this->setClientConfig($clientConfig);
        }

        require_once Yii::getPathOfAlias('application.extensions.Google.autoload') . '.php';

        $this->client = !empty($this->clientConfig) ? new Google_Client($this->clientConfig) : new Google_Client();
        $this->client->setClientId(self::CLIENT_ID);
        $this->client->setClientSecret(self::CLIENT_SECRET);
        $this->client->setRedirectUri(empty($this->redirect_uri) ? Yii::app()->controller->createAbsoluteUrl('') : $this->redirect_uri);
        $this->client->setScopes(array(Google_Service_Analytics::ANALYTICS_READONLY));
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');
        $this->client->setState($campaign->id);
        $this->service = new Google_Service_Analytics($this->client);
    }
}