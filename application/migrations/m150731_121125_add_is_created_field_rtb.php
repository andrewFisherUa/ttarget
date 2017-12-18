<?php

class m150731_121125_add_is_created_field_rtb extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'is_created', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150731_121125_add_is_created_field_rtb does not support migration down.\n";
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
