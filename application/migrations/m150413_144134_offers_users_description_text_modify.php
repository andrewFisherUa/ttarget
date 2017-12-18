<?php

class m150413_144134_offers_users_description_text_modify extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers_users` MODIFY COLUMN `description` TEXT COLLATE utf8_general_ci NOT NULL COMMENT 'Описание заявки'");
	}

	public function down()
	{
		echo "m150413_144134_offers_users_description_text_modify does not support migration down.\n";
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