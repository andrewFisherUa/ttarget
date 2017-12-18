<?php
class Tz_Click{
	protected static $_instance;
	
	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}
	
	public static function app() {
	    if (null === self::$_instance) {
	        self::$_instance = new self();
	    }
	    return self::$_instance;
	}
	
	public function run(){
		if(!$this){
			die();
		}
		self::autoloadInit();
		//==================== START LOGIC
		//get IP
		$ip = Tz_Server::getIP();
		//get GEO
		$geo = Tz_Geo::getGeo($ip);

		//get SERVER
		$platform = null;
		//if(!$platform){
			$get_keys = isset($_GET)?array_keys($_GET):null;
			if($get_keys && isset($get_keys[0])){
				$get_string = Tz_Crypt::decode($get_keys[0]);
				$parts = explode('|', $get_string);
				if(isset($parts[0]) && isset($parts[1]) && ($parts[0] == 'sys')){
					$platform = $parts[1];
				}
			}
		//}
		if(!$platform){
			$platform = Tz_Server::getRefererHost();
		}
	
		if($platform){
			self::log('cicks', $platform);
			$platform_id = Tz_Memcached::connection()->getPlatformId($platform);
			if($platform_id){
				$get_keys = isset($_GET)?array_keys($_GET):null;
				if($get_keys && isset($get_keys[0])){
					self::log('cicks-get', $platform . '---' . $get_keys[0] );
					$get_string = Tz_Crypt::decode($get_keys[0]);
					$parts = explode('|', $get_string);
					if(isset($parts[1])){
						self::log('cicks-ok', $platform . '---' . $get_keys[0] . '----'  .$get_keys[1] . '---' . $platform_id );
						if($parts[0] == 'sys'){
							$parts[1] = $parts[2];
						}
						$url = Tz_Memcached::connection()->getNewsUrl($parts[1]);
						if($url){
							Tz_Memcached::connection()->setNewsClick($parts[1], $ip, $platform_id);
						}
					} else {
						self::log('cicks-no-news', $platform . '---' . $_SERVER['REQUEST_URI']);
					}
						
					
				} else {
					self::log('cicks-no-string', $platform . '---' . $_SERVER['REQUEST_URI']);
				}
			} else {
				self::log('cicks-no-platform', $platform . '---' . $_SERVER['REQUEST_URI']);
				//////////////////////////// FOR TESTS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ////////////////////////////
				$get_keys = isset($_GET)?array_keys($_GET):null;
				if($get_keys && isset($get_keys[0])){
					$get_string = Tz_Crypt::decode($get_keys[0]);
					$parts = explode('|', $get_string);
					if(isset($parts[1])){
						$url = Tz_Memcached::connection()->getNewsUrl($parts[1]);
					}
				}
				////////////////////////////////////////////////////////////////////////////////////////////////////
			}
		} else {
			self::log('cicks-no-referer', var_export($_SERVER, true));
			//////////////////////////// FOR TESTS!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! ////////////////////////////
			$get_keys = isset($_GET)?array_keys($_GET):null;
			if($get_keys && isset($get_keys[0])){
				$get_string = Tz_Crypt::decode($get_keys[0]);
				$parts = explode('|', $get_string);
				if(isset($parts[1])){
					$url = Tz_Memcached::connection()->getNewsUrl($parts[1]);
				}
			}
			////////////////////////////////////////////////////////////////////////////////////////////////////
		}
		//==================== END LOGIC
		if($url){
 			self::output($url);
		}
	}
	
	private function autoloadInit(){
		spl_autoload_extensions(".php");
		spl_autoload_register('Tz_Click::autoloader');
	}
	
	private function output($url){
		print "<html><head><meta http-equiv=\"Refresh\" content=\"0; URL=$url\"></head><body onLoad=\"javascript: window.location='$url';\"></body></html>";
		//header("Location: " . $url, true, 302);
		exit();
	}

	public function autoloader($class_name){
		include(__DIR__ . DIRECTORY_SEPARATOR . str_replace("\\" , "/", $class_name) . ".php");
	}
	
	private function log($file, $data){
		return false;
		$f = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR . $file . '.txt', 'a+');
		fwrite($f, date('Y-m-d H:i:s') . ': ' . $data . ";\n");
		fclose($f);
	}
}