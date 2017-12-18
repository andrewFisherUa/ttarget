<?php

class m141222_110153_user_access_token extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `users` ADD COLUMN `login_token` VARCHAR(255) NULL;");
	}

	public function down()
	{
		echo "m141222_110153_user_access_token does not support migration down.\n";
		return false;
	}
}