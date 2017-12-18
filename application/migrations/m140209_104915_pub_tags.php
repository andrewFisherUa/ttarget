<?php

class m140209_104915_pub_tags extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE  TABLE `teasers_tags` (
            `teaser_id` INT(10) UNSIGNED NOT NULL ,
            `tag_id` INT(10) UNSIGNED NOT NULL ,
            PRIMARY KEY (`teaser_id`, `tag_id`) ,
            INDEX `teasers_tags_teasers` (`teaser_id` ASC) ,
            INDEX `teasers_tags_tags` (`tag_id` ASC) ,
            CONSTRAINT `teasers_tags_teasers`
            FOREIGN KEY (`teaser_id` )
            REFERENCES `teasers` (`id` )
            ON DELETE CASCADE
            ON UPDATE CASCADE,
            CONSTRAINT `teasers_tags_tags`
            FOREIGN KEY (`tag_id` )
            REFERENCES `tags` (`id` )
            ON DELETE CASCADE
            ON UPDATE CASCADE);
        ");

        $this->execute("
            CREATE  TABLE `platforms_tags` (
            `platform_id` INT(10) UNSIGNED NOT NULL ,
            `tag_id` INT(10) UNSIGNED NOT NULL ,
            PRIMARY KEY (`platform_id`, `tag_id`) ,
            INDEX `platforms_tags_platforms` (`platform_id` ASC) ,
            INDEX `platforms_tags_tags` (`tag_id` ASC) ,
            CONSTRAINT `platforms_tags_platforms`
            FOREIGN KEY (`platform_id` )
            REFERENCES `platforms` (`id` )
            ON DELETE CASCADE
            ON UPDATE CASCADE,
            CONSTRAINT `platforms_tags_tags`
            FOREIGN KEY (`tag_id` )
            REFERENCES `tags` (`id` )
            ON DELETE CASCADE
            ON UPDATE CASCADE);
        ");

        $this->execute("INSERT INTO `platforms_tags` SELECT id AS platform_id, tag_id FROM platforms;");
        $this->execute("INSERT INTO `teasers_tags` SELECT teasers.id AS teaser_id, tags.id AS tag_id FROM teasers, tags;");

        $this->execute("ALTER TABLE `news` DROP FOREIGN KEY `news_tags`;");
        $this->execute("ALTER TABLE `news` DROP COLUMN `tag_id`, DROP INDEX `news_tags_idx`;");

        $this->execute("ALTER TABLE `platforms` DROP FOREIGN KEY `platform_tag`;");
        $this->execute("ALTER TABLE `platforms` DROP COLUMN `tag_id`, DROP INDEX `platform_tag_idx` ;");

	}

	public function safeDown()
	{
        echo "m140209_104915_pub_tags.php does not support migration down.\n";
        return false;
	}
}