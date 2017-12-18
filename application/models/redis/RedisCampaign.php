<?php
/**
 * Класс для работы с данными кампании в редис
 */
class RedisCampaign extends RedisAbstract{
    const KEY_CAMPAIGN_WEIGHT = 'ttarget:campaigns';
    const KEY_CAMPAIGN_CACHE = 'ttarget:campaigns:{campaign_id}';

    /**
     * @param string $class
     *
     * @return RedisCampaign
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * Устанавливает вес кампании в общем списке
     * @param $campaignId
     * @param $weight
     */
    public function setCampaignWeight($campaignId, $weight)
    {
        $this->redis()->zAdd(self::KEY_CAMPAIGN_WEIGHT, $weight, $campaignId);
    }

    /**
     * Уменьшает показатель веса кампании
     *
     * @param int $campaignId
     * @param int $decrement
     */
    public function decCampaignWeight($campaignId, $decrement)
    {
        $this->redis()->zIncrBy(self::KEY_CAMPAIGN_WEIGHT, -$decrement, $campaignId);
    }

    /**
     * Расчитывает вес кампании
     *
     * @param Campaigns $campaign
     * @return int
     */
    public function calcWeight(Campaigns $campaign){
        if($campaign->day_clicks > 0){
            return (int)ceil( 100 - ($campaign->totalDayDone() / $campaign->day_clicks * 100) );
        }elseif($campaign->max_clicks > 0){
            return (int) ceil(100 - ($campaign->totalDayDone() /
                    (($campaign->max_clicks - $campaign->totalDone()) / ($campaign->getDaysLeft() + 1)) * 100));
        }
        return 0;
    }

    /**
     * Возващает ключ информации о кампании
     *
     * @param int $campaignId
     *
     * @return string
     */
    public function getCampaignCacheKey($campaignId)
    {
        return str_replace(
            '{campaign_id}',
            $campaignId,
            self::KEY_CAMPAIGN_CACHE
        );
    }

    /**
     * Устанавливает кеш кампании
     *
     * @param Campaigns $campaign
     * @return bool
     */
    public function setCampaignCache(Campaigns $campaign)
    {
        return $this->redis()->hMset(
            $this->getCampaignCacheKey($campaign->id),
            array_merge(
                array_diff_key($campaign->getAttributes(),array('track_js' => null)),
                array(
                    'date_end' => strtotime($campaign->date_end . '23:59:59')
                )
            )
        );
    }

    /**
     * Возвращает кеш кампании
     *
     * @param $campaignId
     * @return array
     */
    public function getCampaignCache($campaignId)
    {
        return $this->redis()->hGetAll($this->getCampaignCacheKey($campaignId));
    }
}