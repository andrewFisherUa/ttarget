<?php
	
class Tz_Memcached{
	protected static $_instance;
	protected static $_memcache;
	
	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}
	
	public static function connection() {
	    if (null === self::$_instance) {
	        self::$_instance = new self();
	        self::$_memcache = new Memcached();
	        self::$_memcache->addServer(Tz_Config::data()->get('memcached.host'), Tz_Config::data()->get('memcached.port')) or die ("Could not connect");
			self::$_memcache->setOption(Memcached::OPT_COMPRESSION, false);
	    }
	    return self::$_instance;
	}
	
	public function getPlatformId($platform_name){
		$key = 'p_'.$platform_name;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getPlatformId($platform_name);
			if($value){
				self::set($key, $value);
			}
		}
		
		return $value;
	}
	
	public function getPlatformTag($platform_id){
		$key = 'pt_'.$platform_id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getPlatformTag($platform_id);
			if($value){
				self::set($key, $value);
			}
		}
		
		return $value;
	}
	
	public function getCityId($city_name){
		$key = 'c_'.$city_name;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getCityId($city_name);
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function getTeasersArrayKey($key){
		$key = 'ti_'.$key;
		$value = self::get($key);
		if($value === false){
			$value = 0;
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function getTeaserNewsId($id){
		$key = 'tn_'.$id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getTeaserNewsId($id);
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	
	public function getNewsShowed($key){
		$key = 'ns_'.$key;
		$value = self::get($key);
		return is_array($value)?$value:array();
	}
	
	public function setNewsShowed($key, $platform_id){
		$stored = false;
		$nkey = 'ns_'.$key;
		do {
			    $res = self::getCas($nkey, null, $cas);
				if(!$res[0]){
					$stored = true;
					self::set($nkey, array($platform_id => 1));
				} else {
						if(array_key_exists($platform_id, $res[0])){
							$res[0][$platform_id]++;
						} else {
							$res[0][$platform_id] = 1;
						}
			            $stored = self::cas($res[1], $nkey, $res[0]);
		    	}
		} while (!$stored);
		return;
		
		
		$value = self::getNewsShowed($key);
		$nkey = 'ns_'.$key;
		
		if(array_key_exists($platform_id, $value)){
			$value[$platform_id]++;
		} else {
			$value[$platform_id] = 1;
		}
		self::set($nkey, $value);
		return;
	}
	
	public function fillNewsShowed($key, $values){
		$key = 'ns_'.$key;
		self::set($key, $values);
		return;
	}
	
	public function clearNewsShowed($key){
		$key = 'ns_'.$key;
		self::delete($key);
		return 0;
	}
	
	public function setTeaserKey($key, $value){
		self::set('ti_' . $key, $value);
	}
	
	public function getTeasers($key, $platform_name, $city){
		$key = 'ta_'.$key;
		$value = self::get($key);
		if($value === false){
			$platform_id = self::getPlatformId($platform_name);
			$platform_tag = self::getPlatformTag($platform_id);
			if($platform_id){
				$city_id = self::getCityId($city);
				if($city_id){
					$value = Tz_DB::connection()->getTeasersByGeoPlatforms($platform_id, $city_id,$platform_tag);
					if($value){
						self::set($key, $value);
					}
				}
			}
		}
		return $value;
	}
	
	public function getTeaserHTML($id){
		$key = 'th_'.$id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getTeaser($id);
			if($value){
				$content = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'template.html');
				$value = str_replace(array('%imagepath', '%text', '%url'), array(Tz_Server::getPageURL() . 'i/t/' . $value['picture'], $value['title'], Tz_Server::getPageURL() . 'go?' . Tz_Crypt::encode(rand(0, 99999) . '|' . self::getTeaserNewsId($id))), $content);			
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function getNewsUrl($id){
		$key = 'nu_'.$id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getNewsUrl($id);
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function clearNewsClicks($id){
		$key = 'nk_'.$id;
		self::delete($key);
		return;
	}
	
	public function getNewsClicks($id){
		$key = 'nk_'.$id;
		$value = self::get($key);
		return $value?$value:'';
	}
	
	public function fillNewsClicks($key, $values){
		$key = 'nk_'.$key;
		self::set($key, $values);
		return;
	}
	
	public function getCampaignId($news_id){
		$key = 'cn_'.$news_id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getCampaignId($news_id);
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function getClicksCount($id){
		$key = 'nkc_'.date('Ymd_').$id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getClicksCount($id);
			self::set($key, $value);
		}
		return $value;
	}
	
	public function addClicksCount($id){
		$key = 'nkc_'.date('Ymd_').$id;
		self::increment($key);
	}
	
	public function getClicksLimit($id){
		$key = 'nkl_'.date('Ymd_').$id;
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getClicksLimit($id);
			if($value){
				self::set($key, $value);
				$value = $value;
			}
		}
		return $value;
	}
	
	public function getClicksIterator($id){
		$key = 'nki_'.$id;
		$value = self::get($key);
		if($value === false){
			$value = 0;
			self::set($key, $value);
		}
		return $value;	
	}
	
	public function addClicksIterator($id){
		$key = 'nki_'.$id;
		$value = self::get($key);
		if($value === false){
			$value = -1;
		}
		$value++;
		self::set($key, $value);
		return $value;	
	}
	
	public function setNewsClick($id, $ip, $platform_id){
		$key = 'nk_'.$id;
		$res = self::get($key);
		if(!$res){
			self::set($key, '['.time().',"'.$ip.'",'.$platform_id.']');
		} else {
			self::append($key, ',['.time().',"'.$ip.'",'.$platform_id.']'); 
    	}
		
		$cid = self::getCampaignId($id);
		$click_limit = self::getClicksLimit($cid);
		$click_count = self::getClicksCount($cid);
		
		if($click_limit){
			$click_count++;
			if($click_count >= $click_limit){
				exec('(sleep 11; php /var/www/cron/index.php flush) > /dev/null &');
			} else {
				self::increment('nkc_'.date('Ymd_').$cid);
			}
		} else {
			exec('php /var/www/cron/index.php flush > /dev/null &');
		}
		return;
	}
	
	public function setNewsLastClick($id, $array){
		$value = self::getNewsClicks($id);
		$key = 'nk_'.$id;
		$add = substr(json_encode($array), 1, -1);
		if(!$value){
			self::set($key, $add);
		} else {
			self::append($key, $add);
		}
		return;
	}
	
	public function getNews(){
		$key = 'narr_';
		$value = self::get($key);
		if($value === false){
			$value = Tz_DB::connection()->getNews();
			if($value){
				self::set($key, $value);
			}
		}
		return $value;
	}
	
	public function safeFlush(){
		$news = Tz_Memcached::connection()->getNews();
		if($news){
			$show_save = array();
			$click_save = array();
			foreach ($news as $element){			
				$shows = self::getNewsShowed($element);
				if($shows){
					$show_save[$element] = $shows;
				}
				$clcicks = self::getNewsClicks($element);
				if($clcicks){
					$click_save[$element] = $clcicks;
				}
			}
			///////////////////////FLUSH
			//exec('/etc/init.d/memcached restart');
			self::flush();
			///////////////////////FLUSH
			if($show_save){
				foreach ($show_save as $k => $value){
					self::fillNewsShowed($k, $value);
				}
			}
			if($click_save){
				foreach ($click_save as $k => $value){
					self::fillNewsClicks($k, $value);
				}
			}
		}
	}
	
	public function flush(){
		self::$_memcache->flush();
	}
	
	private function get($key){
		return self::$_memcache->get($key);
	}
	
	private function cas($cas, $key, $value){
		return self::$_memcache->cas($cas, $key, $value);
	}
	private function getCas($key){
		$r = self::$_memcache->get($key, NULL, $cas);
		return array($r, $cas);
	}
	
	private function add($key, $value){
		return self::$_memcache->add($key, $value, 60);
	}
	
	private function set($key, $value, $lifetime = 0){
		$r = self::$_memcache->set($key, $value, $lifetime);
		return $r;
	}
	
	private function delete($key){
		return self::$_memcache->delete($key);
	}
	private function increment($key){
		return self::$_memcache->increment($key);
	}
	private function append($key, $value){
		return self::$_memcache->append($key, $value);
	}
}