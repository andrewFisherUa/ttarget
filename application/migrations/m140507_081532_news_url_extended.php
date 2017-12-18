<?php

class m140507_081532_news_url_extended extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `news` CHANGE COLUMN `url` `url` VARCHAR(512) NOT NULL  ;");
	}

	public function down()
	{
		echo "m140507_081532_news_url_extended does not support migration down.\n";
		return false;
	}
}