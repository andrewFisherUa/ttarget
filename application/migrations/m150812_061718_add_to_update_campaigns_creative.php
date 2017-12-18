<?php

class m150812_061718_add_to_update_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'to_update', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150812_061718_add_to_update_campaigns_creative does not support migration down.\n";
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