<?php

class m150905_091747_create_platforms_yandex_tags extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `platforms_yandex_tags` (
						`platform_id` int(10) unsigned NOT NULL,
						`tag_id` int(10) unsigned NOT NULL,
						PRIMARY KEY (`platform_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");


	}

	public function down()
	{
		echo "m150905_091747_create_platforms_yandex_tags does not support migration down.\n";
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