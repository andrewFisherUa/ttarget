<?php

/**
 * Класс для работы с отслеживанием пользователей
 */
class RedisSession extends RedisAbstract
{
    /**
     * hash
     */
//    const KEY_TEASER = 'ttarget:user_session:teaser';

    /**
     * set
     */
//    const KEY_GEO = 'ttarget:user_session:geo';

    /**
     * hash
     */
    const KEY_PAGES = 'ttarget:session:pages';

    /**
     * @param string $class
     *
     * @return RedisSession
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * Возвращает данные о изменении гео в сессиях, с момента последнего вызова метода.
     * @return array
     */
//    public function getLastGeo()
//    {
//        $result = $this->redis()->multi()
//            ->sMembers(self::KEY_GEO)
//            ->del(self::KEY_GEO)
//            ->exec();
//
//        foreach($result[0] as $k => $str){
//            $result[0][$k] = explode('-', $str);
//        }
//
//        return $result[0];
//    }

    /**
     * Возвращает данные о переходах, с момента последнего вызова метода.
     * @return array
     */
//    public function getLastTeasers()
//    {
//        $result = $this->redis()->multi()
//            ->hGetAll(self::KEY_TEASER)
//            ->del(self::KEY_TEASER)
//            ->exec();
//
//        $reply = array();
//        foreach($result[0] as $key => $count){
//            $uidTeaser = explode('-', $key);
//            $reply[] = array($uidTeaser[0], $uidTeaser[1], $count);
//        }
//
//        return $reply;
//    }

    /**
     * Возвращает данные о внешних страницах, посещенных с момента последнего вызова метода.
     * @return array
     */
    public function getLastPages()
    {
        $result = $this->redis()->multi()
            ->hGetAll(self::KEY_PAGES)
            ->del(self::KEY_PAGES)
            ->exec();

        $reply = array();
        foreach($result[0] as $key => $count){
            $uidPage = explode('-', $key);
            $reply[] = array($uidPage[0], $uidPage[1], $count);
        }

        return $reply;
    }
}