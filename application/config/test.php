<?php

return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			'db' => array(
	            'connectionString' => 'mysql:host=localhost;dbname=teaser_db_test',
	            'emulatePrepare' => true,
	            'username' => 'teaser',
	            'password' => 'teaser_my$qLyu',
	            'charset' => 'utf8',
	            'schemaCacheID' => 'cache',
	            'schemaCachingDuration' => 3600,
            ),
			'mysqli' => array(
					'class'     => 'application.components.MysqliWrapper',
					'username'  => 'teaser',
					'password'  => 'teaser_my$qLyu',
					'database'  => 'teaser_db_test'
			),
		),
		'params' => array(
			'testBaseUrl' => 'http://'.$_SERVER['SERVER_NAME'],
			'testData' => array()
		)
	)
);
