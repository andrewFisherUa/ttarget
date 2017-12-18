<?php

class m150612_160251_user_tokens extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `users` ADD COLUMN `passwd_token` VARCHAR(255) NULL DEFAULT NULL AFTER `login_token`;");
	}

	public function down()
	{
		echo "m150612_160251_user_tokens does not support migration down.\n";
		return false;
	}
}