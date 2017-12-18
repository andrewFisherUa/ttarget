<?php

class m140213_160127_campaings_reports extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE  TABLE `campaigns_reports` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `campaign_id` INT(10) UNSIGNED NOT NULL ,
            `type` ENUM('full','period') NOT NULL DEFAULT 'period' ,
            `report_date` DATE NOT NULL ,
            PRIMARY KEY (`id`) ,
            INDEX `campaigns_reports_campaigns` (`campaign_id` ASC) ,
            CONSTRAINT `campaigns_reports_campaigns`
            FOREIGN KEY (`campaign_id` )
            REFERENCES `campaigns` (`id` )
            ON DELETE CASCADE
            ON UPDATE CASCADE);
        ");

	}

	public function safeDown()
	{
        $this->dropTable("campaigns_reports");
	}
}