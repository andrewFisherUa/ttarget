<?php

class m150528_143535_offers_clicks extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `report_daily_by_offer`
            CHANGE COLUMN `clicks` `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");
        $this->execute("ALTER TABLE `report_daily_by_offer_user`
            CHANGE COLUMN `clicks` `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");
        $this->execute("ALTER TABLE `offers`
            CHANGE COLUMN `clicks` `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");
        $this->execute("ALTER TABLE `offers_users`
            CHANGE COLUMN `clicks` `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' ;
        ");
        $this->execute("ALTER TABLE `campaigns` 
            ADD COLUMN `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `declined_actions`;
        ");
        $this->execute("UPDATE `campaigns` c SET `offers_clicks` = (SELECT IFNULL(SUM(`offers_clicks`), 0) FROM `offers` o WHERE o.campaign_id = c.id)");

        $this->execute("ALTER TABLE `report_daily_by_campaign`
            ADD COLUMN `offers_clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `clicks_without_externals`;
        ");

        $this->execute("
            INSERT INTO `report_daily_by_campaign`(`campaign_id`, `date`, `offers_clicks`)
                SELECT * FROM (SELECT o.`campaign_id`, ro.`date`, SUM(ro.`offers_clicks`) as asdf FROM `report_daily_by_offer` ro
                LEFT JOIN `offers` o ON o.`id` = ro.`offer_id`
                WHERE ro.`offers_clicks` > 0
                GROUP BY o.`campaign_id`, ro.`date`) a
            ON DUPLICATE KEY UPDATE `offers_clicks` = a.asdf;
        ");

        $this->dropTable("shows");
        $this->dropTable("fake_clicks");

	}

	public function down()
	{
		echo "m150528_143535_offers_clicks does not support migration down.\n";
		return false;
	}
}