<?php

class m140222_113659_billing_decimal extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `billing_income` CHANGE COLUMN `sum` `sum` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0';");
	}

	public function down()
	{
		echo "m140222_113659_billing_decimal does not support migration down.\n";
		return false;
	}
}