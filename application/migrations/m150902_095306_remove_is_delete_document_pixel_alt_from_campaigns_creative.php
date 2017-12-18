<?php

class m150902_095306_remove_is_delete_document_pixel_alt_from_campaigns_creative extends CDbMigration
{
	public function up()
	{
		$this->dropColumn('campaigns_creative', 'is_deleted');
		$this->dropColumn('campaigns_creative', 'document');
		$this->dropColumn('campaigns_creative', 'pixel');
		$this->dropColumn('campaigns_creative', 'alt');
		$this->dropColumn('campaigns_creative', 'cloned_id');
	}

	public function down()
	{
		echo "m150902_095306_remove_is_delete_document_pixel_alt_from_campaigns_creative does not support migration down.\n";
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