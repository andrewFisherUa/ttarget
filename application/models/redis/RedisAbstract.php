<?php

/**
 * Базовый класс-фабрика для объектов работы с redis
 */
abstract class RedisAbstract
{
    /**
     * @var array Инстансы
     */
    private static $instances = array();

    private function __construct() {}
    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    /**
     * Возвращает инстанс класса
     *
     * @param string $class
     *
     * @return RedisAbstract
     */
    public static function instance($class = __CLASS__)
    {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class;
        }
        return self::$instances[$class];
    }

    /**
     * @return Redis
     */
    public function redis()
    {
        return Yii::app()->redis;
    }
}