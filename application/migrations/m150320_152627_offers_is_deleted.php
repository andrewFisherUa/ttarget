<?php

class m150320_152627_offers_is_deleted extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers` ADD COLUMN `is_deleted` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0'");
		$this->execute("ALTER TABLE `offers` DROP FOREIGN KEY `offers_fk1`");
		$this->execute("ALTER TABLE `offers` DROP INDEX `action_id`");
		$this->execute("ALTER TABLE `offers` ADD INDEX `action_id` (`action_id`)");
		$this->execute("ALTER TABLE `offers` ADD CONSTRAINT `offers_fk1` FOREIGN KEY (`action_id`) REFERENCES `campaigns_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
	}

	public function down()
	{
		echo "m150320_152627_offers_is_deleted does not support migration down.\n";
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