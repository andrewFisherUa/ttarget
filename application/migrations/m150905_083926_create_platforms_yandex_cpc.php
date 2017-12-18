<?php

class m150905_083926_create_platforms_yandex_cpc extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `platforms_yandex_cpc` (
						`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						`date` date NOT NULL,
						`platform_id` int(10) unsigned NOT NULL,
						`cost` decimal(10,2) unsigned NOT NULL,
						PRIMARY KEY (`id`)
					) ENGINE=InnoDB AUTO_INCREMENT=298 DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		echo "m150905_083926_create_platforms_yandex_cpc does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}