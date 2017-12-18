<?php

class m150612_095631_news_url_status extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `news` ADD COLUMN `url_status` SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER `url_type`;");
	}

	public function down()
	{
		echo "m150612_095631_news_url_status does not support migration down.\n";
		return false;
	}
}