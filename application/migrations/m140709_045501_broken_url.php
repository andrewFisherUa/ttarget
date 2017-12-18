<?php

class m140709_045501_broken_url extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `news` ADD COLUMN `is_url_broken` TINYINT UNSIGNED NOT NULL DEFAULT 0  AFTER `clicks_without_externals` ;");
	}

	public function down()
	{
		echo "m140709_045501_broken_url does not support migration down.\n";
		return false;
	}
}