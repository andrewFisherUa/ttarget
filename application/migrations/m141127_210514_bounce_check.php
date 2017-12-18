<?php

class m141127_210514_bounce_check extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `bounce_check` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `actions`;");
        $this->execute("ALTER TABLE `tracks`
ADD COLUMN `teaser_id` INT UNSIGNED NOT NULL,
ADD COLUMN `bounce_check` INT UNSIGNED NULL;");
        $this->execute("ALTER TABLE `report_daily`
ADD COLUMN `bounces` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;");
        $this->execute("ALTER TABLE `campaigns`
CHANGE COLUMN `bounce_check` `bounce_check` SMALLINT(5) UNSIGNED NULL AFTER `cost_type`,
CHANGE COLUMN `shows` `shows` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `bounce_check`,
CHANGE COLUMN `clicks` `clicks` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' AFTER `shows`,
ADD COLUMN `bounces` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AFTER `actions`;
");
        $this->execute("UPDATE `campaigns` SET `bounces` = `clicks`");

        $this->execute("CREATE TABLE `bounce_log` (
  `campaign_id` int(10) unsigned NOT NULL,
  `unix_timestamp` int(10) unsigned NOT NULL,
  `total` int(10) unsigned NOT NULL DEFAULT '0',
  `verified` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`unix_timestamp`,`campaign_id`)
) ENGINE=InnoDB;");

        $this->execute("ALTER TABLE `campaigns` ADD COLUMN `bounce_rate_diff` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0;");

	}

	public function down()
	{
		echo "m141127_210514_bounce_check does not support migration down.\n";
		return false;
	}
}