<?php

class m140107_072817_alter_ip_blacklist_table extends CDbMigration
{
	public function safeUp()
	{
        $this->truncateTable('ip_blacklist');

        $this->execute("ALTER TABLE `ip_blacklist` ADD COLUMN `date` DATE NOT NULL");
        $this->execute("ALTER TABLE `ip_blacklist` ADD COLUMN `news_id` INT UNSIGNED NOT NULL");
        $this->execute("ALTER TABLE `ip_blacklist` ADD COLUMN `platform_id` INT UNSIGNED NOT NULL");

        $this->createIndex('i_b_date_news_id_platform_id_idx', 'ip_blacklist', 'date,news_id,platform_id');
	}

	public function safeDown()
	{
        $this->dropIndex('i_b_date_news_id_platform_id_idx', 'ip_blacklist');

        $this->dropColumn('ip_blacklist', 'date');
        $this->dropColumn('ip_blacklist', 'news_id');
        $this->dropColumn('ip_blacklist', 'platform_id');
	}
}