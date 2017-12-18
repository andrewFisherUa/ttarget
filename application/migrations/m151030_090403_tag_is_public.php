<?php

class m151030_090403_tag_is_public extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `tags`
            ADD COLUMN `is_public` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '' AFTER `name`;");
	}

	public function down()
	{
		echo "m151030_090403_tag_is_public does not support migration down.\n";
		return false;
	}

}