<?php

class m150329_123826_offers_limits_fix extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `offers_users`
            CHANGE COLUMN `limits_per_dat` `limits_per_day` INT(10) UNSIGNED NOT NULL ;
        ");

        $this->execute("ALTER TABLE `offers_users`
          ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `limits_total`,
          ADD COLUMN `clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;
        ");

        $this->execute("ALTER TABLE `offers`
            ADD COLUMN `clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `is_deleted`,
            ADD COLUMN `actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `clicks`;
        ");
	}

	public function down()
	{
		echo "m150329_123826_offers_limits_fix does not support migration down.\n";
		return false;
	}
}