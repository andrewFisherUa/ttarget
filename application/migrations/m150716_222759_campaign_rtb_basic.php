<?

class m150716_222759_campaign_rtb_basic extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `campaigns` MODIFY COLUMN `cost_type` ENUM('click','action','rtb') NOT NULL");
		$this->execute("ALTER TABLE `campaigns` ADD COLUMN `rtb_url` VARCHAR(255) DEFAULT NULL COMMENT 'RTB: Сайт'");
		$this->execute("ALTER TABLE `campaigns` ADD COLUMN `rtb_cost` INTEGER(11) NOT NULL DEFAULT '0' COMMENT 'RTB: стоимость'");
		$this->execute("ALTER TABLE `campaigns` ADD COLUMN `rtb_cost_type` ENUM('cpm','cpc') DEFAULT NULL COMMENT 'RTB: тип стоимости (Max CPM/Target CPC)'");
		
		$this->execute("CREATE TABLE `campaigns_creative` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                          `campaign_id` int(10) unsigned NOT NULL,
                          `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                          `name` varchar(50) CHARACTER SET latin1 NOT NULL,
                          `type` enum('image','audio','video') NOT NULL COMMENT 'Формат креатива - ??',
                          `filename` varchar(255) NOT NULL,
                          `filesize` varchar(255) NOT NULL,
                          `max_shows_hour` int(11) NOT NULL DEFAULT '0' COMMENT 'Максимальное число показов за час',
                          `max_shows_day` int(11) NOT NULL DEFAULT '0' COMMENT 'Максимальное число показов за день',
                          `max_shows_week` int(11) NOT NULL DEFAULT '0' COMMENT 'Максимальное число показов за неделю',
                          `count_shows_total` int(11) NOT NULL DEFAULT '0',
                          `count_actions_total` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'переходы всего',
                          `is_active` tinyint(4) NOT NULL DEFAULT '0',
                          `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Участие в аукционе – на модерации /принят',
                          `dsp_id` int(11) unsigned NOT NULL DEFAULT '0',
                          `is_winner` tinyint(3) unsigned NOT NULL DEFAULT '0',
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("ALTER TABLE `campaigns_creative` ADD INDEX `campaign_id` (`campaign_id`)");
		$this->execute("ALTER TABLE `campaigns_creative` ADD CONSTRAINT `campaigns_creative_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		
		
		$this->execute("CREATE TABLE `campaigns_creative_tags` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `creative_id` int(10) unsigned NOT NULL,
						  `tag_id` int(10) unsigned NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("ALTER TABLE `campaigns_creative_tags` ADD INDEX `creative_id` (`creative_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_tags` ADD INDEX `tag_id` (`tag_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_tags` ADD CONSTRAINT `campaigns_creative_tags_fk` FOREIGN KEY (`creative_id`) REFERENCES `campaigns_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		$this->execute("ALTER TABLE `campaigns_creative_tags` ADD CONSTRAINT `campaigns_creative_tags_fk1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		
		
		$this->execute("CREATE TABLE `campaigns_creative_types` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(20) NOT NULL,
						  PRIMARY KEY (`id`),
						  UNIQUE KEY `name` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("CREATE TABLE `campaigns_creative_types_relations` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `creative_id` int(10) unsigned NOT NULL,
						  `type_id` int(10) unsigned NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("ALTER TABLE `campaigns_creative_types_relations` ADD INDEX `creative_id` (`creative_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_types_relations` ADD INDEX `type_id` (`type_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_types_relations` ADD UNIQUE `creative_id_type_id_unique` (`creative_id`, `type_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_types_relations` ADD CONSTRAINT `campaigns_creative_types_relations_fk` FOREIGN KEY (`creative_id`) REFERENCES `campaigns_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		$this->execute("ALTER TABLE `campaigns_creative_types_relations` ADD CONSTRAINT `campaigns_creative_types_relations_fk1` FOREIGN KEY (`type_id`) REFERENCES `campaigns_creative_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
	
		$this->execute("INSERT INTO `campaigns_creative_types` (`id`, `name`) VALUES (NULL, 'Yandex')");
		$this->execute("INSERT INTO `campaigns_creative_types` (`id`, `name`) VALUES (NULL, 'Google')");
	}

	public function down()
	{
		echo "m150716_222759_campaign_rtb_basic does not support migration down.\n";
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