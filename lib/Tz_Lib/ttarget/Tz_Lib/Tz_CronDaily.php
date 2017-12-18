<?php
class Tz_CronDaily{
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
		self::autoloadInit();
		//============================================
		shell_exec('php /var/www/cron/index.php');
		Tz_Memcached::connection()->safeFlush();
		//==================== START LOGIC
		$news = Tz_DB::connection()->getAnaliticNews();
		self::output('Found: ' . count($news) );
		
		if($news){
			$current_time = time();
			foreach ($news as $element){
				//get_news_week
				$time = strtotime($element['create_date'] . ' 00:00:00');
				$life_period = floor($current_time - $time);
				$news_weeks = floor($life_period / 604800);
				
				if($news_weeks > 1 && ($element['last_quality_week'] < $news_weeks)){
					$quality = '';
					if($news_weeks > 2){
						$current_period_clicks = Tz_DB::connection()->getClicksByPeriod($element['id'], $element['create_date'], date('Y-m-d', $time + ($news_weeks * 604800)) );
						$previosly_period_clicks = Tz_DB::connection()->getClicksByPeriod($element['id'], $element['create_date'], date('Y-m-d', $time + (($news_weeks-1) * 604800)) );
						if($current_period_clicks >= ($previosly_period_clicks *3)){
							$quality = 'successful';
						} elseif ($current_period_clicks < ($previosly_period_clicks * 2.5)){
							$quality = 'moderated';
						} else {
							$quality = 'working';
						}
						
					} else {
						$current_period_clicks = Tz_DB::connection()->getClicksByPeriod($element['id'], $element['create_date'], date('Y-m-d', $time + ($news_weeks * 604800)) );
						if($current_period_clicks >= 2000){
							$quality = 'successful';
						} elseif ($current_period_clicks < 1000){
							$quality = 'moderated';
						} else {
							$quality = 'working';
						}
					}
					if($quality){						
						Tz_DB::connection()->setQuality($element['id'], $quality, $news_weeks);
						self::output('News #' . $element['id'] . ': '. $quality );
					}
				}
				
					
			}
		}
		Tz_DB::connection()->setDailyUpdate(self::$is_system);
		Tz_DB::connection()->checkActiveCampaigns(self::$is_system);
		/*
		if(self::$flush){
			Tz_Memcached::connection()->flush();
			echo "Flushed";
		}*/
		//==================== END LOGIC
	}
	
	private function autoloadInit(){
		spl_autoload_extensions(".php");
		spl_autoload_register('Tz_CronDaily::autoloader');
	}
	
	private function output($text){
		//header("Moved Temporarily");
		echo $text . "\n";
	}

	public function autoloader($class_name){
		include(__DIR__ . DIRECTORY_SEPARATOR . str_replace("\\" , "/", $class_name) . ".php");
	}
}