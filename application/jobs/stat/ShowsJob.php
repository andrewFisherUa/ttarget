<?php

/**
 * Фиксирует в БД показы новостей
 */
class ShowsJob
{
    /**
     * Шаблон ключа в redis для поиска показов
     * ttarget:shows:{platform_id}:{teaser_id}
     */
    const KEY_SHOWS = 'ttarget:shows:*';

    public function perform()
    {
        $shows = $this->getShowsFromRedis();
        $totalByTeaser = array();
        $totalByCampaign = array();
        $totalByNews = array();
        /** @var Campaigns[] $campaigns */
        $campaigns = array();
        foreach ($shows as $platform_id => $teasers) {
            $platform = Platforms::model()->notDeleted()->findByPk($platform_id);
            if (!$platform) {
                continue;
            }

            /** @var Teasers[] $teasers */
            $teasers = Teasers::model()
                ->notDeleted()
                ->with(array(
                    'news:notDeleted' => array(
                        'with' => 'campaign:notDeleted'
                    )
                ))
                ->findAllByPk(array_keys($teasers));

            foreach($teasers as $teaser){
                $campaigns[$teaser->news->campaign_id] = $teaser->news->campaign;
                if(strtotime($teaser->news->campaign->date_end . '23:59:59') < time()){
                    continue;
                }
                foreach($shows[$platform_id][$teaser->id] as $city_id => $countries){
                    foreach($countries as $country_code => $amount){
                        if(!isset($totalByTeaser[$teaser->id])){
                            $totalByTeaser[$teaser->id] = 0;
                        }
                        $totalByTeaser[$teaser->id] += $amount[$teaser->id];
                        ReportHandler::addCounter(
                            'shows',
                            array(
                                'teaser_id' => $teaser->id,
                                'news_id' => $teaser->news_id,
                                'campaign_id' => $teaser->news->campaign_id,
                                'platform_id' => $platform_id,
                                'city_id' => $city_id,
                                'country_code' => $country_code,
                            ),
                            $amount
                        );

                        if(!isset($totalByNews[$teaser->news->id])){
                            $totalByNews[$teaser->news->id] = 0;
                        }
                        $totalByNews[$teaser->news->id] += $amount;

                        if(!isset($totalByCampaign[$teaser->news->campaign_id])){
                            $totalByCampaign[$teaser->news->campaign_id] = 0;
                        }
                        $totalByCampaign[$teaser->news->campaign_id] += $amount;
                    }
                }
            }
        }
        $sql = ReportHandler::createUpdateCounterSql('shows');
        foreach($totalByCampaign as $campaign_id => $amount){
            $sql .= Campaigns::createUpdateSql($campaign_id, 'shows', $amount);
        }
        foreach($totalByNews as $news_id => $amount){
            $sql .= News::createUpdateShowsSql($news_id, $amount);
        }

        if($sql != ''){
            Yii::app()->mysqli->multiQuery($sql);
            Yii::app()->mysqli->client()->close();
        }

        foreach($totalByTeaser as $teaserId => $amount){
            $this->updateScore($teaserId, $amount);
        }
    }

    private function updateScore($teaserId, $showsAmount)
    {
        $data = RedisTeaser::instance()->getTeaserScore($teaserId, array('clicks', 'shows', 'tagsCount'));
        RedisTeaser::instance()->setTeaserScore(
            $teaserId,
            array(
                'score' =>
                    RedisTeaser::instance()->calcScore(
                        $data['shows'],
                        $data['clicks'],
                        $data['tagsCount']
                    )
            )
        );
    }

    /**
     * Возвращает показы зафиксированные в redis
     *
     * @return array
     * @todo не хорошо использовать keys
     */
    private function getShowsFromRedis()
    {
        $keys = $this->redis()->keys(self::KEY_SHOWS);

        $this->redis()->multi(Redis::PIPELINE);

            foreach ($keys as $key) {
                $this->redis()->getSet($key, 0);
            }

        $result = $this->redis()->exec();

        return $this->combineData($keys, $result);
    }

    /**
     * Объеденяет ключи и показы
     *
     * @param array $keys
     * @param array $shows
     *
     * @return array
     */
    private function combineData($keys, $shows)
    {
        $combined   = array();
        $pattern    = str_replace('*', '', self::KEY_SHOWS);

        foreach ($keys as $index => $key)
        {
            list($platform_id, $teaser_id, $country_code, $city_id) = explode(':', str_replace($pattern, '', $key));

//            if (!isset($combined[$platform_id])) {
//                $combined[$platform_id] = array();
//            }

            $amount = (int) $shows[$index];
            if ($amount > 0) {
                $combined[$platform_id][$teaser_id][$country_code][$city_id] = $amount;
            }
        }

        return $combined;
    }



    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}