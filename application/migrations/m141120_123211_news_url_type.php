<?php

class m141120_123211_news_url_type extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `news` CHANGE COLUMN `is_url_broken` `url_type` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';");
	}

	public function down()
	{
		echo "m141120_123211_news_url_type does not support migration down.\n";
		return false;
	}
}