<?php

class m151102_084033_rtb_tblname_fix extends CDbMigration
{
	public function up()
	{
        $this->renameTable("platforms_yandex_cpc", "platforms_rtb_cpc");
	}

	public function down()
	{
		echo "m151102_084033_rtb_tblname_fix does not support migration down.\n";
		return false;
	}
}