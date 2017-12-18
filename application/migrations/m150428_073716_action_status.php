<?php

class m150428_073716_action_status extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `actions_log`
            CHANGE COLUMN `is_declined` `status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'статусы: 0 - ожидание, 1 - подтверждено, 2 - отклонено';
        ");
        $this->execute("UPDATE `actions_log` SET `status` = 2 WHERE `status` = 1;");
        $this->execute("UPDATE `actions_log` SET `status` = 1 WHERE `status` = 0;");





        $this->execute("ALTER TABLE `report_daily_by_offer`
            CHANGE COLUMN `actions` `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `declined_actions` `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");

        $this->execute("ALTER TABLE `report_daily_by_offer_user`
            CHANGE COLUMN `actions` `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `declined_actions` `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");

        $this->execute("ALTER TABLE `offers_users`
            CHANGE COLUMN `actions` `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `declined_actions` `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ,
            ADD COLUMN `offers_moderation_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `offers_declined_actions`;
        ");

        $this->execute("ALTER TABLE `offers`
            CHANGE COLUMN `actions` `offers_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ,
            CHANGE COLUMN `declined_actions` `offers_declined_actions` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");
	}

	public function down()
	{
		echo "m150428_073716_action_status does not support migration down.\n";
		return false;
	}
}