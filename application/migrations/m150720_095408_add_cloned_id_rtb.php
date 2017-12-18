<?php

class m150720_095408_add_cloned_id_rtb extends CDbMigration
{
	public function safeUp()
	{
		$this->addColumn('campaigns_creative', 'cloned_id', "INT(11) DEFAULT NULL");
	}

	public function safeDown()
	{
		$this->dropColumn('campaigns_creative', 'cloned_id');
	}
}
