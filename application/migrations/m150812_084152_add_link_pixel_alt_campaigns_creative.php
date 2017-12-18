<?php

class m150812_084152_add_link_pixel_alt_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'link', "VARCHAR(255) NOT NULL");
		$this->addColumn('campaigns_creative', 'pixel', "VARCHAR(255) NOT NULL");
		$this->addColumn('campaigns_creative', 'alt', "VARCHAR(255) NOT NULL");
	}

	public function down()
	{
		echo "m150812_084152_add_link_pixel_alt_campaigns_creative does not support migration down.\n";
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