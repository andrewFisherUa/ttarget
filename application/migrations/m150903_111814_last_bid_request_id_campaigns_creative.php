<?php

class m150903_111814_last_bid_request_id_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'last_bid_request_id', "INT(11) UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150903_111814_last_bid_request_id_campaigns_creative does not support migration down.\n";
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