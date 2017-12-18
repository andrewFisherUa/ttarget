<?php

class m140922_094949_cpa_report_daily_by_action extends CDbMigration
{
	public function up()
	{
        $this->execute("
            CREATE TABLE `report_daily_by_action` (
              `action_id` int(10) unsigned NOT NULL,
              `date` date NOT NULL,
              `actions` bigint(20) unsigned NOT NULL,
              PRIMARY KEY (`action_id`,`date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m140922_094949_cpa_report_daily_by_action does not support migration down.\n";
		return false;
	}
}