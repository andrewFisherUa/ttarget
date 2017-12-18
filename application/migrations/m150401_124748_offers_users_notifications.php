<?php

class m150401_124748_offers_users_notifications extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `offers_users_notifications` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `user_id` int(10) unsigned NOT NULL,
						  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `text` text NOT NULL,
						  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '0 - new\r\n1 - readed',
						  PRIMARY KEY (`id`),
						  KEY `user_id` (`user_id`),
						  CONSTRAINT `offers_users_notifications_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function down()
	{
		echo "m150401_124748_offers_users_notifications does not support migration down.\n";
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