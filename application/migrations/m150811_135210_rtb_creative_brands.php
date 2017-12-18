<?php

class m150811_135210_rtb_creative_brands extends CDbMigration
{
	public function up()
	{
		$this->execute("CREATE TABLE `campaigns_creative_yandex_brands` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `brand_id` int(10) unsigned NOT NULL,
						  `name` varchar(255) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->execute("CREATE TABLE `campaigns_creative_brands_relations` (
						  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `creative_id` int(10) unsigned NOT NULL,
						  `brand_id` int(10) unsigned NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

		$this->execute("ALTER TABLE `campaigns_creative_brands_relations` ADD INDEX `creative_id` (`creative_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_brands_relations` ADD INDEX `brand_id` (`brand_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_brands_relations` ADD UNIQUE `creative_id_brand_id_unique` (`creative_id`, `brand_id`)");
		$this->execute("ALTER TABLE `campaigns_creative_brands_relations` ADD CONSTRAINT `campaigns_creative_brands_relations_fk` FOREIGN KEY (`creative_id`) REFERENCES `campaigns_creative` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
		$this->execute("ALTER TABLE `campaigns_creative_brands_relations` ADD CONSTRAINT `campaigns_creative_brands_relations_fk1` FOREIGN KEY (`brand_id`) REFERENCES `campaigns_creative_yandex_brands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");

	}

	public function down()
	{
		echo "m150811_135210_rtb_creative_brands does not support migration down.\n";
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