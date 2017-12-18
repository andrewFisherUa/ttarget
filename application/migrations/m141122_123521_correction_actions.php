<?php

class m141122_123521_correction_actions extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `report_daily_by_campaign_and_platform_and_action` (
                `date` date NOT NULL,
              `campaign_id` int(10) unsigned NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `action_id` int(10) unsigned NOT NULL,
              `actions` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`date`,`campaign_id`,`platform_id`,`action_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $this->execute("INSERT INTO `report_daily_by_campaign_and_platform_and_action`
            SELECT `date`, `campaign_id`, `platform_id`, 15, `actions`
            FROM `report_daily_by_campaign_and_platform`
            WHERE actions > 0;
        ");
        $this->dropTable("report_daily_by_action");

	}

	public function down()
	{
		echo "m141122_123521_correction_actions does not support migration down.\n";
		return false;
	}
}