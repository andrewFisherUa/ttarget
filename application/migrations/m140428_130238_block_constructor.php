<?php

class m140428_130238_block_constructor extends CDbMigration
{
	public function up()
	{
        $this->execute("CREATE TABLE `blocks` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `platform_id` int(10) unsigned NOT NULL,
              `size` varchar(45) DEFAULT NULL,
              `custom_horizontal_size` smallint(5) unsigned DEFAULT NULL,
              `custom_vertical_size` smallint(5) unsigned DEFAULT NULL,
              `horizontal_count` tinyint(3) unsigned DEFAULT NULL,
              `vertical_count` tinyint(3) unsigned DEFAULT NULL,
              `header_align` varchar(45) DEFAULT NULL,
              `font_name` varchar(45) DEFAULT NULL,
              `font_size` varchar(45) DEFAULT NULL,
              `font_color` varchar(45) DEFAULT NULL,
              `image_size` varchar(45) DEFAULT NULL,
              `header` tinyint(3) unsigned NOT NULL,
              PRIMARY KEY (`id`),
              KEY `blocks_platforms` (`platform_id`),
              CONSTRAINT `blocks_platforms` FOREIGN KEY (`platform_id`) REFERENCES `platforms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB;");
	}

	public function down()
	{
		$this->dropTable("blocks");
	}
}