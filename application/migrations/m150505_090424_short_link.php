<?php

class m150505_090424_short_link extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `short_link` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `eid` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
              `expire_date` date NOT NULL,
              `url` varchar(2048) CHARACTER SET latin1 NOT NULL,
              `target_type` enum('offer_user') CHARACTER SET latin1 NOT NULL DEFAULT 'offer_user',
              `target_id` int(10) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `eid` (`id`),
              UNIQUE KEY `target` (`target_type`,`target_id`),
              KEY `expire_date` (`expire_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

	}

	public function down()
	{
		echo "m150505_090424_short_link does not support migration down.\n";
		return false;
	}
}