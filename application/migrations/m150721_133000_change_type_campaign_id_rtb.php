<?php

class m150721_133000_change_type_campaign_id_rtb extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `campaigns_creative` MODIFY COLUMN campaign_id INT(10) unsigned");
	}

	public function down()
	{
		echo "m150721_133000_change_type_campaign_id_rtb does not support migration down.\n";
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
