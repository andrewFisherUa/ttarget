<?php
/*******************************************************************
*	file: init.platforms.php
*	freated: 20 мая 2015 г. - 6:22:30
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


$_fixtures = (object)array(
		'base1' => (object)array(
				'id' => 1,
				'server' => 'ttarget.strikt-projects.ru',
				'is_active' => 1,
				'is_deleted' => 0,
				'user_id' => $_users->platformActive->id,
				'hosts' => '',
		),
		'base1_withHosts' => (object)array(
				'id' => 2,
				'server' => 'ttarget.strikt-projects.ru',
				'is_active' => 1,
				'is_deleted' => 0,
				'user_id' => $_users->platformActive->id,
				'hosts' => "test1.host.test\ntest2.host.test"
		),
		'base1_bounceCheck' => (object)array(
				'id' => 3,
				'server' => 'ttarget.strikt-projects.ru',
				'is_active' => 1,
				'is_deleted' => 0,
				'user_id' => $_users->platformActive->id,
				'hosts' => ""
		)
);

Yii::app()->db->createCommand()->delete('platforms');
foreach($_fixtures as $k => $_row){
	Yii::app()->db->createCommand()->insert('platforms',$_row);
	$Platform = Platforms::model()->findBypk($_row->id);
	RedisPlatform::instance()->addEncryptedId($Platform);
	if(!empty($Platform->hosts)){
		RedisPlatform::instance()->addHosts($Platform);
	}
	$_fixtures->$k->encryptedId = $Platform->getEncryptedId();
}

return $_fixtures;

/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.platforms.php
**/