<?php

class m150723_105718_add_document_field_rtb extends CDbMigration
{
	public function up()
	{
		$this->addColumn('campaigns_creative', 'document', "VARCHAR(50) CHARACTER SET latin1 NOT NULL");
	}

	public function down()
	{
		echo "m150723_105718_add_document_field_rtb does not support migration down.\n";
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
