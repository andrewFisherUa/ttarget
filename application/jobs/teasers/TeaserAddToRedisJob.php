<?php

/**
 * Добавляет тизер в редис
 */
class TeaserAddToRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('teaser_id' => Teasers::$id)
     *
     */
    public $args = array();

    /**
     * @var Teasers
     */
    private $teaser;

    public function perform()
    {
        if (!$this->canPerform() || $this->getTeaser()->is_external) {
            return;
        }

        $teaser = $this->getTeaser();

        $platforms  = Platforms::model()->getAllActiveByTeaserId($teaser->id);
        if (empty($platforms)) {
            return;
        }

        $cities     = Cities::model()->getAllByCampaignId($teaser->news->campaign_id);
        $countries  = Countries::model()->getAllCodesCampaignId($teaser->news->campaign_id);

        RedisTeaser::instance()->setOutputStr($teaser);
        RedisTeaser::instance()->setTeaserScore($teaser->id, $teaser->getStats());

        $this->addToPlatforms($teaser, $platforms, $cities, $countries);

    }

    /**
     * Создает связь тизера и платформ
     *
     * @param Teasers $teaser
     * @param array $platforms
     * @param array $cities
     * @param array $countries
     */
    public function addToPlatforms(Teasers $teaser, array $platforms, array $cities, array $countries)
    {
        Yii::app()->redis->multi(Redis::PIPELINE);
            RedisPlatform::instance()->addTeaserToNewsSets($teaser, $platforms);
            RedisPlatform::instance()->addTeaserToCampaignSets($teaser, $platforms);
            foreach ($platforms as $platformId) {
                RedisPlatform::instance()->addCampaignToCitiesSets($teaser->news->campaign_id, $platformId, $cities);
                RedisPlatform::instance()->addCampaignToCountriesSets($teaser->news->campaign_id, $platformId, $countries);
                RedisPlatform::instance()->addNewsToCitiesSets($teaser->news_id, $platformId, $cities);
                RedisPlatform::instance()->addNewsToCountriesSets($teaser->news_id, $platformId, $countries);
            }
        Yii::app()->redis->exec();
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        if(
            ($teaser = $this->getTeaser()) &&
            $teaser->is_active &&
            !$teaser->is_deleted
        ) {
            if(News::model()->checkIsActive($teaser->news_id)) {
                return true;
            }elseif(Campaigns::model()->checkIsDailyLimitExceeded($teaser->news->campaign_id)){
                Yii::app()->resque->enqueueJobAt(strtotime('tomorrow'), 'app', 'TeaserAddToRedisJob', array('teaser_id' => $teaser->id));
            }
        }

        return false;
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

            $this->teaser = Teasers::model()->with('news', 'tags')->findByPk($this->args['teaser_id']);
        }

        return $this->teaser;
    }
}