<?php

class m150318_233124_offers_limits extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `offers` ADD COLUMN `use_limits` TINYINT(4) NOT NULL DEFAULT '0'");
		$this->execute("ALTER TABLE `offers` ADD COLUMN `limits_per_day` INTEGER(10) NOT NULL DEFAULT '0'");
		$this->execute("ALTER TABLE `offers` ADD COLUMN `limits_per_months` INTEGER(10) NOT NULL DEFAULT '0'");
		$this->execute("ALTER TABLE `offers` ADD COLUMN `limits_total` INTEGER(10) NOT NULL DEFAULT '0'");
	}

	public function down()
	{
		echo "m150318_233124_offers_limits does not support migration down.\n";
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