<?php
	class Tz_Server{
		public static function getRefererHost(){
			$url = parse_url($_SERVER['HTTP_REFERER']);
			return isset($url['host'])?(substr($url['host'], 0, 4) == 'www.'?substr($url['host'], 4):$url['host']):null;
		}
		
		public static function getIP(){
			return $_SERVER['REMOTE_ADDR'];
		}
		
		public static function getCountTeasers(){
			$server_width = isset($_GET['w'])?$_GET['w']:0;
			$server_heigth = isset($_GET['h'])?$_GET['h']:0;
			
			$teaser_width = Tz_Config::data()->get('teaser.width');
			$teaser_heigth = Tz_Config::data()->get('teaser.heigth');
			
			$by_w = floor($server_width / $teaser_width);
			$by_h = floor($server_heigth / $teaser_heigth);
			
			return $by_w * $by_h;
		}
		
		public static function getPageURL(){
			$pageURL = 'http';
 			if (isset($_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 			$pageURL .= "://";
 			if ($_SERVER["SERVER_PORT"] != "80") {
  				$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
 			} else {
  				$pageURL .= $_SERVER["SERVER_NAME"];
 			}
 			return $pageURL . '/';
		}
	}
		