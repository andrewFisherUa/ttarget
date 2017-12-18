<?php

/**
 * Класс для работы с данными тизера в редис
 */
class RedisTeaser extends RedisAbstract
{
    /**
     * key-value для верстки тизера
     */
    const KEY_HTML = 'ttarget:teasers:{teaser_id}:html';

    /**
     * hash для весов тизера
     */
    const KEY_SCORE = 'ttarget:teasers:{teaser_id}:score';

    /**
     * hash короткой ссылка тизера
     *      содержит {id, url}
     */
    const KEY_LINK = 'ttarget:teasers:link:{link}';

    /** количество показов тизера, до которого реальный ctr будет иметь малое значение в расчете веса */
    const TEASER_BONUS_SHOWS = 10000;

    /**
     * @param string $class
     *
     * @return RedisTeaser
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * Возващает ключ html-верстки тизера
     *
     * @param int $teaserId
     *
     * @return string
     */
    public function getHtmlKey($teaserId)
    {
        return str_replace(
            '{teaser_id}',
            $teaserId,
            self::KEY_HTML
        );
    }

    /**
     * Возващает ключ весов тизера
     *
     * @param int $teaserId
     *
     * @return string
     */
    public function getScoreKey($teaserId)
    {
        return str_replace(
            '{teaser_id}',
            $teaserId,
            self::KEY_SCORE
        );
    }

    /**
     * Возвращает ключ хэша коротких ссылок
     *
     * @param string $link
     *
     * @return string
     */
    public function getLinkKey($link)
    {
        return str_replace('{link}', $link, self::KEY_LINK);
    }

    /**
     * Возвращает данные о весах тизера из redis
     *
     * @param int $teaserId
     * @param string|array $keys
     * @return int
     */
    public function getTeaserScore($teaserId, $keys)
    {
        $teaserKey = $this->getScoreKey($teaserId);
        return $this->redis()->hMGet($teaserKey, (array) $keys);
    }

    /**
     * Увеличивает и/или возвращает данные о весах тизера из redis
     *
     * @param int $teaserId
     * @param string $scoreKey
     * @param int $increment
     * @return int
     */
    public function incrTeaserScore($teaserId, $scoreKey = 'score', $increment = 0)
    {
        $teaserKey = $this->getScoreKey($teaserId);
        if($increment != 0){
            return $this->redis()->hIncrBy($teaserKey, $scoreKey, $increment);
        }else{
            return $this->redis()->hGet($teaserKey, $scoreKey);
        }
    }

    /**
     * Устанавливает данные о весах тизера
     *
     * @param int $teaserId
     * @param array $teaserStats
     */
    public function setTeaserScore($teaserId, array $teaserStats)
    {
        if(!isset($teaserStats['score'])){
            $teaserStats['score'] = $this->calcScore($teaserStats['shows'], $teaserStats['clicks'], $teaserStats['tagsCount']);
        }
        $key = $this->getScoreKey($teaserId);
        $this->redis()->hMset($key, $teaserStats);
    }

    /**
     * Расчитывает вес тизера
     *
     * @param int $shows
     * @param int $clicks
     * @param int $tagsCount
     * @return int
     */
    public function calcScore($shows, $clicks, $tagsCount)
    {
        $rel = 1+1/($tagsCount > 0 ? $tagsCount : 1);

        if($shows < self::TEASER_BONUS_SHOWS){
            $m = 100 / self::TEASER_BONUS_SHOWS;
            $ctr = ($clicks * $m) + ((self::TEASER_BONUS_SHOWS - $shows) * $m);
        }else{
            $ctr = ($clicks == 0 ? 1 : $clicks)  * 100 / $shows;
        }

        return (int) ($rel * $ctr * 1000);
    }

    /**
     * Добавляет html-вывод тизера в редис по ключу HTML_KEY
     *
     * @param Teasers $teaser
     *
     * @return mixed
     */
    public function setOutputStr(Teasers $teaser)
    {
        $key = $this->getHtmlKey($teaser->id);
        $this->redis()->set($key, $teaser->render());
    }

    /**
     * Удаляет html-вывод тизера в редис по ключу HTML_KEY
     *
     * @param Teasers $teaser
     *
     * @return mixed
     */
    public function delOutputStr(Teasers $teaser)
    {
        $key = $this->getHtmlKey($teaser->id);
        return $this->redis()->del($key);
    }

    /**
     * Удаляет несколько html-выводов тизеров из редис
     *
     * @param array $teasers
     *
     * @return mixed
     */
    public function delAllOutpuStr(array $teasers = null)
    {
        $keys = array();
        if(is_null($teasers)){
            $keys = $this->redis()->keys($this->getHtmlKey('*', '*'));
        }else{
            foreach ($teasers as $teaser)
            {
                $keys[] = $this->getHtmlKey($teaser->id);
            }
        }
        return $this->redis()->del($keys);
    }

    /**
     * Добавляет короткую ссылку тизера
     *
     * @param Teasers $teaser
     *
     * @return bool
     */
    public function addLink(Teasers $teaser)
    {
        $key = $this->getLinkKey($teaser->getEncryptedLink());
        $this->redis()->hMset($key, array(
            'id'    => $teaser->id,
            'url'   => $teaser->news->buildUrl(array('utm_term' => $teaser->id)),
            'campaign_id' => $teaser->news->campaign_id
        ));
    }

    /**
     * Удаляет короткую ссылку тизера
     *
     * @param Teasers $teaser
     *
     * @return bool
     */
    public function delLink(Teasers $teaser)
    {
        $key = $this->getLinkKey($teaser->getEncryptedLink());
        $this->redis()->delete($key);
    }
}