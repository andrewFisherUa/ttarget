<?php

class m140107_110549_create_table_report_clickfraud extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE TABLE `report_daily_clickfraud` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `ip` INT UNSIGNED NOT NULL,
                `news_id` INT UNSIGNED NOT NULL,
                `platform_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`),
                UNIQUE INDEX `r_d_c_ip_campaign_id_platform_id_date_unique` (`ip`, `news_id`, `platform_id`, `date`),
                CONSTRAINT `FK_r_d_c_news` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE,
                CONSTRAINT `FK_r_d_c_platforms` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
	}

	public function safeDown()
	{
        $this->dropTable('report_daily_clickfraud');
	}
}