<?php

class m150727_090126_user_session extends CDbMigration
{
	public function up()
	{
	    $this->execute("CREATE TABLE `user_session` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `uuid` varchar(50) CHARACTER SET latin1 NOT NULL,
                      `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `last_visit` datetime NOT NULL,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `uuid` (`uuid`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	    
	    
	    $this->execute("CREATE TABLE `user_session_log` (
                      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                      `session_id` int(10) unsigned NOT NULL,
                      `remote_addr` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
                      `request_uri` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
                      `http_referer` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
                      `city_id` int(10) DEFAULT NULL,
                      `country_code` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
                      `http_user_agent` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
                      `request_data` text CHARACTER SET latin1,
                      `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`),
                      KEY `session_id` (`session_id`),
                      CONSTRAINT `user_session_log_fk` FOREIGN KEY (`session_id`) REFERENCES `user_session` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function down()
	{
		echo "m150727_090126_user_session does not support migration down.\n";
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