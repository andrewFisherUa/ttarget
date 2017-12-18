<?php
	class Tz_Geo{
		public static function getGeo($ip){
			$SxGeo = new thirdparty\geo\SxGeo('SxGeoCity.dat');
			return $SxGeo->getCityFull($ip);
		}
	}