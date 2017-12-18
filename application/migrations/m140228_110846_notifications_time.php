<?php

class m140228_110846_notifications_time extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `notifications` CHANGE COLUMN `create_date` `create_date` DATETIME NOT NULL;");
	}

	public function down()
	{
		echo "m140228_110846_notifications_time does not support migration down.\n";
		return false;
	}

}