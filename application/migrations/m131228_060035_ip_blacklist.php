<?php

class m131228_060035_ip_blacklist extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE TABLE `ip_blacklist` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `ip` INT UNSIGNED NOT NULL,
                `unix_timestamp` INT UNSIGNED NOT NULL,
                `interval` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                INDEX `i_b_ip_unix_timestamp_idx` (`ip`, `unix_timestamp`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
	}

	public function safeDown()
	{
        $this->dropTable('ip_blacklist');
	}
}