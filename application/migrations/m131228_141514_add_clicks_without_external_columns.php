<?php

class m131228_141514_add_clicks_without_external_columns extends CDbMigration
{
    public function safeUp()
    {
        $this->execute("ALTER TABLE `news` ADD COLUMN `clicks_without_externals` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `clicks_without_externals` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `report_daily_by_campaign` ADD COLUMN `clicks_without_externals` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `report_daily_by_news` ADD COLUMN `clicks_without_externals` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
    }

    public function safeDown()
    {
        $this->dropColumn('news', 'clicks_without_externals');
        $this->dropColumn('campaigns', 'clicks_without_externals');
        $this->dropColumn('report_daily_by_campaign', 'clicks_without_externals');
        $this->dropColumn('report_daily_by_news', 'clicks_without_externals');
    }
}