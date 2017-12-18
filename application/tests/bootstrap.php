<?php

define('YIIC_TESTS_COMMAND','php '. dirname(__FILE__)."/yiic.php");
define('TESTS_BASE_PATH', dirname(__FILE__));

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../lib/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';

require_once($yiit);
require_once(dirname(__FILE__).'/TtargetUnitTest.php');

Yii::createWebApplication($config);
