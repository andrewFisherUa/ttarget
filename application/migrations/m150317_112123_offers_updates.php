<?php

class m150317_112123_offers_updates extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers_users` ADD COLUMN `description` VARCHAR(255) NOT NULL COMMENT 'Описание заявки' ;");
		$this->execute("ALTER TABLE `offers_users` DROP INDEX `PRIMARY`");
		$this->execute("ALTER TABLE `offers_users` ADD COLUMN `id` INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
		$this->execute("ALTER TABLE `offers` ADD COLUMN `cookie_expires` INTEGER(10) NOT NULL DEFAULT 0 ;");
		$this->execute("ALTER TABLE `offers_users` DROP COLUMN `lead_status`");
	}

	public function down()
	{
		echo "m150317_112123_offers_updates does not support migration down.\n";
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