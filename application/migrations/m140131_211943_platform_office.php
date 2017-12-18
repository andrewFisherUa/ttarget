<?php

class m140131_211943_platform_office extends CDbMigration
{
	public function safeUp()
	{
		$this->execute("ALTER TABLE `users` CHANGE COLUMN `role` `role` ENUM('guest','user','admin', 'platform') NOT NULL DEFAULT 'guest';");
		$this->execute("
			CREATE TABLE `notifications` (
			  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `platform_id` int(10) unsigned DEFAULT NULL,
			  `teaser_id` int(10) unsigned DEFAULT NULL,
			  `create_date` date NOT NULL,
			  `action` enum('except') NOT NULL DEFAULT 'except',
			  `is_new` tinyint(3) unsigned NOT NULL DEFAULT '1',
			  PRIMARY KEY (`id`),
			  KEY `platform_notifications_platform` (`platform_id`),
			  KEY `platform_notifications_teaser` (`teaser_id`),
			  CONSTRAINT `platform_notifications_platform` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
			  CONSTRAINT `platform_notifications_teaser` FOREIGN KEY (`teaser_id`) REFERENCES `teasers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		$this->execute("ALTER TABLE `billing_income` CHANGE COLUMN `number` `number` INT UNSIGNED NOT NULL;");
		$this->execute("UPDATE users SET role='platform' WHERE (SELECT COUNT(id) FROM platforms WHERE platforms.user_id=users.id)");
	}

	public function safeDown()
	{
		$this->execute("ALTER TABLE `users` CHANGE COLUMN `role` `role` ENUM('guest','user','admin') NOT NULL DEFAULT 'guest';");
		$this->dropTable("notifications");
		$this->execute("ALTER TABLE `billing_income` CHANGE COLUMN `number` `number` varchar(32) NOT NULL;");
		$this->execute("UPDATE users SET role='user' WHERE role='platform';");
	}

}