<?php

class m150429_130448_offers_users_filter extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `offers_users_filter` (
					  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `offer_id` int(10) unsigned NOT NULL,
					  `user_id` int(10) unsigned NOT NULL,
					  `type` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '1 - allow for user\r\n0 - deny for user',
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `offer_id_user_id_uniq` (`offer_id`,`user_id`),
					  KEY `offer_id` (`offer_id`),
					  KEY `user_id` (`user_id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("ALTER TABLE `offers_users_filter` ADD CONSTRAINT `offers_users_filter_offers_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		$this->execute("ALTER TABLE `offers_users_filter` ADD CONSTRAINT `offers_users_filter_users_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		$this->execute("ALTER TABLE `offers` DROP COLUMN `visible`");
	}

	public function down()
	{
		echo "m150429_130448_offers_users_filter does not support migration down.\n";
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