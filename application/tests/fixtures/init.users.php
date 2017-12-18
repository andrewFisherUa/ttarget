<?php
/*******************************************************************
*	file: init.users.php
*	freated: 20 мая 2015 г. - 6:02:11
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/

$_fixtures = (object)array(
	'platformActive' => (object)array(
			'id' => 1,
			'email' => 'platform_user_active_1@email.test',
			'login' => 'platform_user_active_1',
			'password' => md5('password'),
			'role' => 'platform',
			'billing_details_text' => 'test',
			'is_deleted' => 0
	),
	'platformDeleted' => (object)array(
			'id' => 2,
			'email' => 'platform_user_deleted_1@email.test',
			'login' => 'platform_user_deleted_1',
			'password' => md5('password'),
			'role' => 'platform',
			'billing_details_text' => 'test',
			'is_deleted' => 1
	),
	'webmasterActive' => (object)array(
			'id' => 3,
			'email' => 'webmaster_user_active_1@email.test',
			'login' => 'webmaster_user_active_1',
			'password' => md5('password'),
			'role' => 'webmaster',
			'billing_details_text' => 'test',
			'is_deleted' => 0
	),
	'webmasterDeleted' => (object)array(
			'id' => 4,
			'email' => 'webmaster_user_deleted_1@email.test',
			'login' => 'webmaster_user_deleted_1',
			'password' => md5('password'),
			'role' => 'webmaster',
			'billing_details_text' => 'test',
			'is_deleted' => 1
	)
);

Yii::app()->db->createCommand()->delete('users');
foreach($_fixtures as $_row){
	Yii::app()->db->createCommand()->insert('users',$_row);
}

return $_fixtures;

/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.users.php
**/