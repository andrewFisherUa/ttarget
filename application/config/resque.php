<?php

$yii=dirname(__FILE__).'/../../lib/framework/yii.php';
$config=dirname(__FILE__).'/console.php';

defined('YII_DEBUG') or define('YII_DEBUG',false);
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', 0);

require_once($yii);
$app = Yii::createConsoleApplication($config);

mb_internal_encoding($app->charset);