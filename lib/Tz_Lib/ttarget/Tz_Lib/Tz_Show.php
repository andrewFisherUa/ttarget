<?php
class Tz_Show{
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
		ob_start();
		if(!$this){
			die();
		}
		self::autoloadInit();
		//==================== START LOGIC
		//get IP
		$ip = Tz_Server::getIP();
		//get GEO
		$geo = Tz_Geo::getGeo($ip);
	
		if($geo){
				//get SERVER
				$platform = Tz_Server::getRefererHost();

				//get_key
				$key = crc32($platform) . '_' . crc32($geo['city']);
				//get teasers Array
				$teasers = Tz_Memcached::connection()->getTeasers($key, $platform, $geo['city']);

				$teasers_count = count($teasers);
				//get teasers array key
				$teasers_key = Tz_Memcached::connection()->getTeasersArrayKey($key);
				//get count teasers
				$showed_teasers_count = Tz_Server::getCountTeasers();
				
				$out_teasers = array();
				
				$next_teasers_key = $teasers_key;
				//var_dump($teasers);
				if($showed_teasers_count >= count($teasers)){
					$out_teasers = $teasers;
				} else {
					if(($teasers_key + $showed_teasers_count + 1) > count($teasers)){
						for($i = $teasers_key; $i < $teasers_count;$i++){
							$out_teasers[] = $teasers[$i];
						}
						$next_teasers_key = $showed_teasers_count - count($out_teasers);
						for($i = 0; $i < $next_teasers_key; $i++){
							$out_teasers[] = $teasers[$i];
						}
					} else {
						$next_teasers_key = $teasers_key + $showed_teasers_count;
						for($i = $teasers_key; $i < $next_teasers_key; $i++){
							$out_teasers[] = $teasers[$i];
						}
					}
				}
				
				$content = '';
				if($out_teasers){
					foreach ($out_teasers as $teaser){
						$html = Tz_Memcached::connection()->getTeaserHTML($teaser);
						if($html){
							$news_id = Tz_Memcached::connection()->getTeaserNewsId($teaser);
							if($news_id){
								$platform_id = Tz_Memcached::connection()->getPlatformId($platform);
								if($platform_id){
									Tz_Memcached::connection()->setNewsShowed($news_id, $platform_id);
								}
							}
							$content .= $html;
						}
					}
					if($next_teasers_key != $teasers_key){
						Tz_Memcached::connection()->setTeaserKey($key, $next_teasers_key);
					}
					echo $content;
				}
				
		}
		//==================== END LOGIC
		$content = ob_get_contents();
 		ob_end_clean();
 		self::output($content);
	}
	
	private function autoloadInit(){
		spl_autoload_extensions(".php");
		spl_autoload_register('Tz_Show::autoloader');
	}
	
	private function output($content){
		echo "document.write('",str_replace(array("\\", "\n", "'"), array("\\\\","\\n", "\\'"), $content)."');";
	}

	public function autoloader($class_name){
		include(__DIR__ . DIRECTORY_SEPARATOR . str_replace("\\" , "/", $class_name) . ".php");
	}
}