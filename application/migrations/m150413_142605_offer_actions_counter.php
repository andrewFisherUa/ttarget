<?php

class m150413_142605_offer_actions_counter extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `teaser_db`.`report_daily`
            ADD COLUMN `offer_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `action_id`,
            ADD COLUMN `offer_user_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `offer_id`,
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (`date`, `campaign_id`, `news_id`, `teaser_id`, `platform_id`, `city_id`, `country_code`, `action_id`, `offer_id`, `offer_user_id`);");

        $this->execute("ALTER TABLE `teaser_db`.`report_daily_by_campaign`
            ADD COLUMN `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `clicks_without_externals`,
            ADD COLUMN `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `offers_actions`;");

        $this->execute("ALTER TABLE `teaser_db`.`campaigns`
            ADD COLUMN `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `declined_actions`,
            ADD COLUMN `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `offers_actions`;");
	}

	public function down()
	{
		echo "m150413_142605_offer_actions_counter does not support migration down.\n";
		return false;
	}
}