<?php

class m150409_150547_offers_encoding_fix extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers` MODIFY COLUMN `name` VARCHAR(255) COLLATE utf8_general_ci NOT NULL");
		$this->execute("ALTER TABLE `offers` MODIFY COLUMN `description` VARCHAR(255) COLLATE utf8_general_ci NOT NULL");
	}

	public function down()
	{
		echo "m150409_150547_offers_encoding_fix does not support migration down.\n";
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