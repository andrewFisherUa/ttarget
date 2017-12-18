<?php

class m140201_100839_geo_new extends CDbMigration
{
	public function safeUp()
	{
        $this->insert("countries", array('id' => 2, 'name' => 'Украина', 'code' => 'UA'));
		$this->insert("countries", array('id' => 3, 'name' => 'Беларусь', 'code' => 'BY'));

        // переносим города новостей с максимальным id в кампании
        $this->execute("set foreign_key_checks=0;");

        $this->execute("alter table news_cities add is_old tinyint not null;");
        $this->execute("update news_cities set is_old = 1;");
        $this->execute("insert into news_cities select c.id, nc.city_id, 0 from campaigns c
            left join news_cities nc on nc.news_id = (select n.id from news n where n.campaign_id = c.id ORDER BY n.ID DESC LIMIT 1) and is_old = 1
            where nc.news_id is not null;"
        );
        $this->execute("delete from news_cities where is_old = 1;");
        $this->execute("alter table news_cities drop is_old;");

        // переносим страны новостей с максимальным id в кампании
        $this->execute("alter table news_countries add is_old tinyint not null;");
        $this->execute("update news_countries set is_old = 1;");
        $this->execute("insert into news_countries select c.id, nc.country_id, 0 from campaigns c
            left join news_countries nc on nc.news_id = (select n.id from news n where n.campaign_id = c.id ORDER BY n.ID DESC LIMIT 1) and is_old = 1
            where nc.news_id is not null;"
        );
        $this->execute("delete from news_countries where is_old = 1;");
        $this->execute("alter table news_countries drop is_old;");

        $this->renameTable("news_countries","campaigns_countries");
        $this->renameTable("news_cities","campaigns_cities");

        $this->execute("ALTER TABLE `campaigns_cities` DROP FOREIGN KEY `news_cities_cities_fk` ;");
        $this->execute("ALTER TABLE `campaigns_cities` DROP FOREIGN KEY `news_cities_news_fk` ;");
        $this->execute("ALTER TABLE `campaigns_cities` CHANGE COLUMN `news_id` `campaign_id` INT(10) UNSIGNED NOT NULL ,
            ADD CONSTRAINT `campaigns_cities_cities_fk`
            FOREIGN KEY (`city_id` )
            REFERENCES `cities` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
            ADD CONSTRAINT `campaigns_cities_campaigns_fk`
            FOREIGN KEY (`campaign_id` )
            REFERENCES `campaigns` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
            , DROP PRIMARY KEY
            , ADD PRIMARY KEY (`campaign_id`, `city_id`)
            , DROP INDEX `news_except_idx`
            , ADD INDEX `news_except_idx` (`campaign_id` ASC) ;"
        );
        $this->execute("ALTER TABLE `campaigns_countries` DROP FOREIGN KEY `news_countries_countries_fk` ;");
        $this->execute("ALTER TABLE `campaigns_countries` DROP FOREIGN KEY `news_countries_news_fk` ;");
        $this->execute("ALTER TABLE `campaigns_countries` CHANGE COLUMN `news_id` `campaign_id` INT(10) UNSIGNED NOT NULL ,
            ADD CONSTRAINT `campaigns_countries_countries_fk`
            FOREIGN KEY (`country_id` )
            REFERENCES `countries` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
            ADD CONSTRAINT `campaigns_countries_campaigns_fk`
            FOREIGN KEY (`campaign_id` )
            REFERENCES `campaigns` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
            , DROP PRIMARY KEY
            , ADD PRIMARY KEY (`campaign_id`, `country_id`)
            , DROP INDEX `ncountry_news`
            , ADD INDEX `ncountry_news` (`campaign_id` ASC) ;"
        );

        $this->execute("set foreign_key_checks=1;");
	}

	public function safeDown()
	{
        $this->delete("countries", 'id=2');
        $this->delete("countries", 'id=3');

        // нифига не переносим
        $this->truncateTable("campaigns_cities");
        $this->truncateTable("campaigns_countries");

        $this->execute("ALTER TABLE `campaigns_cities` DROP FOREIGN KEY `campaigns_cities_cities_fk` ;");
        $this->execute("ALTER TABLE `campaigns_cities` DROP FOREIGN KEY `campaigns_cities_campaigns_fk` ;");
        $this->execute("ALTER TABLE `campaigns_cities` CHANGE COLUMN `campaign_id` `news_id` INT(10) UNSIGNED NOT NULL ,
            ADD CONSTRAINT `news_cities_cities_fk`
            FOREIGN KEY (`city_id` )
            REFERENCES `cities` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
            ADD CONSTRAINT `news_cities_news_fk`
            FOREIGN KEY (`news_id` )
            REFERENCES `news` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
            , DROP PRIMARY KEY
            , ADD PRIMARY KEY (`news_id`, `city_id`)
            , DROP INDEX `news_except_idx`
            , ADD INDEX `news_except_idx` (`news_id` ASC) ;"
        );

        $this->execute("ALTER TABLE `campaigns_countries` DROP FOREIGN KEY `campaigns_countries_countries_fk` ;");
        $this->execute("ALTER TABLE `campaigns_countries` DROP FOREIGN KEY `campaigns_countries_campaigns_fk` ;");
        $this->execute("ALTER TABLE `campaigns_countries` CHANGE COLUMN `campaign_id` `news_id` INT(10) UNSIGNED NOT NULL ,
            ADD CONSTRAINT `news_countries_countries_fk`
            FOREIGN KEY (`country_id` )
            REFERENCES `countries` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
            ADD CONSTRAINT `news_countries_news_fk`
            FOREIGN KEY (`news_id` )
            REFERENCES `news` (`id` )
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
            , DROP PRIMARY KEY
            , ADD PRIMARY KEY (`news_id`, `country_id`)
            , DROP INDEX `ncountry_news`
            , ADD INDEX `ncountry_news` (`news_id` ASC) ;"
        );

        $this->renameTable("campaigns_countries","news_countries");
        $this->renameTable("campaigns_cities","news_cities");

	}
}