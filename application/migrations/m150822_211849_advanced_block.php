<?php

class m150822_211849_advanced_block extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `blocks`
            CHANGE COLUMN `html` `html` TEXT NULL ,
            ADD COLUMN `css` TEXT NOT NULL AFTER `html`;"
        );

        $this->execute("ALTER TABLE `blocks`
            ADD COLUMN `use_client_code` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `css`;
        ");

        $this->execute("CREATE TABLE `client_code` (
              `platform_id` int(10) unsigned NOT NULL,
              `file_name` varchar(45) NOT NULL,
              `url` varchar(2048) NOT NULL,
              `path` varchar(512) NOT NULL,
              `control_url` varchar(2048) NOT NULL,
              `update_date` datetime NOT NULL,
              `error` varchar(1024) DEFAULT NULL,
              PRIMARY KEY (`platform_id`),
              KEY `client_code_platforms_idx` (`platform_id`),
              CONSTRAINT `client_code_platforms` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m150822_211849_advanced_block does not support migration down.\n";
		return false;
	}
}