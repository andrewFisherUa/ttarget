<?php

class m150903_083035_campaigns_creative_click_yandex extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `campaigns_creative_click_yandex` (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `click_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `campaign_id` int(11) unsigned NOT NULL,
              `creative_id` int(11) unsigned NOT NULL DEFAULT '0',
              `ip` varchar(100) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m150903_083035_campaigns_creative_click_yandex does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}