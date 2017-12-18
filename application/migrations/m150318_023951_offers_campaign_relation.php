<?php

class m150318_023951_offers_campaign_relation extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers` ADD COLUMN `campaign_id` INTEGER(10) UNSIGNED NOT NULL AFTER `id`");
		$this->execute("ALTER TABLE `offers` ADD INDEX  (`campaign_id`)");
		$this->execute("ALTER TABLE `offers` ADD CONSTRAINT `offers_fk` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
	}

	public function down()
	{
		echo "m150318_023951_offers_campaign_relation does not support migration down.\n";
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