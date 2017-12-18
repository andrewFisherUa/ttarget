<?php

class m141106_140847_teaser_is_external extends CDbMigration
{
	public function up()
	{
        // restore tag links foreign keys
        // teaser_tags
        $this->execute("DELETE tt from teasers_tags tt left join teasers t on tt.teaser_id = t.id where t.id is null;");
        $this->execute("ALTER TABLE `teaser_db`.`teasers_tags` ENGINE = InnoDB
            , DROP INDEX `teasers_tags_tags`
            , DROP INDEX `teasers_tags_teasers` ;
        ");
        $this->execute("ALTER TABLE `teaser_db`.`teasers_tags`
              ADD CONSTRAINT `teasers_tags_tags`
              FOREIGN KEY (`tag_id` )
              REFERENCES `teaser_db`.`tags` (`id` )
              ON DELETE CASCADE
              ON UPDATE CASCADE,
              ADD CONSTRAINT `teasers_tags_teasers`
              FOREIGN KEY (`teaser_id` )
              REFERENCES `teaser_db`.`teasers` (`id` )
              ON DELETE CASCADE
              ON UPDATE CASCADE
            , ADD INDEX `teasers_tags_tags` (`tag_id` ASC)
            , ADD INDEX `teasers_tags_teasers` (`teaser_id` ASC) ;
        ");
        // platforms_tags
        $this->execute("DELETE pt FROM platforms_tags pt left join platforms p ON pt.platform_id = p.id where p.id is null;");
        $this->execute("ALTER TABLE `teaser_db`.`platforms_tags` ENGINE = InnoDB
            , DROP INDEX `platforms_tags_platforms`
            , DROP INDEX `platforms_tags_tags` ;
        ");
        $this->execute("ALTER TABLE `teaser_db`.`platforms_tags`
              ADD CONSTRAINT `platforms_tags_platforms`
              FOREIGN KEY (`platform_id` )
              REFERENCES `teaser_db`.`platforms` (`id` )
              ON DELETE CASCADE
              ON UPDATE CASCADE,
              ADD CONSTRAINT `platforms_tags_tags`
              FOREIGN KEY (`tag_id` )
              REFERENCES `teaser_db`.`tags` (`id` )
              ON DELETE CASCADE
              ON UPDATE CASCADE
            , ADD INDEX `platforms_tags_platforms` (`platform_id` ASC)
            , ADD INDEX `platforms_tags_tags` (`tag_id` ASC) ;
        ");
        // -----

        $this->execute("ALTER TABLE `teasers` ADD COLUMN `is_external` TINYINT NOT NULL DEFAULT 0  AFTER `is_deleted` ;");
        $this->execute("UPDATE teasers t join teasers_tags tt ON tt.teaser_id = t.id SET is_external = 1 WHERE tt.tag_id = 13;");
        $this->execute("DELETE FROM tags WHERE id = 13;");
	}

	public function down()
	{
		echo "m141106_140847_teaser_is_external does not support migration down.\n";
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