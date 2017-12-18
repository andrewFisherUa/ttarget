<?php

class m150127_071335_report_action_details extends CDbMigration
{
	public function up()
	{
        $this->execute("
            CREATE TABLE `report_actions` (
              `action_id` INT UNSIGNED NOT NULL,
              `campaign_id` INT UNSIGNED NOT NULL,
              `news_id` INT UNSIGNED NOT NULL,
              `platform_id` INT UNSIGNED NOT NULL,
              `ip` INT UNSIGNED NOT NULL,
              `date` DATETIME NOT NULL,
              `city_id` INT UNSIGNED NOT NULL,
              `country_code` CHAR(2) NOT NULL
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
        $this->execute("ALTER TABLE `report_actions` 
          ADD INDEX `report_actions_campaign_id_date` (`campaign_id` ASC, `date` ASC);");
	}

	public function down()
	{
		echo "m150127_071335_report_action_details does not support migration down.\n";
		return false;
	}
}