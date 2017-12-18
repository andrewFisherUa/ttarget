<?php

class m131218_212210_add_is_deleted_flag extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('platforms', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        $this->addColumn('users', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        $this->addColumn('campaigns', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        $this->addColumn('news', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
        $this->addColumn('teasers', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function safeDown()
	{
        $this->dropColumn('platforms', 'is_deleted');
        $this->dropColumn('users', 'is_deleted');
        $this->dropColumn('campaigns', 'is_deleted');
        $this->dropColumn('news', 'is_deleted');
        $this->dropColumn('teasers', 'is_deleted');
	}
}