<?php
class Tz_Cron{
	private static $_instance;
	private static $is_system = false;
	private static $flush = false;
	
	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}
	
	public static function set_system() {
		self::$is_system = true;
	}
	public static function set_flush() {
		self::$flush = true;
	}
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
		set_time_limit(0);
		self::autoloadInit();
		
		//==================== START LOGIC
		$news = Tz_Memcached::connection()->getNews();
		self::output('Found: ' . count($news) );
		
		if($news){
			$time_limit = time() - 10;
			$clicks_update = array();
			$add = array();
			$clicks = array();
			foreach ($news as $element){
				//show
				self::output('News #' . $element . ':');
				$value = Tz_Memcached::connection()->getNewsShowed($element);
				
				if($value){
					Tz_Memcached::connection()->clearNewsShowed($element);
					$c = 0;
					foreach ($value as $platform_id=>$show){
						Tz_DB::connection()->addNewsShowed($element, $platform_id, $show);
						$c+=$show;
					}
					self::output(' . . . added ' . $c . ' shows;');
				}
				//clicks
				$value = Tz_Memcached::connection()->getNewsClicks($element);
				
				if($value){
					$value = json_decode('['.$value.']');
					Tz_Memcached::connection()->clearNewsClicks($element);
					$cid = Tz_Memcached::connection()->getCampaignId($element);
					$news_ips = array();
					foreach ($value as $click){
						if(isset($news_ips[$click[1]])){
							$news_ips[$click[1]][] = array('ip'=>$click[1],'time' => $click[0], 'platform' => $click[2], 'news' => $element, 'is_real' => 1, 'campaign' => $cid);
						} else {
							$news_ips[$click[1]] = array(array('ip'=>$click[1],'time' => $click[0], 'platform' => $click[2], 'news' => $element, 'is_real' => 1, 'campaign' => $cid));
						}
					}
					
					foreach ($news_ips as $k => $ip){
						if(count($ip) > 10){
							$time = array();
							foreach ($ip as $key => $val){
								$time[$key] = $val['time'];
							}
							array_multisort($time, SORT_ASC, $ip);
							$c = count($ip);
							$failed = false;
							
							for($i = 10;$i++;$i<$c){
								if(($ip[$i]['time'] - $ip[$i-10]['time']) < 10){
									$failed = true;
									break;
								}
							}
							if($failed){
								foreach ($ip as $kip => $val){
									$ip[$kip]['is_real'] = 0;
								}
							};
						}
						
						foreach ($ip as $key => $val){
							$add[] = $val;
						}
					}
				}
			}
			
			$clicks_count = 0;
			$last10 = array();
			
			if($add){
				foreach ($add as $data){
						if($data['time'] < $time_limit){
							Tz_DB::connection()->addNewsClick($data['news'], array($data['time'], $data['ip'], $data['platform']), $data['is_real']);
							$clicks_count++;
							if($data['is_real'] == 1){
								if(isset($clicks_update[$data['campaign']])){
									$clicks_update[$data['campaign']]++;
								} else {
									$clicks_update[$data['campaign']] = 1;
								}
							}
						} else {
							if(isset($last10[$data['news']])){
								$last10[$data['news']][] = array($data['time'], $data['ip'], $data['platform']);
							} else {
								$last10[$data['news']] = array(array($data['time'], $data['ip'], $data['platform']));
							}
						}
				}
			}
			
			if($last10){
				foreach ($last10 as $news => $last)	{
					Tz_Memcached::connection()->setNewsLastClick($news, $last);
				}
			}
			
			if($clicks_update){
				foreach ($clicks_update as $campaign_id => $count){
					$no_flush = Tz_DB::connection()->addCampaignClicks($campaign_id, $count);
					if(!$no_flush){
						self::$flush = true;
					}
				}
			}

			self::output(' . . . added ' . $clicks_count . ' clicks;');
		}
		
		Tz_DB::connection()->setCronUpdate(self::$is_system);
		
		
		if(self::$flush){
			Tz_Memcached::connection()->safeFlush();
			echo "Flushed";
		}
		//==================== END LOGIC
	}
	
	private function autoloadInit(){
		spl_autoload_extensions(".php");
		spl_autoload_register('Tz_Cron::autoloader');
	}
	
	private function output($text){
		echo $text . "\n";
	}

	public function autoloader($class_name){
		include(__DIR__ . DIRECTORY_SEPARATOR . str_replace("\\" , "/", $class_name) . ".php");
	}
}