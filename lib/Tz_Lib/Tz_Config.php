<?php
	
class Tz_Config{
	protected static $_instance;
	protected static $_config;
	
	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}
	
	public static function data() {
	    if (null === self::$_instance) {
	        self::$_instance = new self();
	        $config = parse_ini_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.ini') or die('Can\'t read config');
	        self::$_config = $config;
	    }
	    return self::$_instance;
	}
	
	public function get($key, $default = null){
		return isset(self::$_config[$key])?self::$_config[$key]:$default;
	}
	
	
}
	