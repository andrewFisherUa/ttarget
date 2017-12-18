<?php

class m150203_132446_daily_bounce extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `report_daily_by_campaign`
            ADD COLUMN `bounces` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `clicks`;");
	}

	public function down()
	{
		echo "m150203_132446_daily_bounce does not support migration down.\n";
		return false;
	}
}