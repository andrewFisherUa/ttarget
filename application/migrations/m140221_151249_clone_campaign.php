<?php

class m140221_151249_clone_campaign extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE `teasers` ADD COLUMN `cloned_id` INT(10) UNSIGNED NULL  AFTER `is_deleted` ;");
        $this->execute("ALTER TABLE `teasers` ADD INDEX `teasers_cloned_id` (`cloned_id` ASC) ;");
	}

	public function safeDown()
	{
        $this->dropColumn("teasers", "cloned_id");
	}
}