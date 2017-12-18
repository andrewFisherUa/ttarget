<?php
/*******************************************************************
*	file: init.php
*	freated: 19 мая 2015 г. - 7:22:56
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/



$_users 	= require_once(dirname(__FILE__).'/init.users.php');
$_platforms = require_once(dirname(__FILE__).'/init.platforms.php');
$_campaigns = require_once(dirname(__FILE__).'/init.campaigns.php');
$_news 		= require_once(dirname(__FILE__).'/init.news.php');
$_teasers	= require_once(dirname(__FILE__).'/init.teasers.php');


$_fixtures = (object)array(
		'users' 	=> $_users,
		'platforms' => $_platforms,
		'campaigns' => $_campaigns,
		'news' 		=> $_news,
		'teasers'	=> $_teasers
);

Yii::app()->params->testData = $_fixtures;

/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.php
**/