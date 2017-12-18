<?php

class m151217_074107_segments extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `segments` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `parent_id` int(10) unsigned DEFAULT NULL,
              `name` varchar(255) NOT NULL,
              `path` varchar(2048) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `segments_segments_idx` (`parent_id`),
              CONSTRAINT `segments_segments` FOREIGN KEY (`parent_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("CREATE TABLE `sessions_segments` (
              `uid` char(32) NOT NULL,
              `segment_id` int(10) unsigned NOT NULL,
              `count` smallint(5) unsigned NOT NULL DEFAULT '1',
              `last_date` date NOT NULL,
              PRIMARY KEY (`uid`,`segment_id`)
            ) ENGINE=InnoDB
        ");

        $this->execute("CREATE TABLE `pages` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `url` varchar(2048) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("CREATE TABLE `pages_segments` (
              `page_id` int(10) unsigned NOT NULL,
              `segment_id` int(10) unsigned NOT NULL,
              PRIMARY KEY (`page_id`,`segment_id`),
              KEY `pages_segments_segments_idx` (`segment_id`),
              CONSTRAINT `pages_segments_pages` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `pages_segments_segments` FOREIGN KEY (`segment_id`) REFERENCES `segments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;
        ");
	}

	public function down()
	{
		echo "m151217_074107_segments does not support migration down.\n";
		return false;
	}
}