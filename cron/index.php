<?php
	require_once(dirname(__Dir__) . DIRECTORY_SEPARATOR . 'Tz_Lib' .DIRECTORY_SEPARATOR . 'Tz_Cron.php');
	if(isset($argv)&&isset($argv[1])&&($argv[1]=='system')){
		Tz_Cron::app()->set_system();
	}

	if(isset($argv)&&isset($argv[1])&&($argv[1]=='flush')){
		Tz_Cron::app()->set_flush();
	}
	Tz_Cron::app()->run();
?>