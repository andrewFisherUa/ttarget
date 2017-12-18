<?php

class m140110_201439_rename_ip_blacklist extends CDbMigration
{
	public function safeUp()
	{
        $this->renameTable('ip_blacklist', 'ip_log');
	}

	public function safeDown()
	{
        $this->renameTable('ip_log', 'ip_blacklist');
	}
}