<?php

class m150910_105147_campaigns_creative_google_click_view extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `campaigns_creative_view_goolge` (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `click_datetime` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `campaign_id` int(11) unsigned NOT NULL,
              `creative_id` int(11) unsigned NOT NULL DEFAULT '0',
              `ip` varchar(100) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

		$this->execute("CREATE TABLE `campaigns_creative_click_goolge` (
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
		echo "m150910_105147_campaigns_creative_google_click_view does not support migration down.\n";
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