<?php

class m150409_074911_offer_new_action_log extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `actions_log`
            ADD COLUMN `source_type` ENUM('teaser', 'offer') NOT NULL DEFAULT 'teaser' AFTER `is_declined`,
            ADD COLUMN `source_id` INT(10) UNSIGNED NOT NULL AFTER `source_type`,
            ADD COLUMN `target_id` INT(10) UNSIGNED NOT NULL AFTER `source_id`,
            ADD COLUMN `referrer_url` VARCHAR(2048) NULL AFTER `country_code`;");

        $this->execute("UPDATE `actions_log` SET `source_id` = `platform_id`, `target_id` = `news_id`;");

        $this->execute("ALTER TABLE `actions_log`
            DROP COLUMN `platform_id`,
            DROP COLUMN `news_id`;");

        $this->execute("DROP TABLE IF EXISTS `offers_actions_log`");

        $this->execute("ALTER TABLE `offers_users`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `clicks`;");

        $this->execute("ALTER TABLE `offers`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");

        $this->execute("ALTER TABLE `tracks`
            ADD COLUMN `referrer_url` VARCHAR(2048) NOT NULL DEFAULT '' AFTER `action_eid`;");
	}

	public function down()
	{
		echo "m150409_074911_offer_new_action_log does not support migration down.\n";
		return false;
	}
}