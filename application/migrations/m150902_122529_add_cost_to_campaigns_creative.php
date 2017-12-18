<?php

class m150902_122529_add_cost_to_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'cost', "INT(10) unsigned NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150902_122529_add_price_to_campaigns_creative does not support migration down.\n";
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