<?php
	require_once(dirname(__Dir__) . DIRECTORY_SEPARATOR . 'Tz_Lib' .DIRECTORY_SEPARATOR . 'Tz_CronDaily.php');
	if(isset($argv)&&isset($argv[1])&&($argv[1]=='system')){
		Tz_CronDaily::app()->set_system();
	}
	if(isset($argv)&&isset($argv[1])&&($argv[1]=='flush')){
		Tz_CronDaily::app()->set_flush();
	}
	Tz_CronDaily::app()->run();
?>