<?php

class m150609_060057_billing_fix_revert extends CDbMigration
{
	public function up()
	{
        $bi = $this->getDbConnection()->getSchema()->getTable("billing_income");
        $bo = $this->getDbConnection()->getSchema()->getTable("billing_outgoing");
        if(isset($bi->columns["source_name"])) {
            $this->dropColumn("billing_income", "source_name");
        }
        if(isset($bo->columns["client_name"])) {
            $this->dropColumn("billing_outgoing", "client_name");
        }
	}

	public function down()
	{
		echo "m150609_060057_billing_fix_revert does not support migration down.\n";
		return false;
	}
}