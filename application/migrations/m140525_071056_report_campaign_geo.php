<?php

class m140525_071056_report_campaign_geo extends CDbMigration
{
	public function up()
	{
        $this->execute("
            CREATE TABLE `report_daily_by_campaign_and_platform_and_country` (
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `country_code` char(2) NOT NULL,
              `date` date NOT NULL,
              `shows` bigint(20) unsigned NOT NULL DEFAULT '0',
              `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`campaign_id`,`platform_id`,`country_code`,`date`)
            ) ENGINE=InnoDB;");

        $this->execute("CREATE TABLE `report_daily_by_campaign_and_platform_and_city` (
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `city_id` int(10) unsigned NOT NULL,
              `date` date NOT NULL,
              `shows` bigint(20) unsigned NOT NULL DEFAULT '0',
              `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`campaign_id`,`platform_id`,`city_id`,`date`)
            ) ENGINE=InnoDB;");

        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `ga_access_token` VARCHAR(512) NULL  AFTER `is_notified` , ADD COLUMN `ga_profile_id` BIGINT UNSIGNED NULL  AFTER `ga_access_token` ;");

	}

	public function down()
	{
		echo "m140525_071056_report_campaign_geo does not support migration down.\n";
		return false;
	}
}