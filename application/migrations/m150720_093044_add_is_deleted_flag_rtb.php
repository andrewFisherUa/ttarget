<?php

class m150720_093044_add_is_deleted_flag_rtb extends CDbMigration
{
	public function safeUp()
	{
		$this->addColumn('campaigns_creative', 'is_deleted', "TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'");
	}

	public function safeDown()
	{
		$this->dropColumn('campaigns_creative', 'is_deleted');
	}
}
