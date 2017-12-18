<?php

class m150912_185641_signin_platform extends CDbMigration
{
	public function up()
	{
		$this->execute("ALTER TABLE `platforms`
			ADD COLUMN `url` VARCHAR(2048) NOT NULL DEFAULT '' COMMENT '' AFTER `lr_notify_date`;
		");

		$this->execute("ALTER TABLE `users`
			ADD COLUMN `skype` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '' AFTER `passwd_token`,
			ADD COLUMN `phone` VARCHAR(16) NOT NULL DEFAULT '' COMMENT '' AFTER `skype`;
		");

		$this->execute("ALTER TABLE `teaser_db`.`users`
			CHANGE COLUMN `login` `login` VARCHAR(128) NOT NULL COMMENT '' ;
		");

		$this->execute("ALTER TABLE `users`
			ADD COLUMN `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '' AFTER `phone`;
		");
	}

	public function down()
	{
		echo "m150912_185641_signin_platform does not support migration down.\n";
		return false;
	}
}