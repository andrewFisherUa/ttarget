<?php

class m150903_095539_yandex_platforms extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `yandex_platforms` (
			  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
              `platform_id` INT(11) NOT NULL,
              `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `domain` varchar(100) NOT NULL,
              `referer` varchar(100) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m150903_095539_yandex_platforms does not support migration down.\n";
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