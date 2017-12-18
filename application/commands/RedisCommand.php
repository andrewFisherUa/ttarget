<?php

/**
 * Добавляет в редис все активные новости и тизеры
 */
class RedisCommand extends CConsoleCommand
{
    public function actionLoadScripts($show = false)
    {
        $this->redis()->script('flush');
        $sha = array();
        $sha['WeightedRandom.lua'] = $this->redis()->script('load', file_get_contents(Yii::app()->basePath
            . DIRECTORY_SEPARATOR . 'lua'
            . DIRECTORY_SEPARATOR . 'WeightedRandom.lua')
        );
        $sha['PagesMatch.lua'] = $this->redis()->script('load', file_get_contents(Yii::app()->basePath
                . DIRECTORY_SEPARATOR . 'lua'
                . DIRECTORY_SEPARATOR . 'PagesMatch.lua')
        );
        if($show){
            var_dump($sha);
        }
    }

    public function actionUpdateWeights()
    {
        $campaigns = Campaigns::model()->notDeleted()->active()->with('activeNews:activeTeasers')->findAll();
        foreach ($campaigns as $campaign) {
            RedisCampaign::instance()->setCampaignWeight(
                $campaign->id,
                RedisCampaign::instance()->calcWeight($campaign)
            );
        }
    }

    public function actionCheckIntegrity($teaserId = null)
    {
        if($teaserId !== null){
            $teaser = Teasers::model()->with('news')->findByPk($teaserId);
            $this->checkTeaserIntegrity($teaser);
        }else{
            $campaigns = Campaigns::model()->notDeleted()->active()->with('activeNews:activeTeasers')->findAll();
            foreach ($campaigns as $campaign) {
                foreach ($campaign->activeNews as $news) {
                    foreach ($news->activeTeasers as $teaser) {
                        $this->checkTeaserIntegrity($teaser);
                    }
                }
            }
        }
    }

    public function actionCheckIntegrityNew($teaserId = null)
    {
        if($teaserId !== null){
            $teaser = Teasers::model()->with('news')->findByPk($teaserId);
            $this->checkTeaserIntegrityNew($teaser);
        }else{
            $campaigns = Campaigns::model()->notDeleted()->active()->with(array(
                'activeNews' => array(
                    'together' => false,
                    'with' => array(
                        'activeTeasers' => array(
                            'together' => false,
                            'with' => array(
                                'news' => array(
                                    'together' => true
                                )
                            )
                        )
                    )
                )
            ))->findAll();
            foreach ($campaigns as $campaign) {
                foreach ($campaign->activeNews as $news) {
                    foreach ($news->activeTeasers as $teaser) {
                        $this->checkTeaserIntegrityNew($teaser);
                    }
                }
            }
        }
    }

    private function checkTeaserIntegrity(Teasers $teaser)
    {
        $platforms = Platforms::model()->getAllActiveByTeaserId($teaser->id);
        $cities     = Cities::model()->getAllByCampaignId($teaser->news->campaign_id);
        $countries  = Countries::model()->getAllCodesCampaignId($teaser->news->campaign_id);
        $errors = array();
        foreach($platforms as $platformId){
            $s = $this->redis()->zScore(RedisPlatform::instance()->getTeasersKey($platformId, $teaser->news_id), $teaser->id);
            if($s === false){
                $errors['teasers'][$teaser->id][$platformId][]=$teaser->news_id;
            }
            foreach($cities as $cityId){
                $s = $this->redis()->zScore(RedisPlatform::instance()->getCitiesNewsKey($platformId, $cityId), $teaser->news_id);
                if($s === false){
                    $errors['news'][$teaser->news_id][$platformId]['cities'][] = $cityId;
                }
            }
            foreach($countries as $countryCode){
                $s = $this->redis()->zScore(RedisPlatform::instance()->getCountriesNewsKey($platformId, $countryCode), $teaser->news_id);
                if($s === false){
                    $errors['news'][$teaser->news_id][$platformId]['countries'][] = $countryCode;
                }
            }
        }

        if(isset($errors['teasers'])){
            foreach($errors['teasers'] as $teaserId => $platforms){
                echo date('Y-m-d H:i:s').'Teaser '.$teaserId.' missing in platform:news:teasears. Platforms(news): ';
                foreach($platforms as $platformId => $news){
                    echo $platformId.'('.implode(', ', $news).'),';
                }
                echo "\n";
            }
        }
        if(isset($errors['news'])){
            foreach($errors['news'] as $newsId => $platforms){
                echo date('Y-m-d H:i:s').'News '.$newsId.' missing in platform:city:news. Platform(Cities, Countries): ';
                foreach($platforms as $platformId => $geo){
                    echo $platformId.'(('.implode(', ', $geo['cities']).', ('.implode(', ', $geo['countries']).')),';
                }
                echo "\n";
            }
        }
    }

    private function checkTeaserIntegrityNew(Teasers $teaser)
    {
        $platforms = Platforms::model()->getAllActiveByTeaserId($teaser->id);
        if(empty($platforms)){
            return;
        }
        $cities     = Cities::model()->getAllByCampaignId($teaser->news->campaign_id);
        $countries  = Countries::model()->getAllCodesCampaignId($teaser->news->campaign_id);
        $errors = array();

        foreach($platforms as $platformId){
            $s = $this->redis()->sIsMember(RedisPlatform::instance()->getCampaignTeasersKey($platformId, $teaser->news->campaign_id), $teaser->id);
            if($s === false){
                $errors['teasers'][$teaser->id][$platformId][]=$teaser->news->campaign_id;
            }
            foreach($cities as $cityId){
                $s = $this->redis()->sIsMember(RedisPlatform::instance()->getCitiesCampaignsKey($platformId, $cityId), $teaser->news->campaign_id);
                if($s === false){
                    $errors['campaigns'][$teaser->news->campaign_id][$platformId]['cities'][] = $cityId;
                }
            }
            foreach($countries as $countryCode){
                $s = $this->redis()->sIsMember(RedisPlatform::instance()->getCountriesCampaignsKey($platformId, $countryCode), $teaser->news->campaign_id);
                if($s === false){
                    $errors['campaigns'][$teaser->news->campaign_id][$platformId]['countries'][] = $countryCode;
                }
            }
        }

        if(isset($errors['teasers'])){
            foreach($errors['teasers'] as $teaserId => $platforms){
                echo date('Y-m-d H:i:s').' Teaser '.$teaserId.' missing in platform:campaigns:teasears. Platforms(campaigns): ';
                foreach($platforms as $platformId => $campaignId){
                    echo $platformId.'('.implode(', ', $campaignId).'),';
                }
                echo "\n";
            }
        }

        if(isset($errors['campaigns'])){
            foreach($errors['campaigns'] as $campaignId => $platforms){
                echo date('Y-m-d H:i:s').' Campaign '.$campaignId.' missing in platform:(city|country):campaigns. Platform(Cities, Countries): ';
                foreach($platforms as $platformId => $geo){
                    echo $platformId.'(('.implode(', ', $geo['cities']).'), ('.implode(', ', $geo['countries']).')),';
                }
                echo "\n";
            }
        }

        if(false === $this->redis()->zScore(RedisCampaign::KEY_CAMPAIGN_WEIGHT, $teaser->news->campaign_id)){
            echo date('Y-m-d H:i:s')." Campaign ".$teaser->news->campaign_id." Score missing in ttarget:campaigns\n";
        }
        if(false === $this->redis()->exists(RedisTeaser::instance()->getScoreKey($teaser->id))){
            echo date('Y-m-d H:i:s')." Teaser ".$teaser->id." Score missing in ttarget:tesers:x:score\n";
        }

    }

    public function actionDeploy($refreshOnly = 0)
    {
        Yii::app()->db->schema->getTables();
        Yii::app()->db->schema->refresh();
        if(!$refreshOnly){
            RedisPlatform::instance()->deleteAll();
            RedisTeaser::instance()->delAllOutpuStr();
            $this->actionLoadScripts();
            $this->actionWarm();
            $this->actionAddLinks();
            $this->actionAddShortLinks();
            $this->actionAddActions();
            $this->actionAddTracks();
            $this->actionAddPages();
        }
    }

    public function actionAddActions(){
        $actions = CampaignsActions::model()->notDeleted()->findAll();
        $this->redis()->multi(Redis::PIPELINE);
        foreach($actions as $action){
            RedisAction::instance()->addAction($action);
        }
        $this->redis()->exec();
    }

    public function actionAddTracks(){
        RedisTrack::instance()->setSequence(Tracks::model()->getSequence());
        $tracks = Tracks::model()->notDeleted()->findAll();
        $this->redis()->multi(Redis::PIPELINE);
        foreach($tracks as $track){
            RedisTrack::instance()->addTrack($track);
        }
        $this->redis()->exec();
    }

    public function actionAddPages(){

        $this->redis()->multi(Redis::PIPELINE);
        RedisPages::instance()->deleteAll();
        foreach(Pages::model()->findAll() as $page){
            RedisPages::instance()->addPage($page);
        }
        $this->redis()->exec();
    }


    public function actionWarm()
    {
        $this->addPlatformsToRedis();
        $campaigns = Campaigns::model()->notDeleted()->active()
            ->with(array('activeNews:activeTeasers' => array('together' => false)))
            ->findAll();
        foreach ($campaigns as $campaign) {
            Yii::app()->resque->createJob('app', 'CampaignAddToRedisJob', array('campaign_id' => $campaign->id));
            Yii::app()->resque->enqueueJobAt(strtotime($campaign->date_end . ' 23:59:59') + 1, 'app', 'CampaignHandleLimitJob', array(
                'campaign_id' => $campaign->id,
            ));
            foreach ($campaign->activeNews as $news) {
                foreach ($news->activeTeasers as $teaser) {
                    Yii::app()->resque->createJob('app', 'TeaserAddToRedisJob', array('teaser_id' => $teaser->id));
                }
            }
        }
    }

    /**
     * Добавляет ссылки не удаленных тизеров в редис
     */
    public function actionAddLinks()
    {
        $teasers = Teasers::model()->notDeleted()->findAll(array('order' => 'id ASC'));
        foreach ($teasers as $teaser) {
            RedisTeaser::instance()->addLink($teaser);
        }
    }

    /**
     * Добавляет короткие ссылки в редис
     */
    public function actionAddShortLinks()
    {
        $links = ShortLink::model()->findAll();
        foreach ($links as $link) {
            RedisShortLink::instance()->set($link);
        }
    }

    /**
     * Удаляет ключи по показам с нулевыми значениями
     *
     * Выполняется по крону раз в час
     */
    public function actionDelShowsKeys()
    {
        $keys = $this->redis()->keys(ShowsJob::KEY_SHOWS);
        foreach ($keys as $key) {

            $this->redis()->watch('x');

            $result = $this->redis()->get($key);
            if ($result === '0') {
                $this->redis()->multi();
                $this->redis()->del($key);
                $this->redis()->exec();
            }

            $this->redis()->unwatch();
        }
    }

    /**
     * Добавляет адреса серверов активных платформ в редис
     */
    private function addPlatformsToRedis()
    {
        $platforms = Platforms::model()->notDeleted()->active()->findAll();
        $addPlatformJob = new PlatformAddToRedisJob();

        foreach ($platforms as $platform) {
            $addPlatformJob->setHosts($platform);
            $addPlatformJob->addEncryptedId($platform);
        }
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}