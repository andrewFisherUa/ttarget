<?php

/**
 * Класс для работы с данными оффера в редис
 */
class RedisOffer extends RedisAbstract
{
    /**
     * hash оффер
     */
    const KEY_OFFER = "ttarget:offers:{id}";

    /**
     * set ГЕО страны оффера
     */
    const KEY_COUNTRIES = 'ttarget:offers:{id}:countries';

    /**
     * set ГЕО города оффера
     */
    const KEY_CITIES = 'ttarget:offers:{id}:cities';

    /**
     * hash пользователь-оффер
     */
    const KEY_OFFER_USER = 'ttarget:offers_users:{eid}';

    /**
     * @param string $class
     *
     * @return RedisOffer
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /* Keys */

    public function getOfferKey($id)
    {
        return str_replace('{id}', $id, self::KEY_OFFER);
    }

    public function getOfferUserKey($eid)
    {
        return str_replace('{eid}', $eid, self::KEY_OFFER_USER);
    }

    public function getOfferCountriesKey($id)
    {
        return str_replace('{id}', $id, self::KEY_COUNTRIES);
    }

    public function getOfferCitiesKey($id)
    {
        return str_replace('{id}', $id, self::KEY_CITIES);
    }

    /* Countries */

    public function getCountries(Offers $offer)
    {
        return $this->redis()->sMembers($this->getOfferCountriesKey($offer->id));
    }

    public function addCountries(Offers $offer, $countries)
    {
        if(!empty($countries)) {
            $key = $this->getOfferCountriesKey($offer->id);
            array_unshift($countries, $key);
            call_user_func_array(array($this->redis(), 'sAdd'), $countries);
        }
    }

    public function delCountries(Offers $offer, $countries = null)
    {
        if($countries === null){
            $this->redis()->del($this->getOfferCountriesKey($offer->id));
        }elseif(!empty($countries)) {
            array_unshift($countries, $this->getOfferCountriesKey($offer->id));
            call_user_func_array(array($this->redis(), 'sRem'), $countries);
        }
    }

    /* Cities */

    public function getCities(Offers $offer)
    {
        return $this->redis()->sMembers($this->getOfferCitiesKey($offer->id));
    }

    public function addCities(Offers $offer, $cities)
    {
        if(!empty($cities)) {
            $key = $this->getOfferCitiesKey($offer->id);
            array_unshift($cities, $key);
            call_user_func_array(array($this->redis(), 'sAdd'), $cities);
        }
    }

    public function delCities(Offers $offer, $cities = null)
    {
        if($cities === null){
            $this->redis()->del($this->getOfferCitiesKey($offer->id));
        }elseif(!empty($cities)) {
            array_unshift($cities, $this->getOfferCitiesKey($offer->id));
            call_user_func_array(array($this->redis(), 'sRem'), $cities);
        }
    }

    /* Offers */

    public function addOffer(Offers $offer)
    {
        $this->redis()->hMset(
            $this->getOfferKey($offer->id),
            array(
                'campaign_id' => $offer->campaign_id,
                'action_eid' => $offer->action->getEncryptedId(),
                'cookie_expires' => $offer->cookie_expires,
                'url' => $offer->url . '?{args}',
            )
        );
    }

    public function delOffer(Offers $offer)
    {
        $this->redis()->del($this->getOfferKey($offer->id));
    }

    /* OffersUsers */

    public function addOfferUser(OffersUsers $offerUser)
    {
        $this->redis()->hMset(
            $this->getOfferUserKey($offerUser->getEncryptedId()),
            array(
                'id' => $offerUser->id,
                'offer_id' => $offerUser->offer_id,
//                'platform_id' => $offerUser->user->platforms[0]->id,
    //            'user_id' => $offerUser->user_id,
    //            'action_id' => $offerUser->offer->action_id,
    //            'url' => $offerUser->offer->url,
    //            'campaign_id' =>$offerUser->offer->campaign_id
            )
        );
    }

    public function delOfferUser(OffersUsers $offerUser){
        $this->redis()->del($this->getOfferUserKey($offerUser->getEncryptedId()));
    }
}