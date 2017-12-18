<?php

class m141124_150359_report_daily extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `report_daily` (
              `date` date NOT NULL,
              `campaign_id` int(10) unsigned NOT NULL,
              `news_id` int(10) unsigned NOT NULL DEFAULT '0',
              `teaser_id` int(10) unsigned NOT NULL DEFAULT '0',
              `platform_id` int(10) unsigned NOT NULL DEFAULT '0',
              `city_id` int(10) unsigned NOT NULL DEFAULT '0',
              `country_code` char(2) NOT NULL DEFAULT '0',
              `action_id` int(10) unsigned NOT NULL DEFAULT '0',
              `shows` bigint(20) unsigned NOT NULL DEFAULT '0',
              `clicks` bigint(20) unsigned NOT NULL DEFAULT '0',
              `actions` bigint(20) unsigned NOT NULL DEFAULT '0',
              PRIMARY KEY (`date`,`campaign_id`,`news_id`,`teaser_id`,`platform_id`,`city_id`,`country_code`,`action_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m141124_150359_report_daily does not support migration down.\n";
		return false;
	}
}