<?php

class m140430_181707_new_blocks extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `blocks` ADD COLUMN `external_border_color` VARCHAR(45) NULL  AFTER `header` , ADD COLUMN `external_border_width` SMALLINT UNSIGNED NULL  AFTER `header` , ADD COLUMN `internal_border_color` VARCHAR(45) NULL  AFTER `external_border_color` , ADD COLUMN `internal_border_width` SMALLINT UNSIGNED NULL  AFTER `external_border_color` ;");
        $this->execute("ALTER TABLE `blocks` CHARACTER SET = utf8 ;");
        $this->execute("ALTER TABLE `blocks` ADD COLUMN `html` TEXT NOT NULL  AFTER `internal_border_color` ;");
	}

	public function down()
	{
		echo "m140430_181707_new_blocks does not support migration down.\n";
		return false;
	}
}