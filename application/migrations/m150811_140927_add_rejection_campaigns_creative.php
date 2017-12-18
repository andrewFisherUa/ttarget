<?php

class m150811_140927_add_rejection_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'rejection', "TEXT DEFAULT NULL");
	}

	public function down()
	{
		echo "m150811_140927_add_rejection_campaigns_creative does not support migration down.\n";
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