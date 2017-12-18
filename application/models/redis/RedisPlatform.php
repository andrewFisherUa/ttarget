<?php

/**
 * Класс для работы с данными платформы в редис
 */
class RedisPlatform extends RedisAbstract
{
    /**
     * sorted-set для хранения хостов площадки, в качестве веса выступает id площадки
     */
    const KEY_HOSTS         = 'ttarget:platforms:hosts';

    /**
     * ветка для хранения закодированных идентификаторов площадки
     */
    const KEY_ENCRYPTED     = 'ttarget:platforms:encrypted';

    /**
     * sorted-set новостей для площадки и страны, в качестве веса выступает кол-во показов новости
     * @deprecated by new teaser rotation
     */
    const KEY_COUNTRIES_NEWS   = 'ttarget:platforms:{platform_id}:countries:{code}:news';

    /**
     * set кампаний для площадки и страны
     */
    const KEY_COUNTRIES_CAMPAIGNS   = 'ttarget:platforms:{platform_id}:countries:{code}:campaigns';

    /**
     * sorted-set новостей для площадки и города, в качестве веса выступает кол-во показов новости
     * @deprecated by new teaser rotation
     */
    const KEY_CITIES_NEWS   = 'ttarget:platforms:{platform_id}:cities:{city_id}:news';

    /**
     * базовый set кампаний для площадки и города
     */
    const KEY_CITIES_CAMPAIGNS   = 'ttarget:platforms:{platform_id}:cities:{city_id}:campaigns';

    /**
     * sorted-set тизеров новостей для площадки, в качестве весы используется кол-во показов тизера
     * @deprecated by new teaser rotation
     */
    const KEY_NEWS_TEASERS  = 'ttarget:platforms:{platform_id}:news:{news_id}:teasers';

    /**
     * set тизеров кампании для площадки
     */
    const KEY_CAMPAIGN_TEASERS  = 'ttarget:platforms:{platform_id}:campaigns:{campaign_id}:teasers';

    /**
     * set идентификторов площадки, с которых приходили запросы блока
     */
    const KEY_PLATFORMS_REQUESTS = 'ttarget:platforms_requests';


    /**
     * @param string $class
     *
     * @return RedisPlatform
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * @return string Возвращает ключ списка хостов платформ
     */
    public function getHostsKey()
    {
        return self::KEY_HOSTS;
    }

    /**
     * @return string Возвращает ключ хеша id->encryptedId или encryptedId->id платформ
     */
    public function getEncryptedKey()
    {
        return self::KEY_ENCRYPTED;
    }

    /**
     * Возвращает ключ списка новостей для платформы и страны
     *
     * @param int    $platformId
     * @param string $code
     *
     * @return string
     * @deprecated by new teaser rotation
     */
    public function getCountriesNewsKey($platformId, $code)
    {
        return str_replace(
            array('{platform_id}', '{code}'),
            array($platformId, $code),
            self::KEY_COUNTRIES_NEWS
        );
    }

    /**
     * Возвращает ключ списка новостей для платформы и страны
     *
     * @param int    $platformId
     * @param string $code
     *
     * @return string
     */
    public function getCountriesCampaignsKey($platformId, $code)
    {
        return str_replace(
            array('{platform_id}', '{code}'),
            array($platformId, $code),
            self::KEY_COUNTRIES_CAMPAIGNS
        );
    }

    /**
     * Возвращает ключ списка весов кампаний для платформы и страны
     *
     * @param int    $platformId
     * @param string $code
     *
     * @return string
     */
    public function getCountriesCampaignsWeightsKey($platformId, $code)
    {
        return str_replace(
            array('{platform_id}', '{code}'),
            array($platformId, $code),
            self::KEY_COUNTRIES_CAMPAIGNS
        ).':weights';
    }

    /**
     * Возвращает ключ списка новостей для платформы и города
     *
     * @param int $platformId
     * @param int $cityId
     *
     * @return string
     * @deprecated by new teaser rotation
     */
    public function getCitiesNewsKey($platformId, $cityId)
    {
        return str_replace(
            array('{platform_id}', '{city_id}'),
            array($platformId, $cityId),
            self::KEY_CITIES_NEWS
        );
    }

    /**
     * Возвращает ключ списка кампаний для платформы и города
     *
     * @param int $platformId
     * @param int $cityId
     *
     * @return string
     */
    public function getCitiesCampaignsKey($platformId, $cityId)
    {
        return str_replace(
            array('{platform_id}', '{city_id}'),
            array($platformId, $cityId),
            self::KEY_CITIES_CAMPAIGNS
        );
    }

    /**
     * Возвращает ключ списка весов кампаний для платформы и города
     *
     * @param int $platformId
     * @param int $cityId
     *
     * @return string
     */
    public function getCitiesCampaignsWeightsKey($platformId, $cityId)
    {
        return str_replace(
            array('{platform_id}', '{city_id}'),
            array($platformId, $cityId),
            self::KEY_CITIES_CAMPAIGNS
        ).':weights';
    }

    /**
     * Возвращает ключ списка тизеров для новости и платформы
     *
     * @param int $platformId
     * @param int $newsId
     *
     * @return string
     * @deprecated by new teaser rotation
     */
    public function getTeasersKey($platformId, $newsId)
    {
        return str_replace(
            array('{platform_id}', '{news_id}'),
            array($platformId, $newsId),
            self::KEY_NEWS_TEASERS
        );
    }

    /**
     * Возвращает ключ списка тизеров для кампании и платформы
     *
     * @param int $platformId
     * @param int $campaignId
     *
     * @return string
     */
    public function getCampaignTeasersKey($platformId, $campaignId)
    {
        return str_replace(
            array('{platform_id}', '{campaign_id}'),
            array($platformId, $campaignId),
            self::KEY_CAMPAIGN_TEASERS
        );
    }

    /**
     * Возвращает ключ списка тизеров для платформы
     *
     * @param int $platformId
     *
     * @return string
     */
    public function getPlatformTeasersKey($platformId)
    {
        return str_replace(
            '{platform_id}',
            $platformId,
            self::KEY_PLATFORM_TEASERS
        );
    }

    /**
     * Добавляет тизер в sorted set новости и платформы
     *
     * Используется для поиска тизера, отображаемого на определенной платформе
     *
     * @param Teasers $teaser
     * @param array   $platforms
     * @deprecated by new teaser rotation
     */
    public function addTeaserToNewsSets(Teasers $teaser, array $platforms)
    {
        foreach ($platforms as $platformId) {
            $key = $this->getTeasersKey($platformId, $teaser->news_id);
            $this->redis()->zAdd($key, 0, $teaser->id);
        }
    }

    /**
     * Добавляет тизер в sorted set кампании и платформы
     *
     * Используется для поиска тизера, отображаемого на определенной платформе
     *
     * @param Teasers $teaser
     * @param array   $platforms
     */
    public function addTeaserToCampaignSets(Teasers $teaser, array $platforms)
    {
        foreach ($platforms as $platformId) {
            $key = $this->getCampaignTeasersKey($platformId, $teaser->news->campaign_id);
            $this->redis()->sAdd($key, $teaser->id);
        }
    }

    /**
     * Удаляет тизер из sorted set новости и платформ
     *
     * Используется для поиска тизера, отображаемого на определенной платформе
     *
     * @param Teasers $teaser
     * @param array   $platforms
     * @deprecated by new teaser rotation
     */
    public function remTeaserFromNewsSets(Teasers $teaser, array $platforms)
    {
        foreach ($platforms as $platformId) {
            $key = $this->getTeasersKey($platformId, $teaser->news_id);
            $this->redis()->zRem($key, $teaser->id);
        }
    }

    /**
     * Возвращает количество тизеров новости для платформы
     *
     * @param int $news_id
     * @param int $platformId
     *
     * @return int
     * @deprecated by new teaser rotation
     */
    public function countOfNewsTeasers($news_id, $platformId)
    {
        $key = $this->getTeasersKey($platformId, $news_id);
        return (int) $this->redis()->zCard($key);;
    }

    /**
     * Возвращает количество тизеров кампании для платформы
     *
     * @param int $campaignId
     * @param int $platformId
     *
     * @return int
     *
     */
    public function countOfCampaignTeasers($campaignId, $platformId)
    {
        $key = $this->getCampaignTeasersKey($platformId, $campaignId);
        return (int) $this->redis()->sCard($key);
    }

    /**
     * Добавляет новости к городам платформы
     *
     * @param int   $newsId
     * @param int   $platformId
     * @param array $cities
     *
     * @return void
     * @deprecated by new teaser rotation
     */
    public function addNewsToCitiesSets($newsId, $platformId, array $cities)
    {
        foreach ($cities as $cityId)
        {
            $key = $this->getCitiesNewsKey($platformId, $cityId);
            $this->redis()->zAdd($key, 0, $newsId);
        }
    }

    /**
     * Добавляет кампанию к городам платформы
     *
     *
     * @param int   $campaignId
     * @param int   $platformId
     * @param array $cities
     */
    public function addCampaignToCitiesSets($campaignId, $platformId, array $cities)
    {
        foreach ($cities as $cityId)
        {
            $key = $this->getCitiesCampaignsKey($platformId, $cityId);
            $this->redis()->sAdd($key, $campaignId);
        }
    }

    /**
     * Удаляет тизеры из городов платформы
     *
     * @param Teasers   $teaser
     * @param int   $platformId
     *
     * @return void
     */
    public function remTeaserFromCampaignSets($teaser, $platformId)
    {
        $key = $this->getCampaignTeasersKey($platformId, $teaser->news->campaign_id);
        $this->redis()->sRem($key, $teaser->id);
    }

    /**
     * Удаляет новости из городов платформы
     *
     * @param int   $newsId
     * @param int   $platformId
     * @param array $cities
     *
     * @return void
     * @deprecated by new teaser rotation
     */
    public function remNewsFromCitiesSets($newsId, $platformId, array $cities)
    {
        foreach ($cities as $cityId)
        {
            $key = $this->getCitiesNewsKey($platformId, $cityId);
            $this->redis()->zRem($key, $newsId);
        }
    }

    /**
     * Удаляет кампанию из городов платформы
     *
     * @param int   $campaignId
     * @param int   $platformId
     * @param array $cities
     *
     * @return void
     */
    public function remCampaignFromCitiesSets($campaignId, $platformId, array $cities)
    {
        foreach ($cities as $cityId)
        {
            $key = $this->getCitiesCampaignsKey($platformId, $cityId);
            $this->redis()->sRem($key, $campaignId);
        }
    }

    /**
     * Добавляет новости к странам платформы
     *
     * @param int   $newsId
     * @param int   $platformId
     * @param array $countries
     *
     * @return void
     * @deprecated by new teaser rotation
     */
    public function addNewsToCountriesSets($newsId, $platformId, array $countries)
    {
        foreach ($countries as $code)
        {
            $key = $this->getCountriesNewsKey($platformId, $code);
            $this->redis()->zAdd($key, 0, $newsId);
        }

    }

    /**
     * Добавляет кампанию к странам платформы
     *
     * @param int   $campaignId
     * @param int   $platformId
     * @param array $countries
     *
     * @return void
     */
    public function addCampaignToCountriesSets($campaignId, $platformId, array $countries)
    {
        foreach ($countries as $code)
        {
            $key = $this->getCountriesCampaignsKey($platformId, $code);
            $this->redis()->sAdd($key, $campaignId);
        }
    }

    /**
     * Удаляет новости из стран платформы
     *
     * @param int   $newsId
     * @param int   $platformId
     * @param array $countries
     *
     * @return void
     * @deprecated by new teaser rotation
     */
    public function remNewsFromCountriesSets($newsId, $platformId, array $countries)
    {
        foreach ($countries as $code)
        {
            $key = $this->getCountriesNewsKey($platformId, $code);
            $this->redis()->zRem($key, $newsId);
        }
    }

    /**
     * Удаляет новости из стран платформы
     *
     * @param int   $campaignId
     * @param int   $platformId
     * @param array $countries
     *
     * @return void
     */
    public function remCampaignFromCountriesSets($campaignId, $platformId, array $countries)
    {
        foreach ($countries as $code)
        {
            $key = $this->getCountriesCampaignsKey($platformId, $code);
            $this->redis()->sRem($key, $campaignId);
        }
    }

    public function addEncryptedId(Platforms $platform)
    {
        $encrypted = $platform->getEncryptedId();
        $this->redis()->hMset(
            $this->getEncryptedKey(),
            array(
                $platform->id => $encrypted,
                $encrypted => $platform->id
            )
        );
    }

    /**
     * Добавляет хосты платформы в редис
     *
     * @param Platforms $platform
     *
     * @return bool
     */
    public function addHosts(Platforms $platform)
    {
        foreach ($platform->getHostsAsArray() as $url)
        {
            $this->redis()->zAdd($this->getHostsKey(), $platform->id, $url);
        }
    }

    /**
     * Удаляет хосты площадки
     *
     * @param int $platformId
     *
     * @return mixed
     */
    public function delHosts($platformId)
    {
        $this->redis()->zRemRangeByScore($this->getHostsKey(), $platformId, $platformId);
    }

    /**
     * Удаляет все новости привязанные к платформе
     *
     * @param Platforms $platform
     * @deprecated by new teaser rotation
     */
    public function deleteNewsByPlatform(Platforms $platform)
    {
        $pattern    = $this->getCitiesNewsKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);

        $pattern    = $this->getCountriesNewsKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);
    }

    /**
     * Удаляет все кампании привязанные к платформе
     *
     * @param Platforms $platform
     */
    public function deleteCampaignsByPlatform(Platforms $platform)
    {
        $pattern    = $this->getCitiesCampaignsKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);

        $pattern    = $this->getCountriesCampaignsKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);
    }

    /**
     * Удаляет все тизеры, привязанные к платформе
     *
     * @param Platforms $platform
     * @deprecated by new teaser rotation
     */
    public function deleteTeasersByPlatform(Platforms $platform)
    {
        $pattern    = $this->getTeasersKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);
    }

    /**
     * Удаляет все тизеры, привязанные к платформе
     *
     * @param Platforms $platform
     */
    public function deleteCampaignTeasersByPlatform(Platforms $platform)
    {
        $pattern    = $this->getCampaignTeasersKey($platform->id, '*');
        $keys       = $this->redis()->keys($pattern);
        $this->redis()->del($keys);
    }

    /**
     * Удаляет платформу из редис
     *
     * @param Platforms $platform
     */
    public function delete(Platforms $platform)
    {
        $this->delHosts($platform->id);
        $this->deleteNewsByPlatform($platform);
        $this->deleteTeasersByPlatform($platform);
        $this->deleteCampaignsByPlatform($platform);
        $this->deleteCampaignTeasersByPlatform($platform);
    }

    /**
     * Удаляет все платформы из редис
     */
    public function deleteAll()
    {
        $keys = $this->redis()->keys('ttarget:platforms:*');
        $this->redis()->del($keys);
    }

    /**
     * @return int
     * @deprecated временая закладка
     */
    public function getVersion()
    {
        $version = $this->redis()->get('ttarget:version');
        return (int) $version;
    }

    public function setVersion($version)
    {
        return $this->redis()->set('ttarget:version', $version);
    }

    /**
     * Возвращает идентификаторы площадок, с которых были запросы блоков, с момента последнего вызова метода.
     * @return array
     */
    public function getLastRequests()
    {
        $result = $this->redis()->multi()
            ->sMembers(self::KEY_PLATFORMS_REQUESTS)
            ->del(self::KEY_PLATFORMS_REQUESTS)
            ->exec();
        return $result[0];
    }
}