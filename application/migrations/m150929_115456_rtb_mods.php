<?php

class m150929_115456_rtb_mods extends CDbMigration
{
	public function up()
	{
		$this->dropTable("campaigns_creative_tags");
		$this->execute("ALTER TABLE `campaigns_creative`
			ADD COLUMN `size` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '' AFTER `type`;
		");
	}

	public function down()
	{
		echo "m150929_115456_rtb_mods does not support migration down.\n";
		return false;
	}
}