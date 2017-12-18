<?php

class m140112_160727_update_countries_alter_cities extends CDbMigration
{
	public function safeUp()
	{
        $this->renameTable('country', 'countries');
        $this->createIndex('countries_code_unique', 'countries', 'code', true);
        $this->execute("ALTER TABLE `cities` MODIFY country_id INT UNSIGNED NOT NULL;");

        $this->dropForeignKey('city_country', 'cities');
        $this->execute("
            ALTER TABLE `cities`
	        ADD CONSTRAINT `cities_country_fk`
	        FOREIGN KEY (`country_id`)
	        REFERENCES `countries` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
        ");

        $this->renameTable('ncountry_include', 'news_countries');
        $this->dropForeignKey('ncountry_news', 'news_countries');
        $this->dropForeignKey('ncountry_country', 'news_countries');

        $this->execute("
            ALTER TABLE `news_countries`
	        ADD CONSTRAINT `news_countries_news_fk`
	        FOREIGN KEY (`news_id`)
	        REFERENCES `news` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `news_countries`
	        ADD CONSTRAINT `news_countries_countries_fk`
	        FOREIGN KEY (`country_id`)
	        REFERENCES `countries` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
        ");

        $this->renameTable('nc_except', 'news_cities');
        $this->dropForeignKey('cities_except', 'news_cities');
        $this->dropForeignKey('news_except', 'news_cities');

        $this->execute("
            ALTER TABLE `news_cities`
	        ADD CONSTRAINT `news_cities_news_fk`
	        FOREIGN KEY (`news_id`)
	        REFERENCES `news` (`id`) ON UPDATE NO ACTION ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `news_cities`
	        ADD CONSTRAINT `news_cities_cities_fk`
	        FOREIGN KEY (`city_id`)
	        REFERENCES `cities` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
        ");

        $this->addPrimaryKey('news_cities_pk', 'news_cities', 'news_id, city_id');
	}

	public function safeDown()
	{
        $this->dropForeignKey('cities_country_fk', 'cities');
        $this->execute("
            ALTER TABLE `cities`
	        ADD CONSTRAINT `city_country`
	        FOREIGN KEY (`country_id`)
	        REFERENCES `countries` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");
        $this->renameTable('countries', 'country');
        $this->dropIndex('countries_code_unique', 'country');
        $this->renameTable('news_countries', 'ncountry_include');
        $this->renameTable('news_cities', 'nc_except');

        $this->dropForeignKey('news_countries_news_fk', 'ncountry_include');
        $this->dropForeignKey('news_countries_countries_fk', 'ncountry_include');

        $this->execute('ALTER TABLE `ncountry_include` DROP PRIMARY KEY;');
        $this->execute("
            ALTER TABLE `ncountry_include`
	        ADD CONSTRAINT `ncountry_news`
	        FOREIGN KEY (`news_id`)
	        REFERENCES `news` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `ncountry_include`
	        ADD CONSTRAINT `ncountry_country`
	        FOREIGN KEY (`country_id`)
	        REFERENCES `country` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->dropForeignKey('news_cities_news_fk', 'nc_except');
        $this->dropForeignKey('news_cities_cities_fk', 'nc_except');

        $this->execute('ALTER TABLE `nc_except` DROP PRIMARY KEY;');
        $this->execute("
            ALTER TABLE `nc_except`
	        ADD CONSTRAINT `news_except`
	        FOREIGN KEY (`news_id`)
	        REFERENCES `news` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");

        $this->execute("
            ALTER TABLE `nc_except`
	        ADD CONSTRAINT `cities_except`
	        FOREIGN KEY (`city_id`)
	        REFERENCES `cities` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
        ");
	}
}