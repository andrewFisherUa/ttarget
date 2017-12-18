<?php

class m150406_080122_offer_action_log extends CDbMigration
{
	public function up()
	{
//        $this->execute("CREATE TABLE `teaser_db`.`offers_actions_log` (
//              `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
//              `campaign_id` INT UNSIGNED NOT NULL,
//              `offer_id` INT UNSIGNED NOT NULL,
//              `offer_user_id` INT UNSIGNED NOT NULL,
//              `ip` INT UNSIGNED NOT NULL,
//              `date` DATETIME NOT NULL,
//              `city_id` INT UNSIGNED NOT NULL,
//              `country_code` CHAR(2) NULL,
//              `is_declined` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
//              PRIMARY KEY (`id`));
//        ");

	}

	public function down()
	{
		echo "m150406_080122_offer_action_log does not support migration down.\n";
		return false;
	}

}