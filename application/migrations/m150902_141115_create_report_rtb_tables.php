<?php

class m150902_141115_create_report_rtb_tables extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `report_rtb_daily` (
              `date` date NOT NULL,
              `campaign_id` int(10) unsigned NOT NULL,
              `creative_id` int(10) unsigned NOT NULL DEFAULT '0',
              `platform_id` int(10) unsigned NOT NULL DEFAULT '0',
              `city` varchar(100) NOT NULL,
              `region` varchar(100) NOT NULL,
              `country` varchar(100) NOT NULL,
              `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
              `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
              PRIMARY KEY (`date`,`campaign_id`,`creative_id`,`country`,`city`,`region`,`platform_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

		$this->execute("
            CREATE TABLE `report_rtb_daily_by_platform` (
                `platform_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`platform_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");

		$this->execute("
            CREATE TABLE `report_rtb_daily_by_campaign` (
                `campaign_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`campaign_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");

		$this->execute("
            CREATE TABLE `report_rtb_daily_by_campaign_and_platform` (
                `campaign_id` INT UNSIGNED NOT NULL,
                `platform_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`campaign_id`, `platform_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");

		$this->execute("
            CREATE TABLE `report_rtb_daily_by_campaign_and_platform_and_country` (
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `country` char(2) NOT NULL,
              `date` date NOT NULL,
              `shows` bigint(20) unsigned NOT NULL DEFAULT '0',
              `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`campaign_id`,`platform_id`,`country`,`date`)
            ) ENGINE=InnoDB;");

		$this->execute("CREATE TABLE `report_rtb_daily_by_campaign_and_platform_and_city` (
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `city` char(8) NOT NULL,
              `date` date NOT NULL,
              `shows` bigint(20) unsigned NOT NULL DEFAULT '0',
              `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`campaign_id`,`platform_id`,`city`,`date`)
            ) ENGINE=InnoDB;");
	}

	public function down()
	{
		echo "m150902_141115_create_report_rtb_tables does not support migration down.\n";
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