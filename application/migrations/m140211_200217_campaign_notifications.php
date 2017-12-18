<?php

class m140211_200217_campaign_notifications extends CDbMigration
{

//	public function down()
//	{
//		echo "m140211_200217_campaign_notifications does not support migration down.\n";
//		return false;
//	}

	public function safeUp()
	{
        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `is_notified` TINYINT UNSIGNED NOT NULL DEFAULT '0';");
        $this->execute("UPDATE `campaigns` SET `is_notified` = 1 WHERE `date_end` < CURDATE();");
	}

	public function safeDown()
	{
        $this->dropColumn("campaigns", "is_notified");
	}
}