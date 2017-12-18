<?php
/**
 * This file part of RResque
 *
 * Autoloader for Resque library
 *
 * For license and full copyright information please see main package file
 * @package       yii-resque
 */
class RResqueAutoloader
{
    private static $lib_path = '';

    /**
     * Registers Raven_Autoloader as an SPL autoloader.
     * @param $path
     */
    static public function register($path)
    {
        self::$lib_path = $path . '/lib/';
        Yii::registerAutoloader(array(new self,'autoload'),true);
    }

    /**
     * Handles autoloading of classes.
     *
     * @param  string  $class  A class name.
     *
     * @return boolean Returns true if the class has been loaded
     */
    static public function autoload($class)
    {
        if (is_file($file = self::$lib_path.str_replace(array('_', "\0"), array('/', ''), $class).'.php')) {
            require $file;
            return true;
        } else if (is_file($file = self::$lib_path.str_replace(array('\\', "\0"), array('/', ''), $class).'.php')) {
            require $file;
            return true;
        }

        return false;
    }
}