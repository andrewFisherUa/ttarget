<?php

class m150312_130036_offers_user extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `users`
          CHANGE COLUMN `role` `role` ENUM('guest','user','admin','platform','webmaster') NOT NULL DEFAULT 'guest' ;
        ");

        $this->execute("ALTER TABLE `users`
            ADD COLUMN `contact_details` VARCHAR(1024) NULL AFTER `billing_details_type`,
            ADD COLUMN `created_date` DATETIME NULL AFTER `contact_details`,
            ADD COLUMN `lastlogin_date` DATETIME NULL AFTER `created_date`;
        ");
	}

	public function down()
	{
		echo "m150312_130036_offers_user does not support migration down.\n";
		return false;
	}
}