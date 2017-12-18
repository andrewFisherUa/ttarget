<?php

class m151218_113559_rtb_optimization extends CDbMigration
{
	public function up()
	{
        $this->execute("ALTER TABLE `teaser_db`.`campaigns_creative`
            ADD INDEX `complex_reciever` (`is_active` ASC, `status` ASC, `is_created` ASC, `size` ASC, `cost` ASC, `max_shows_hour` ASC, `max_shows_day` ASC, `max_shows_week` ASC)  COMMENT '';
        ");

        $this->execute("ALTER TABLE `teaser_db`.`campaigns_creative_view_yandex`
            ADD INDEX `creative_id_view_datetime` (`creative_id` ASC, `view_datetime` ASC)  COMMENT '';
        ");
	}

	public function down()
	{
		echo "m151218_113559_rtb_optimization does not support migration down.\n";
		return false;
	}
}