<?php

class m150910_090823_google_bid_request extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `google_bid_request` (
			  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `json_data` TEXT NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
	}

	public function down()
	{
		echo "m150910_090823_google_bid_request does not support migration down.\n";
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