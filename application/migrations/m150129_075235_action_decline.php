<?php

class m150129_075235_action_decline extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `report_actions`
            ADD COLUMN `is_declined` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0 AFTER `country_code`;");
        $this->execute("ALTER TABLE `report_actions`
            ADD COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
            ADD PRIMARY KEY (`id`);");
        $this->execute("ALTER TABLE `report_actions`
            RENAME TO  `teaser_db`.`actions_log` ;");
        // $this->execute("ALTER TABLE `actions_log`
        //    ADD COLUMN `teaser_id` INT UNSIGNED NOT NULL AFTER `news_id`;");
        $this->execute("ALTER TABLE `campaigns`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `report_daily_by_campaign`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform_and_action`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform_and_city`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `report_daily_by_campaign_and_platform_and_country`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");

        // чистим древние обрубки
        $this->execute("ALTER TABLE `campaigns`
            DROP COLUMN `calculated_date`,
            DROP COLUMN `full_clicks_count`,
            DROP COLUMN `day_clicks_count`;");
	}

	public function down()
	{
		echo "m150129_075235_action_decline does not support migration down.\n";
		return false;
	}
}