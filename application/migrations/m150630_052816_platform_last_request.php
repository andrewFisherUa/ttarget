<?php

class m150630_052816_platform_last_request extends CDbMigration
{
	public function up()
	{
        $this->execute("DROP TABLE IF EXISTS `platforms_teaser_block_status_log`;");
        $this->execute("ALTER TABLE `platforms`
            ADD COLUMN `last_request_date` DATETIME NOT NULL AFTER `visits_count`,
            ADD COLUMN `lr_notify_date` DATETIME NOT NULL AFTER `last_request_date`;");
        $this->execute("UPDATE `platforms` SET `last_request_date`=now();");
        $this->execute("UPDATE `platforms` SET `lr_notify_date`=`last_request_date`;");
	}

	public function down()
	{
		echo "m150630_052816_platform_last_request does not support migration down.\n";
		return false;
	}
}