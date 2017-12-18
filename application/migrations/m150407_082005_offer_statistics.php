<?php

class m150407_082005_offer_statistics extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `report_daily_by_offer`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;
        ");
        $this->execute("ALTER TABLE `report_daily_by_offer_user`
            ADD COLUMN `declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;
        ");
	}

	public function down()
	{
		echo "m150407_082005_offer_statistics does not support migration down.\n";
		return false;
	}
}