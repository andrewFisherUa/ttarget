<?php

class m140113_021242_create_repoort_by_teaser extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("
            CREATE TABLE `report_daily_by_teaser_and_platform` (
                `teaser_id` INT UNSIGNED NOT NULL,
                `platform_id` INT UNSIGNED NOT NULL,
                `date` DATE NOT NULL,
                `shows` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                `clicks` BIGINT UNSIGNED NOT NULL DEFAULT '0',
                PRIMARY KEY (`teaser_id`, `platform_id`, `date`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB;
        ");
	}

	public function safeDown()
	{
        $this->dropTable('report_daily_by_teaser_and_platform');
	}
}