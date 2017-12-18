<?php

class m131227_002252_add_shows_and_clicks_columns extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE `news` ADD COLUMN `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `news` ADD COLUMN `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");

        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function safeDown()
	{
        $this->dropColumn('news', 'shows');
        $this->dropColumn('news', 'clicks');

        $this->dropColumn('campaigns', 'shows');
        $this->dropColumn('campaigns', 'clicks');
	}
}