<?php

class m140720_114939_cpa extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `campaigns_actions` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `campaign_id` int(10) unsigned NOT NULL,
              `target_type` enum('url','click') NOT NULL DEFAULT 'url',
              `target` varchar(512) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` varchar(255) DEFAULT NULL,
              `target_match_type` enum('contain','match','begin','regexp') NOT NULL DEFAULT 'match',
              `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `cost` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
              PRIMARY KEY (`id`),
              KEY `campaigns_actions_campaigns_fk` (`campaign_id`),
              CONSTRAINT `campaigns_actions_campaigns_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `cost_type` ENUM('click', 'action') NOT NULL  AFTER `ga_profile_id` ;");

        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `actions` BIGINT UNSIGNED NOT NULL DEFAULT 0  AFTER `cost_type` ;");

        $this->execute("CREATE TABLE `tracks` (
              `id` int(10) unsigned NOT NULL,
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `is_deleted` tinyint(3) unsigned NOT NULL DEFAULT '0',
              `created_date` datetime NOT NULL,
              `revoked_date` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `tracks_campaigns` (`campaign_id`),
              KEY `tracks_platforms` (`platform_id`),
              CONSTRAINT `tracks_campaigns` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`),
              CONSTRAINT `tracks_platforms` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`)
            ) ENGINE=InnoDB;");

        $this->execute("ALTER TABLE `report_daily_by_campaign` ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'  AFTER `clicks` ;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform` ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'  AFTER `clicks` ;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform_and_city` ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'  AFTER `clicks` ;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform_and_country` ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0'  AFTER `clicks` ;");
	}

	public function down()
	{
		echo "m140720_114939_cpa does not support migration down.\n";
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