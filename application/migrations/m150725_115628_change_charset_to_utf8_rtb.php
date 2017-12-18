<?php

class m150725_115628_change_charset_to_utf8_rtb extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `campaigns_creative` MODIFY COLUMN name VARCHAR(45) NOT NULL");
	}

	public function down()
	{
		echo "m150725_115628_change_charset_to_utf8_rtb does not support migration down.\n";
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
