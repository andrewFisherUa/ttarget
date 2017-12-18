<?php

class m150411_103235_action_track extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `actions_log`
            DROP COLUMN `referrer_url`,
            ADD COLUMN `track_id` INT(10) UNSIGNED NOT NULL AFTER `target_id`;");
	}

	public function down()
	{
		echo "m150411_103235_action_track does not support migration down.\n";
		return false;
	}
}