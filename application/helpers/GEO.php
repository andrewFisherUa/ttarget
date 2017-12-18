<?php


class GEO {
    /**
     * @param Countries[] $countries
     * @param Cities[] $cities
     * @param bool $addDefaultCountry
     * @return array
     */
    public static function getIds($countries, $cities, $addDefaultCountry = false){
        $countryCodes = array();
        foreach($countries as $country){
            $countryCodes[] = $country->code;
        }
        $cityIds = array();
        foreach($cities as $city){
            $cityIds[] = $city->id;
        }
        return array($countryCodes, $cityIds);
    }

    public static function getStringByName($countryName, $cityName)
    {

        if(empty($countryName)) {
            $result = 'Страна не определена';
        }else{
            $result = $countryName;
            if(!empty($cityName)){
                $result .= ': ' . $cityName;
            }
        }
        return $result;
    }


} 