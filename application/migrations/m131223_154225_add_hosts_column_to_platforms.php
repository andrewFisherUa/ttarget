<?php

class m131223_154225_add_hosts_column_to_platforms extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('platforms', 'hosts', "TEXT");
        $this->execute('UPDATE platforms SET hosts = server');
	}

	public function safeDown()
	{
        $this->dropColumn('platforms', 'hosts');
	}
}