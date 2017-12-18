<?php

class m150312_230344_offers_basic_structure extends CDbMigration
{
	public function up()
	{
		$this->execute("DROP TABLE IF EXISTS `offers`;");
		
		$this->execute("CREATE TABLE `offers` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `action_id` int(10) unsigned NOT NULL,
                `name` varchar(255) CHARACTER SET latin1 NOT NULL,
                `description` varchar(255) CHARACTER SET latin1 NOT NULL,
                `payment` float NOT NULL DEFAULT '0' COMMENT 'выплата (от клиента)',
                `reward` float NOT NULL DEFAULT '0' COMMENT 'вознаграждение (вебмастеру)',
                `is_active` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Флаг активности',
                `date_start` datetime NOT NULL COMMENT 'Дата начала действия оффера',
                `date_end` datetime NOT NULL COMMENT 'Дата окончания действия оффера',
                `unique_ip` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Уникальные IP (учитываем ip для действий)',
                `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                `lead_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'в каком статусе приходят лиды - 0 - подтверждено, 1 - ожидание',
                PRIMARY KEY (`id`),
                KEY `action_id` (`action_id`),
                CONSTRAINT `offers_campaigns_actions_fk` FOREIGN KEY (`action_id`) REFERENCES `campaigns_actions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("CREATE TABLE `offers_users` (
                `offer_id` int(10) unsigned NOT NULL,
                `user_id` int(10) unsigned NOT NULL,
                `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'статусы: на модерации = 0 активная = 1 отклоненная = 2',
                `lead_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'в каком статусе приходят лиды: 0 - ожидание, 1  - подтверждено',
                PRIMARY KEY (`offer_id`,`user_id`),
                KEY `offer_id_idx` (`offer_id`),
                KEY `user_id_idx` (`user_id`),
                CONSTRAINT `offers_users_offer_id_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `offers_users_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("CREATE TABLE `offers_cities` (
                `offer_id` int(10) unsigned NOT NULL,
                `city_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`offer_id`,`city_id`),
                KEY `offer_id_idx` (`offer_id`),
                KEY `city_id_idx` (`city_id`),
                CONSTRAINT `offers_cities_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `offers_cities_fk1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("CREATE TABLE `offers_countries` (
                `offer_id` int(10) unsigned NOT NULL,
                `country_id` int(10) unsigned NOT NULL,
                PRIMARY KEY (`offer_id`,`country_id`),
                KEY `offer_id` (`offer_id`),
                KEY `country_id` (`country_id`),
                CONSTRAINT `offers_countries_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `offers_countries_fk1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
		
		$this->execute("CREATE TABLE `offers_images` (
                `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `offer_id` int(10) unsigned NOT NULL,
                `filename` varchar(255) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `offer_id` (`offer_id`),
                CONSTRAINT `offers_images_fk` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
	}

	public function down()
	{
		echo "m150312_230344_offers_basic_structure does not support migration down.\n";
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