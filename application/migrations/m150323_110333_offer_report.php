<?php

class m150323_110333_offer_report extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `report_daily_by_offer_user` (
          `offer_user_id` INT UNSIGNED NOT NULL,
          `date` DATE NOT NULL,
          `clicks` BIGINT UNSIGNED NOT NULL DEFAULT 0,
          `actions` BIGINT UNSIGNED NOT NULL DEFAULT 0,
          PRIMARY KEY (`offer_user_id`, `date`));
        ");
	}

	public function down()
	{
		echo "m150323_110333_offer_report does not support migration down.\n";
		return false;
	}
}