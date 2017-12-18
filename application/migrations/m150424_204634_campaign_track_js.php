<?php

class m150424_204634_campaign_track_js extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `campaigns` ADD COLUMN `track_js` TEXT");
		$this->execute("ALTER TABLE `campaigns` ADD COLUMN `track_js_compiled` TEXT");
	}

	public function down()
	{
		echo "m150424_204634_campaign_track_js does not support migration down.\n";
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