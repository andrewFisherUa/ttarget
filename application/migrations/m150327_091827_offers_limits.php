<?php

class m150327_091827_offers_limits extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `offers`
            DROP COLUMN `limits_per_months`,
            CHANGE COLUMN `payment` `payment` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT 'выплата (от клиента)' ,
            CHANGE COLUMN `reward` `reward` DECIMAL(10,2) NOT NULL DEFAULT '0' COMMENT 'вознаграждение (вебмастеру)' ,
            CHANGE COLUMN `limits_per_day` `limits_per_day` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `limits_total` `limits_total` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
            ADD COLUMN `user_limits_per_day` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `limits_total`,
            ADD COLUMN `user_limits_total` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `user_limits_per_day`;
        ");

        $this->execute("ALTER TABLE `offers_users`
            ADD COLUMN `limits_per_dat` INT(10) UNSIGNED NOT NULL AFTER `description`,
            ADD COLUMN `limits_total` INT(10) UNSIGNED NOT NULL AFTER `limits_per_dat`;
        ");
	}

	public function down()
	{
		echo "m150327_091827_offers_limits does not support migration down.\n";
		return false;
	}
}