<?php

class m140317_103929_platform_billing_details extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE `platforms` ADD COLUMN `is_vat` TINYINT UNSIGNED NOT NULL DEFAULT 0  AFTER `hosts` ;");

        $this->execute("ALTER TABLE `users`
            ADD COLUMN `is_auto_withdrawal` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            ADD COLUMN `billing_details_text` TEXT NULL,
            ADD COLUMN `billing_details_type` VARCHAR(45) NULL;"
        );

	}

	public function safeDown()
	{
        $this->dropColumn("users", "billing_details_type");
        $this->dropColumn("users", "billing_details_text");
        $this->dropColumn("users", "is_auto_withdrawal");
        $this->dropColumn("platforms", "is_vat");
	}
}