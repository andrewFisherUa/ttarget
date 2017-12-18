<?php

/**
 * Синхронизирует состояние offer в redis
 */
class OfferSyncToRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('offer_id' => Offers::$id)
     *
     */
    public $args = array();

    /**
     * @var Offers
     */
    private $offer;

    public function perform()
    {
        if (!$this->canPerform()) {
            return;
        }

        $offer = $this->getOffer();

        SyncManager::syncRelated($offer);

        if($offer->isActive() && !$offer->isLimitExceeded(true)){
            RedisLimit::instance()->del($offer);
            list($countries, $cities) = GEO::getIds($offer->countries, $offer->cities);
            RedisOffer::instance()->addOffer($offer);
        }else{
            $countries = array();
            $cities = array();
            RedisOffer::instance()->delOffer($offer);
        }

        $this->syncCountries($offer, $countries);
        $this->syncCities($offer, $cities);
    }

    private function syncCountries($offer, $countries){
        if(empty($countries)){
            RedisOffer::instance()->delCountries($offer);
        }else {
            $redisCountries = RedisOffer::instance()->getCountries($offer);
            RedisOffer::instance()->delCountries($offer, array_diff($redisCountries, $countries));
            RedisOffer::instance()->addCountries($offer, array_diff($countries, $redisCountries));
        }
    }

    private function syncCities($offer, $cities){
        if(empty($cities)){
            RedisOffer::instance()->delCities($offer);
        }else{
            $redisCities = RedisOffer::instance()->getCities($offer);
            RedisOffer::instance()->delCities($offer, array_diff($redisCities, $cities));
            RedisOffer::instance()->addCities($offer, array_diff($cities, $redisCities));
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return $this->getOffer() !== null;
    }

    private function getOffer()
    {
        if (!isset($this->offer)) {
            if (!isset($this->args['offer_id'])) {
                return null;
            }
            $this->offer = Offers::model()->with('countries', 'cities')->findByPk($this->args['offer_id']);
        }

        return $this->offer;
    }
}