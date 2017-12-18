<?php

class m150320_181220_offers_images extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers_images` ADD COLUMN `mime` VARCHAR(20) NOT NULL");
		$this->execute("ALTER TABLE `offers_images` ADD COLUMN `width` INTEGER(11) NOT NULL DEFAULT '0'");
		$this->execute("ALTER TABLE `offers_images` ADD COLUMN `height` INTEGER(11) NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150320_181220_offers_images does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}