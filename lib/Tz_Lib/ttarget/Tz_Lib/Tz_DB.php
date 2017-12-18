<?php
class Tz_DB{
	protected static $_instance;
	protected static $_db;
	
	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}
	
	public static function connection() {
	    if (null === self::$_instance) {
	        self::$_instance = new self();
	        $mysqli = new mysqli(	Tz_Config::data()->get('db.host'),
	        						Tz_Config::data()->get('db.user'),
	        						Tz_Config::data()->get('db.password'),
	        						Tz_Config::data()->get('db.db_name'));
	        						
	        if ($mysqli->connect_errno) {
			    printf("Connect failed: %s\n", $mysqli->connect_error);
			    exit();
			}
	        $mysqli->set_charset('utf8');
	        self::$_db = $mysqli;
	    }
	    return self::$_instance;
	}
	
	public function getPlatformId($platform_name){
		$stmt = self::$_db->prepare("SELECT id FROM `".Tz_Config::data()->get('db.prefix')."platforms` WHERE `server` = ? AND is_active = 1");
		$stmt->bind_param('s', $platform_name);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		return $id;
	}
	public function getPlatformTag($platform_id){
		$stmt = self::$_db->prepare("SELECT tag_id FROM `".Tz_Config::data()->get('db.prefix')."platforms` WHERE `id` = ? AND is_active = 1");
		$stmt->bind_param('i', $platform_id);
		$stmt->execute();
		$stmt->bind_result($tag_id);
		$stmt->fetch();
		return $tag_id;
	}
	
	public function getTeaserNewsId($teaser_id){
		$stmt = self::$_db->prepare("SELECT news_id FROM `".Tz_Config::data()->get('db.prefix')."teasers` WHERE `id` = ?");
		$stmt->bind_param('i', $teaser_id);
		$stmt->execute();
		$stmt->bind_result($news_id);
		$stmt->fetch();
		return $news_id;
	}
	
	public function getNewsUrl($news_id){
		$stmt = self::$_db->prepare("SELECT url FROM `".Tz_Config::data()->get('db.prefix')."news` WHERE `id` = ?");
		$stmt->bind_param('i', $news_id);
		$stmt->execute();
		$stmt->bind_result($url);
		$stmt->fetch();
		return $url;
	}
	
	public function addNewsShowed($news_id, $platform_id, $show){
		$stmt = self::$_db->prepare("INSERT INTO `".Tz_Config::data()->get('db.prefix')."shows`
			(	`news_id` ,
				`showdate` ,
				`from_id`,
				`count`
				) VALUES (?,?,?,?)
				ON DUPLICATE KEY UPDATE
				`count` = `count` + ?
				");
		$stmt->bind_param('isiii', $news_id, date('Y-m-d'), $platform_id, $show, $show);
		$stmt->execute();
		return;
	}
	
	public function addNewsClick($news_id, $click, $is_real){
		$stmt = self::$_db->prepare("INSERT INTO `".Tz_Config::data()->get('db.prefix')."clicks` (`news_id` ,`click_date` ,`ip`, `from_id`, `is_real`) VALUES (?,?,?,?,?)");
		$stmt->bind_param('issii', $news_id, date('Y-m-d H:i:s', $click[0]), $click[1], $click[2], $is_real);
		$stmt->execute();
		//echo "INSERT INTO `".Tz_Config::data()->get('db.prefix')."clicks` (`news_id` ,`click_date` ,`ip`, `from_id`, `is_real`) VALUES ('".$news_id."','". date('Y-m-d H:i:s', $click[0])."','".$click[1]."','".$click[2]."','".$is_real."')";

		return;
	}
	
	public function getCityId($city_name){
		$stmt = self::$_db->prepare("SELECT id FROM `".Tz_Config::data()->get('db.prefix')."cities` WHERE `name` = ?");
		$stmt->bind_param('s', $city_name);
		$stmt->execute();
		$stmt->bind_result($id);
		$stmt->fetch();
		return $id;
	}
	
	public function getTeaser($id){
		$stmt = self::$_db->prepare("SELECT `title`, `picture` FROM `".Tz_Config::data()->get('db.prefix')."teasers` WHERE `id` = ? AND is_active = 1");
		$stmt->bind_param('i', $id);
		$stmt->execute();
		return self::fetchOne($stmt);
	}
	
	public function getTeasersByGeoPlatforms($platform_id, $city_id, $tag_id){
		$stmt = self::$_db->prepare("	SELECT t.`id` FROM ".Tz_Config::data()->get('db.prefix')."teasers t
											LEFT JOIN ".Tz_Config::data()->get('db.prefix')."ct_except ct ON (ct.`teaser_id` = t.`id` AND ct.`platform_id` = ?)
											INNER JOIN ".Tz_Config::data()->get('db.prefix')."news n ON (n.`id` = t.`news_id` AND n.`tag_id` = ?)
											LEFT JOIN ".Tz_Config::data()->get('db.prefix')."nc_except nc ON (nc.`news_id` = n.`id` AND nc.`city_id` = ?)
											INNER JOIN ".Tz_Config::data()->get('db.prefix')."campaigns c ON (c.`id` = n.`campaign_id`)
										WHERE
											ct.`platform_id` IS NULL AND
											nc.`city_id` IS NULL AND											
											t.`is_active` = 1 AND 
											n.`is_active` = 1 AND 
											n.`deleted` = 0 AND 
											c.`is_active` = 1 AND
											CURDATE() BETWEEN c.`date_start` AND c.`date_end` AND
											(c.`full_clicks_count` < c.`max_clicks` OR c.`max_clicks` IS NULL) AND
											(c.`limit_per_day` = 0 OR c.`calculated_date` <> ? OR (c.`day_clicks` > c.`day_clicks_count`) )
										GROUP BY t.`id`
										ORDER BY t.`id` DESC");
		$stmt->bind_param('iiis', $platform_id, $tag_id, $city_id, date('Y-m-d'));
		$stmt->execute();
		$r = self::fetchArrayFlat($stmt, 'id');
		return $r;
	}
	
	public function getClicksCount($cid){
		$stmt = self::$_db->prepare("SELECT * FROM ".Tz_Config::data()->get('db.prefix')."campaigns WHERE id = ?");
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$val = self::fetchOne($stmt);
		if($val){
			if($val['limit_per_day'] == 1){
				if(date('Y-m-d') == $val['calculated_date']){
					return $val['day_clicks_count'];
				} else {
					$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."campaigns` SET `calculated_date` = ?, `day_clicks_count` = 0 WHERE id = ?");
					$stmt->bind_param('si', date('Y-m-d'), $cid);
					$stmt->execute();
					return 0;
				}
			} else {
				return $val['full_clicks_count'];
			}
		}
	}
	public function getClicksLimit($cid){
		$stmt = self::$_db->prepare("SELECT * FROM ".Tz_Config::data()->get('db.prefix')."campaigns WHERE id = ?");
		$stmt->bind_param('i', $cid);
		$stmt->execute();
		$val = self::fetchOne($stmt);
		
		if($val){
			if($val['limit_per_day'] == 1){
				if(is_null($val['max_clicks'])){
					return $val['day_clicks'];
				} else {
					return $val['max_clicks'] < ($val['full_clicks_count'] + $val['day_clicks'])?($val['max_clicks'] - $val['full_clicks_count'] ):$val['day_clicks'];
				}
			} else {
				return is_null($val['max_clicks'])?999999999:$val['max_clicks'];
			}
		}
	}
	
	public function getNews(){
		$stmt = self::$_db->prepare("	SELECT n.`id` FROM ".Tz_Config::data()->get('db.prefix')."news n
										INNER JOIN ".Tz_Config::data()->get('db.prefix')."campaigns c ON (c.`id` = n.`campaign_id`)
										WHERE n.`is_active` = 1 AND n.`deleted` = 0 AND 
										CURDATE() BETWEEN c.`date_start` AND c.`date_end` AND
											(c.`full_clicks_count` < c.`max_clicks` OR c.`max_clicks` IS NULL) AND
											(c.`limit_per_day` = 0 OR c.`calculated_date` <> ? OR (c.`day_clicks` > c.`day_clicks_count`) )");
		
		$stmt->bind_param('s', date('Y-m-d'));
		$stmt->execute();
		return self::fetchArrayFlat($stmt, 'id');
	}
	
	public function getAnaliticNews(){
		$stmt = self::$_db->prepare("	SELECT n.`id`, n.`quality`, n.`create_date`, n.`last_quality_week` FROM ".Tz_Config::data()->get('db.prefix')."news n
										INNER JOIN ".Tz_Config::data()->get('db.prefix')."campaigns c ON (c.`id` = n.`campaign_id`)
										WHERE n.`is_active` = 1 AND n.`deleted` = 0 AND 
										CURDATE() BETWEEN c.`date_start` AND c.`date_end` AND
											(c.`full_clicks_count` < c.`max_clicks` OR c.`max_clicks` IS NULL) AND
											(c.`limit_per_day` = 0 OR c.`calculated_date` <> ? OR (c.`day_clicks` > c.`day_clicks_count`) )");
		$stmt->bind_param('s', date('Y-m-d'));
		$stmt->execute();
		return self::fetchArray($stmt, 'id');
	}
	
	public function checkActiveCampaigns(){
		$stmt = self::$_db->prepare("	UPDATE ".Tz_Config::data()->get('db.prefix')."campaigns c
										SET c.`is_active` = 0
										WHERE c.`is_active` = 1 AND CURDATE() > c.`date_end`");
		$stmt->execute();
		return;
	}
	
	public function getClicksByPeriod($news_id, $start_date, $end_dete){
		$stmt = self::$_db->prepare("	SELECT count(c.id) as cnt FROM ".Tz_Config::data()->get('db.prefix')."clicks c
										WHERE c.`news_id` = ? AND
										c.`is_real` = 1 AND
										c.`click_date` BETWEEN ? AND ?");
		$stmt->bind_param('iss', $news_id, $start_date, $end_dete);
		$stmt->execute();
		$row = self::fetchOne($stmt);
		$val = $row['cnt'];
		
		$stmt = self::$_db->prepare("	SELECT SUM(c.`count`) as cnt FROM ".Tz_Config::data()->get('db.prefix')."fake_clicks c
										WHERE c.`news_id` = ? AND
										c.`click_date` BETWEEN ? AND ?");
		$stmt->bind_param('iss', $news_id, $start_date, $end_dete);
		$stmt->execute();
		$row = self::fetchOne($stmt);
		$val += $row['cnt'];
		
		return $val;
	}
	
	public function setQuality($news_id, $quality, $week){
		$time = time();
		$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."news` SET `quality` = ?, `last_quality_week`= ? WHERE `id`= ?");
		$stmt->bind_param('sii', $quality, $week, $news_id);
		$stmt->execute();
		return;
	}
	public function setCronUpdate($system = false){
		$time = time();
		$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."options` SET `value` = ? WHERE `key`='last_cron_update'");
		$stmt->bind_param('i', $time);
		$stmt->execute();
		if($system){
			$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."options` SET `value` = ? WHERE `key`='next_cron_update'");
			$stmt->bind_param('i', $tmp = ($time+300));
			$stmt->execute();
		}
		return;
	}
	
	public function setDailyUpdate($system = false){
		$time = time();
		$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."options` SET `value` = ? WHERE `key`='last_daily_update'");
		$stmt->bind_param('i', $time);
		$stmt->execute();
		if($system){
			$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."options` SET `value` = ? WHERE `key`='next_daily_update'");
			$stmt->bind_param('i', $tmp = ($time+86400));
			$stmt->execute();
		}
		return;
	}

	public function getCampaignId($news_id){
		$stmt = self::$_db->prepare("SELECT campaign_id FROM `".Tz_Config::data()->get('db.prefix')."news` WHERE `id` = ?");
		$stmt->bind_param('i', $news_id);
		$stmt->execute();
		$stmt->bind_result($campaign_id);
		$stmt->fetch();
		return $campaign_id;
	}
	
	public function addCampaignClicks($campaign_id, $count){
		$return = false;
		$stmt = self::$_db->prepare("SELECT * FROM ".Tz_Config::data()->get('db.prefix')."campaigns WHERE id = ?");
		$stmt->bind_param('i', $campaign_id);
		$stmt->execute();
		$val = self::fetchOne($stmt);
		
		if($val){
			if($val['limit_per_day'] == 1){
				$curr_count = $val['full_clicks_count'] + $count;
				$curr_day_count = $val['day_clicks_count'] + $count;
				$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."campaigns` SET `full_clicks_count` = ?, `day_clicks_count` = ? WHERE `id`=?");
				$stmt->bind_param('iii', $curr_count, $curr_day_count, $campaign_id);
				$stmt->execute();
				return ((($curr_count < $val['max_clicks']) || is_null($val['max_clicks'])) && $curr_day_count < $val['day_clicks']);
			} else {
				$curr_count = $val['full_clicks_count'] + $count;
				$stmt = self::$_db->prepare("UPDATE `".Tz_Config::data()->get('db.prefix')."campaigns` SET `full_clicks_count` = ? WHERE `id`=?");
				$stmt->bind_param('ii', $curr_count, $campaign_id);
				$stmt->execute();
				return (($curr_count < $val['max_clicks'])|| is_null($val['max_clicks']));
			}
		}
		return $return;
	}
	
	private function fetchArray($stmt){
   		$result = $stmt->get_result();
   		$return = array();
        while ($row = $result->fetch_assoc()){
			$return[] = $row;
        }
        return $return;
	}
	private function fetchOne($stmt){
   		$result = $stmt->get_result();
   		$return = array();
        return $result->fetch_assoc();
	}
	
	private function fetchArrayFlat($stmt, $key){
   		$result = $stmt->get_result();
   		$return = array();
        while ($row = $result->fetch_assoc()){
			$return[] = $row[$key];
        }
        return $return;
	}
}
	