<?php

class m150325_092116_tracks_temp extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `tracks`
            DROP FOREIGN KEY `tracks_platforms`,
            DROP FOREIGN KEY `tracks_campaigns`;
        ");
        $this->execute("ALTER TABLE `tracks`
            CHANGE COLUMN `platform_id` `platform_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
            CHANGE COLUMN `teaser_id` `teaser_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
            ADD COLUMN `offer_user_id` INT(10) NULL DEFAULT NULL AFTER `bounce_check`,
            ADD COLUMN `action_eid` VARBINARY(255) NULL DEFAULT NULL AFTER `offer_user_id`,
            DROP INDEX `tracks_platforms` ,
            DROP INDEX `tracks_campaigns` ;
        ");
	}

	public function down()
	{
		echo "m150325_092116_tracks_temp does not support migration down.\n";
		return false;
	}
}