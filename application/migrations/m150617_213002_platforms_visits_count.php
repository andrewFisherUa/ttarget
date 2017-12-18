<?php

class m150617_213002_platforms_visits_count extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `platforms` ADD COLUMN `visits_count` INTEGER(11) UNSIGNED NOT NULL DEFAULT '0'");

	}

	public function down()
	{
		echo "m150617_213002_platforms_visits_count does not support migration down.\n";
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