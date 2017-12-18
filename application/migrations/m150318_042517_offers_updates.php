<?php

class m150318_042517_offers_updates extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers_users` DROP FOREIGN KEY `offers_users_offer_id_fk`");
		$this->execute("ALTER TABLE `offers_users` ADD CONSTRAINT `offers_users_offer_id_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`)");
		
		$this->execute("ALTER TABLE `offers` MODIFY COLUMN `date_start` DATE NOT NULL COMMENT 'Дата начала действия оффера'");
		$this->execute("ALTER TABLE `offers` MODIFY COLUMN `date_end` DATE NOT NULL COMMENT 'Дата окончания действия оффера'");
		
		$this->execute("ALTER TABLE `offers` DROP FOREIGN KEY `offers_campaigns_actions_fk`");
		$this->execute("ALTER TABLE `offers` DROP INDEX `action_id`");
		$this->execute("ALTER TABLE `offers` ADD UNIQUE `action_id` (`action_id`)");
		$this->execute("ALTER TABLE `offers` ADD CONSTRAINT `offers_fk1` FOREIGN KEY (`action_id`) REFERENCES `campaigns_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
	}

	public function down()
	{
		echo "m150318_042517_offers_updates does not support migration down.\n";
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