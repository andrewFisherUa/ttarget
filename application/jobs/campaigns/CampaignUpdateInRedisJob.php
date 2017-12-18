<?php

/**
 * Обновляет данные кампании в редис
 */
class CampaignUpdateInRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'campaign_id' => Campaigns::$id,
     *              'clean_attributes'      => array('attribute' => 'value'),
     *              'added_cities'          => array(Cities::$id)
     *              'excepted_cities'       => array(Cities::$id)
     *              'added_countries'       => array(Countries::$id)
     *              'excepted_countries'    => array(Countries::$id)
     *            )
     */
    public $args = array();

    /**
     * @var Campaigns
     */
    private $campaign;

    public function perform()
    {
        if ($this->canPerform()) {
//            $this->processGeo();
            $this->addCache();
            $this->updateScore();
            $campaignPlatforms = Platforms::model()->getAllActiveByCampaignId($this->getCampaign()->id);
            $this->updateCampaignCities($campaignPlatforms);
            $this->updateCampaignCountries($campaignPlatforms);
            foreach($this->getCampaign()->activeNews as $news){
                $platforms = Platforms::model()->getAllActiveByNewsId($news->id);
                if ($platforms) {
                    $this->updateNewsCities($platforms, $news);
                    $this->updateNewsCountries($platforms, $news);
                }
            }
        }
    }

    /**
     * Обновляем вес для кампании, если изменились лимиты кликов или даты.
     */
    public function updateScore()
    {
        if($this->args['update_score']){
            $this->getCampaign()->updateWeight();
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($campaign = $this->getCampaign()) &&
        $campaign->checkIsActive($campaign->id);
    }

    /**
     * Возвращает объект кампании по переданному идентификатору
     *
     * @return Campaigns
     */
    private function getCampaign()
    {
        if (!isset($this->campaign)) {

            if (!isset($this->args['campaign_id'])) {
                return null;
            }

            $this->campaign = Campaigns::model()->findByPk($this->args['campaign_id']);
        }

        return $this->campaign;
    }


    /**
     * Обновляет список городов, в которых показывается РК
     *
     * @param array $platforms
     */
    private function updateCampaignCities($platforms)
    {
        if(empty($this->args['excepted_cities']) && empty($this->args['added_cities'])){
            return ;
        }

        $this->redis()->multi(Redis::PIPELINE);
        if (!empty($this->args['excepted_cities'])) {
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->remCampaignFromCitiesSets(
                    $this->getCampaign()->id,
                    $platformId,
                    $this->args['excepted_cities']
                );
            }
        }

        if (!empty($this->args['added_cities'])) {
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->addCampaignToCitiesSets(
                    $this->getCampaign()->id,
                    $platformId,
                    $this->args['added_cities']
                );
            }
        }
        $this->redis()->exec();
    }

    /**
     * Обновляет список городов, в которых показывается новость,
     * если при изменении новости не менялся сегмент
     *
     * @param array $platforms
     * @param News $news
     * @deprecated by new teaser rotation
     */
    private function updateNewsCities($platforms, $news)
    {
        $this->remNewsFromCities($news, $platforms);
        $this->addNewsToCities($news, $platforms);
    }

    /**
     * Удаляет связи новости и городов
     *
     * @param News $news
     * @param array $platforms
     * @deprecated by new teaser rotation
     */
    private function remNewsFromCities(News $news, array $platforms)
    {
        if (!empty($this->args['excepted_cities'])) {
            $this->redis()->multi(Redis::PIPELINE);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->remNewsFromCitiesSets($news->id, $platformId, $this->args['excepted_cities']);
            }
            $this->redis()->exec();
        }
    }

    /**
     * Добавляет связи новости и городов
     *
     * @param News $news
     * @param array $platforms
     * @deprecated by new teaser rotation
     */
    private function addNewsToCities(News $news, array $platforms)
    {
        if (!empty($this->args['added_cities'])) {
            $this->redis()->multi(Redis::PIPELINE);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->addNewsToCitiesSets($news->id, $platformId, $this->args['added_cities']);
            }
            $this->redis()->exec();
        }
    }

    /**
     * Обновляет список стран, в которых показывается РК
     *
     * @param array $platforms
     */
    private function updateCampaignCountries($platforms)
    {
        if(empty($this->args['excepted_countries']) && empty($this->args['added_countries'])){
            return;
        }

        $this->redis()->multi(Redis::PIPELINE);
        if (!empty($this->args['excepted_countries'])) {
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->remCampaignFromCountriesSets(
                    $this->getCampaign()->id,
                    $platformId,
                    $this->args['excepted_countries']
                );
            }
        }
        if (!empty($this->args['added_countries'])) {
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->addCampaignToCountriesSets(
                    $this->getCampaign()->id,
                    $platformId,
                    $this->args['added_countries']
                );
            }
        }
        $this->redis()->exec();
    }

    /**
     * Обновляет список стран, в которых показывается новость,
     * если при изменении новости не менялся сегмент
     *
     * @param array $platforms
     * @param News $news
     * @deprecated by new teaser rotation
     */
    private function updateNewsCountries($platforms, $news)
    {
        $this->remNewsFromCountries($news, $platforms);
        $this->addNewsToCountries($news, $platforms);
    }

    /**
     * Удаляет связи новости и cтран
     *
     * @param News $news
     * @param array $platforms
     * @deprecated by new teaser rotation
     */
    private function remNewsFromCountries(News $news, array $platforms)
    {
        if (!empty($this->args['excepted_countries'])) {
            $this->redis()->multi(Redis::PIPELINE);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->remNewsFromCountriesSets($news->id, $platformId, $this->args['excepted_countries']);
            }
            $this->redis()->exec();
        }
    }

    /**
     * Добавляет связи новости и стран
     *
     * @param News $news
     * @param array $platforms
     * @deprecated by new teaser rotation
     */
    private function addNewsToCountries(News $news, array $platforms)
    {
        if (!empty($this->args['added_countries'])) {
            $this->redis()->multi(Redis::PIPELINE);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->addNewsToCountriesSets($news->id, $platformId, $this->args['added_countries']);
            }
            $this->redis()->exec();

        }
    }

    /**
     * Сохраняем в редис
     */
    public function addCache()
    {
        RedisCampaign::instance()->setCampaignCache($this->getCampaign());
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}