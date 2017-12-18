<?php

class m150331_062050_report_daily_offer extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `report_daily_by_offer` (
            `offer_id` int(10) unsigned NOT NULL,
            `date` date NOT NULL,
            `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
            `actions` bigint(20) unsigned NOT NULL DEFAULT '0',
            PRIMARY KEY (`offer_id`,`date`)
            ) ENGINE=InnoDB;
        ");
	}

	public function down()
	{
		echo "m150331_062050_report_daily_offer does not support migration down.\n";
		return false;
	}
}