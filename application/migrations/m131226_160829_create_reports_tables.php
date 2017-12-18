<?php

class m131226_160829_create_reports_tables extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE TABLE `report_daily_by_platform` (
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
            CREATE TABLE `report_daily_by_campaign` (
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
            CREATE TABLE `report_daily_by_news` (
                `news_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`news_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");

        $this->execute("
            CREATE TABLE `report_daily_by_campaign_and_platform` (
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
            CREATE TABLE `report_daily_by_news_and_platform` (
                `news_id` INT UNSIGNED NOT NULL,
                `platform_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`news_id`, `platform_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
	}

	public function safeDown()
	{
        $this->dropTable('report_daily_by_platform');
        $this->dropTable('report_daily_by_campaign');
        $this->dropTable('report_daily_by_news');
        $this->dropTable('report_daily_by_campaign_and_platform');
        $this->dropTable('report_daily_by_news_and_platform');
	}
}