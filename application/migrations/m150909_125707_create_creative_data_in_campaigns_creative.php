<?php

class m150909_125707_create_creative_data_in_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'creative_data', "TEXT DEFAULT NULL");
	}

	public function down()
	{
		echo "m150909_125707_create_creative_data_in_campaigns_creative does not support migration down.\n";
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