<?php

class m131228_123738_create_fake_clicks_columns extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE `news` ADD COLUMN `fake_clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `fake_clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `report_daily_by_campaign` ADD COLUMN `fake_clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `report_daily_by_news` ADD COLUMN `fake_clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function safeDown()
	{
        $this->dropColumn('news', 'fake_clicks');
        $this->dropColumn('campaigns', 'fake_clicks');
        $this->dropColumn('report_daily_by_campaign', 'fake_clicks');
        $this->dropColumn('report_daily_by_news', 'fake_clicks');
	}
}