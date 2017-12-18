<?php

class m150827_074855_user_session_mods extends CDbMigration
{
	public function up()
	{
        $this->dropTable("user_session_log");
        $this->dropTable('user_session');
        $this->execute("CREATE TABLE `sessions` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `uid` varchar(50) CHARACTER SET latin1 NOT NULL,
              `created_date` datetime NOT NULL,
              `last_date` datetime NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uuid` (`uid`)
            ) ENGINE=InnoDB;
        ");

        $this->execute("CREATE TABLE `sessions_geo` (
              `session_id` int(10) unsigned NOT NULL,
              `coutry_code` char(2) NOT NULL DEFAULT '',
              `city_id` int(10) unsigned NOT NULL DEFAULT '0',
              `last_date` datetime NOT NULL,
              PRIMARY KEY (`session_id`,`coutry_code`,`city_id`),
              CONSTRAINT `sessions_geo_sessions` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

        $this->execute("CREATE TABLE `sessions_tags` (
              `session_id` int(10) unsigned NOT NULL,
              `tag_id` int(10) unsigned NOT NULL,
              `count` int(10) unsigned NOT NULL DEFAULT '1',
              `last_date` datetime NOT NULL,
              PRIMARY KEY (`session_id`,`tag_id`),
              KEY `session_tag_tags_idx` (`tag_id`),
              CONSTRAINT `session_tags_sessions` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `session_tags_tags` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");

	}

	public function down()
	{
		echo "m150827_074855_user_session_mods does not support migration down.\n";
		return false;
	}
}