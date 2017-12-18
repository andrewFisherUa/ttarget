<?php

class m150410_094157_offer_billing extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `teaser_db`.`billing_income`
            ADD COLUMN `source_type` ENUM('platform', 'webmaster') NOT NULL DEFAULT 'platform' AFTER `platform_id`,
            ADD COLUMN `source_id` INT(10) UNSIGNED NOT NULL AFTER `source_type`;");

        $this->execute("UPDATE `teaser_db`.`billing_income` SET `source_id` = `platform_id`;");

        $this->execute("ALTER TABLE `teaser_db`.`billing_income`
            DROP FOREIGN KEY `bill_p`;
            ALTER TABLE `teaser_db`.`billing_income`
            DROP COLUMN `platform_id`,
            DROP INDEX `bill_p_idx` ;");

        $this->execute("ALTER TABLE `teaser_db`.`billing_income`
            ADD INDEX `source_id` (`source_id` ASC, `source_type` ASC);");


	}

	public function down()
	{
		echo "m150410_094157_offer_billing does not support migration down.\n";
		return false;
	}
}