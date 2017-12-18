<?php

class m140226_173754_teaser_creation_date extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("ALTER TABLE `teasers` ADD COLUMN `create_date` DATE NOT NULL  AFTER `cloned_id` ;");
        $this->execute("UPDATE `teasers` SET `create_date` = NOW();");
	}

	public function safeDown()
	{
        $this->dropColumn("teasers","create_date");
	}

}