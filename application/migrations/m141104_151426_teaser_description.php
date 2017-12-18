<?php

class m141104_151426_teaser_description extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `teasers` ADD COLUMN `description` VARCHAR(75) NOT NULL DEFAULT ''  AFTER `title` ;");
	}

	public function down()
	{
		echo "m141104_151426_teaser_description does not support migration down.\n";
		return false;
	}
}