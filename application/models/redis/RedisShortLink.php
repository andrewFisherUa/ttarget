<?php
/**
 * Класс для работы с данными кампании в редис
 */
class RedisShortLink extends RedisAbstract{
    const KEY_SHORT_LINK = 'ttarget:short_link:{eid}';

    /**
     * @param string $class
     *
     * @return RedisShortLink
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    protected function getShortLinkKey($eid)
    {
        return str_replace('{eid}', $eid, self::KEY_SHORT_LINK);
    }

    public function set(ShortLink $shortLink)
    {
        return $this->redis()->set(
            $this->getShortLinkKey($shortLink->eid),
            $shortLink->url
//            strtotime($shortLink->expire_date) - time()
        );
    }
}