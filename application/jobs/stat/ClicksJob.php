<?php

/**
 * Фиксирует клики
 */
class ClicksJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'platform_id'   => Platforms::$id
     *              'teaser_id'     => Teasers::$id
     *              'remote_addr'   => '0.0.0.0',
     *              'timestamp'     => unix_timestamp
     *            )
     */
    public $args = array();

    public function perform()
    {
        if (!$this->validateArgs()) {
            return;
        }

        $platform = Platforms::getById($this->args['platform_id']);
        $teaser   = Teasers::getById($this->args['teaser_id']);
        $news     = News::getById($teaser['news_id']);
        $campaign = Campaigns::getById($news['campaign_id']);

        if(isset($this->args['track_id'])){
            $track = Tracks::getById($this->args['track_id']);
        }

        $counter = 'clicks';
        if($campaign['bounce_check'] != "" && isset($this->args['bc'])){
            $counter = 'bounces';
        }

        ReportHandler::addCounter(
            $counter,
            array(
                'teaser_id' => $teaser['id'],
                'news_id' => $news['id'],
                'campaign_id' => $news['campaign_id'],
                'platform_id' => $platform['id'],
                'city_id' => isset($this->args['city_id']) ? $this->args['city_id'] : 0,
                'country_code' => isset($this->args['country_code']) ? $this->args['country_code'] : 'ZZ',
            ),
            1
        );
        $sql = ReportHandler::createUpdateCounterSql($counter);

//        $sql .= News::createUpdateClicksSql($news['id'], 1, $platform['is_external']);
        $sql .= Campaigns::createUpdateSql($news['campaign_id'], $counter, 1, $platform['is_external']);

        // Добавляем данные трэк-кода в БД (для востановления и ссылок)
        if(isset($track)){
            $sql .= Tracks::createSql(array(
                'id' => $track['id'],
                'campaign_id' => $track['campaign_id'],
                'platform_id' => $track['platform_id'],
                'teaser_id'   => $track['teaser_id'],
                'created_date' => date('Y-m-d H:i:s', $this->args['timestamp']),
                'bounce_check' => isset($track['bounce_check']) ? $track['bounce_check'] : null
            ));
        }

        // Отправляем все завпросы одной пачкой
        Yii::app()->mysqli->multiQuery($sql);
        Yii::app()->mysqli->client()->close();

        if ($counter == 'clicks' && !$teaser['is_external']) {
            $this->updateScore($teaser);
        }
        if(!isset($this->args['bc'])) {
            IpLog::model()->add($this->args['remote_addr'], $this->args['timestamp'], $news['id'], $platform['id']);
        }
        BounceLog::add($news['campaign_id'], $this->args['timestamp'], isset($this->args['bc']));
        if($counter == 'clicks' && $campaign['cost_type'] == Campaigns::COST_TYPE_CLICK){
            $c = Campaigns::model()->findByPk($news['campaign_id']);
            $c->updateWeight();
            $c->handleLimit();
            $this->campaignCheckNotify($c);
        }
    }

    /**
     * @param Campaigns $campaign
     */
    private function campaignCheckNotify($campaign)
    {
        if($campaign->max_clicks > 0 && !$campaign->is_notified && ($campaign->max_clicks - Yii::app()->params->CampaignNotifyClicksLeft) <= $campaign->clicks){
            $campaign->notify('CampaignNotifyClicksLeft');
        }
    }

    private function updateScore($teaser)
    {
        $data = RedisTeaser::instance()->getTeaserScore($teaser['id'], array('shows', 'tagsCount'));
        RedisTeaser::instance()->setTeaserScore(
            $teaser['id'],
            array(
                'score' =>
                    RedisTeaser::instance()->calcScore(
                        $data['shows'],
                        RedisTeaser::instance()->incrTeaserScore($teaser['id'], 'clicks', 1),
                        $data['tagsCount']
                    )
            )
        );
    }


    /**
     * собирает рефереров для платформ которые пришли по ссылке с заданой платформой.
     */
    private function populatePlatform()
    {
        file_put_contents(Yii::app()->getRuntimePath().'/clicks_'.$this->args['platform_id'].'.log', '"'.implode('";"', $this->args).'"'."\n",FILE_APPEND|LOCK_EX);
        if(isset($this->args['referer'])){
            $populated = Yii::app()->cache->get(sprintf('ttarget:palform:%s:populate', $this->args['platform_id']));
            if(preg_match('#(https?://)?(www\\.)?([a-z0-9-\\.]+\\.[a-z]{2,4})#', $this->args['referer'], $match)){
                if(!$populated){
                    $populated = array();
                }
                $populated[$match[3]] = 1;
                Yii::app()->cache->set(sprintf('ttarget:palform:%s:populate', $this->args['platform_id']), $populated);
                file_put_contents(Yii::app()->getRuntimePath().'/platforms_'.$this->args['platform_id'].'.log', implode("\n", array_keys($populated)),LOCK_EX);
            }
        }
    }

    /**
     * @return bool Валидирует переданные параметры
     */
    private function validateArgs()
    {
        if(
            isset($this->args['platform_id']) &&
            isset($this->args['teaser_id']) &&
            isset($this->args['timestamp']) &&
            isset($this->args['remote_addr'])
        ){
            return true;
        }else{
            throw new CException('Not valid args: '.print_r($this->args, true));
        }
    }
}