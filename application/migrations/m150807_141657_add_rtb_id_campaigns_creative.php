<?php

class m150807_141657_add_rtb_id_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'rtb_id', "INT(11) DEFAULT NULL");
	}

	public function down()
	{
		echo "m150807_141657_add_rtb_id_campaigns_creative does not support migration down.\n";
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