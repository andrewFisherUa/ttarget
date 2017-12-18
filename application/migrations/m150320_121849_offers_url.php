<?php

class m150320_121849_offers_url extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `offers` ADD COLUMN `url` VARCHAR(512) NOT NULL AFTER `limits_total`;");
	}

	public function down()
	{
		echo "m150320_121849_offers_url does not support migration down.\n";
		return false;
	}
}