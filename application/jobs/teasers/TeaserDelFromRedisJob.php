<?php

/**
 * Удаляет тизер из редис
 */
class TeaserDelFromRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'teaser_id' => Teasers::$id
     *              'platforms' => array(Platforms::$id)
     *              'cities'    => array(Cities::$id)
     *              'countries' => array(Countries::$code)
     *            )
     *
     */
    public $args = array();

    /**
     * @var Teasers
     */
    private $teaser;

    public function perform()
    {
        if (!($teaser = $this->getTeaser()) || $teaser->is_external) {
            return;
        }

        $platforms  = $this->getPlatforms($teaser);
        $cities     = $this->getCities($teaser);
        $countries  = $this->getCountries($teaser);

        if (empty($platforms) || (empty($cities) && empty($countries)))
        {
            return;
        }

        $this->redis()->multi(Redis::PIPELINE);
            RedisPlatform::instance()->remTeaserFromNewsSets($teaser, $platforms);
            RedisTeaser::instance()->delOutputStr($teaser);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->remTeaserFromCampaignSets($teaser, $platformId);
            }
        $this->redis()->exec();
        $this->removeCampaignsIfNoTeasers($teaser->news->campaign_id, $platforms, $cities, $countries);
        $this->removeNewsIfNoTeasers($teaser->news_id, $platforms, $cities, $countries);
    }

    /**
     * Возвращает идентификаторы платформ
     *
     * @param Teasers $teaser
     * @return array
     */
    private function getPlatforms(Teasers $teaser)
    {
        if (isset($this->args['platforms']) && !empty($this->args['platforms'])) {
            return $this->args['platforms'];
        }

        return Platforms::model()->getAllActiveByTeaserId($teaser->id);
    }

    /**
     * Возвращает коды стран
     *
     * @param Teasers $teaser
     * @return array
     */
    private function getCountries(Teasers $teaser)
    {
        if (isset($this->args['countries']) && !empty($this->args['countries'])) {
            return $this->args['countries'];
        }

        return Countries::model()->getAllCodesCampaignId($teaser->news->campaign_id);
    }

    /**
     * Возвращает идентификаторы городов
     *
     * @param Teasers $teaser
     * @return array
     */
    private function getCities(Teasers $teaser)
    {
        if (isset($this->args['cities']) && !empty($this->args['cities'])) {
            return $this->args['cities'];
        }

        return Cities::model()->getAllByCampaignId($teaser->news->campaign_id);
    }

    /**
     * Удаляет связь новости и платформы, если у новости больше нет активных тизеров
     *
     * @param int $news_id
     * @param array $platforms
     * @param array $cities
     * @param array $countries
     */
    public function removeNewsIfNoTeasers($news_id, $platforms, array $cities, array $countries)
    {
        foreach ($platforms as $platformId) {
            if (0 == RedisPlatform::instance()->countOfNewsTeasers($news_id, $platformId)) {
                $this->redis()->multi(Redis::PIPELINE);
                    RedisPlatform::instance()->remNewsFromCitiesSets($news_id, $platformId, $cities);
                    RedisPlatform::instance()->remNewsFromCountriesSets($news_id, $platformId, $countries);
                $this->redis()->exec();
            }
        }
    }

    /**
     * Удаляет связь кампании и платформы, если у новости больше нет активных тизеров
     *
     * @param int $campaign_id
     * @param array $platforms
     * @param array $cities
     * @param array $countries
     */
    public function removeCampaignsIfNoTeasers($campaign_id, $platforms, array $cities, array $countries)
    {
        foreach ($platforms as $platformId) {
            if (0 == RedisPlatform::instance()->countOfCampaignTeasers($campaign_id, $platformId)) {
                $this->redis()->multi(Redis::PIPELINE);
                    RedisPlatform::instance()->remCampaignFromCitiesSets($campaign_id, $platformId, $cities);
                    RedisPlatform::instance()->remCampaignFromCountriesSets($campaign_id, $platformId, $countries);
                $this->redis()->exec();
            }
        }
    }

    /**
     * Возвращает объект тизера по переданному идентификатору
     *
     * @return Teasers
     */
    private function getTeaser()
    {
        if (!isset($this->teaser)) {

            if (!isset($this->args['teaser_id'])) {
                return null;
            }

            $this->teaser = Teasers::model()->findByPk($this->args['teaser_id']);
        }

        return $this->teaser;
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}