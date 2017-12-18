<?php

/**
 * Класс для работы с данными новости в редис
 */
class RedisNews extends RedisAbstract
{
    /**
     * @param string $class
     *
     * @return RedisNews
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }
}