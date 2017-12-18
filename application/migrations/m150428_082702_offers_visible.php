<?php

class m150428_082702_offers_visible extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers` ADD COLUMN `visible` TINYINT(4) NOT NULL DEFAULT '1' COMMENT 'видимость заявки для вебмастера'");
	}

	public function down()
	{
		echo "m150428_082702_offers_visible does not support migration down.\n";
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