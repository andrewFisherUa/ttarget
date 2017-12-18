<?php

class m140319_223715_new_fakeclicks extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `campaigns` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `news` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `report_daily_by_campaign` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `report_daily_by_news` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) NOT NULL DEFAULT '0';");
	}

	public function down()
	{
        $this->execute("ALTER TABLE `campaigns` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `news` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `report_daily_by_campaign` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';");
        $this->execute("ALTER TABLE `report_daily_by_news` CHANGE COLUMN `fake_clicks` `fake_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0';");
	}

}