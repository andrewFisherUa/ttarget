<?php
$main = require(dirname(__FILE__) . '/main.local.php');
// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'runtimePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime',
    'name' => 'My Console Application',
    'sourceLanguage' => $main['sourceLanguage'],
    'language' => $main['language'],

    // preloading 'log' component
    'preload' => array('log'),

    // autoloading model and component classes
    'import' => array_merge($main['import'], array(
        'ext.yii-resque.RResqueCommand',
    )),

    // application components
    'components' => array(
        'redis' => $main['components']['redis'],
        'resque' => $main['components']['resque'],
        'cache' => $main['components']['cache'],
        'db' => $main['components']['db'],
        'mysqli' => $main['components']['mysqli'],
        'log' => $main['components']['log'],
        'mail' => $main['components']['mail'],
        'xmlrpc' => $main['components']['xmlrpc'],
        'db_test' => array(
            'class' => 'CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=teaser_db_test',
            'emulatePrepare' => true,
            'username' => 'teaser',
            'password' => 'teaser_my$qLyu',
            'charset' => 'utf8',
            'schemaCacheID' => 'cache',
            'schemaCachingDuration' => 3600,
        ),
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array_merge($main['params'], array()),
);
