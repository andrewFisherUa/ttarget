<?php

class m151019_093927_sessions_index extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `teaser_db`.`sessions_geo`
            CHANGE COLUMN `coutry_code` `country_code` CHAR(2) NOT NULL DEFAULT '' COMMENT '' ;
        ");
        $this->execute("ALTER TABLE `teaser_db`.`sessions`
            ADD INDEX `last_date` (`last_date` DESC)  COMMENT '';
        ");
	}

	public function down()
	{
		echo "m151019_093927_sessions_index does not support migration down.\n";
		return false;
	}

}