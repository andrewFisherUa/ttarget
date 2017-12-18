<?php

class m160204_082203_external_integration extends CDbMigration
{
	public function up()
	{
        $this->execute("INSERT INTO `teaser_db`.`campaigns` (`id`, `client_id`, `date_start`, `date_end`, `max_clicks`, `is_active`, `name`, `limit_per_day`, `day_clicks`, `is_deleted`, `fake_clicks`, `clicks_without_externals`, `is_notified`, `cost_type`, `shows`, `clicks`, `actions`, `declined_actions`, `offers_clicks`, `offers_actions`, `offers_declined_actions`, `bounces`, `bounce_rate_diff`) VALUES ('1', '34', '2000-01-01', '2999-12-31', '0', '1', 'Внешние источники', '0', '0', '0', '0', '0', '0', 'click', '0', '0', '0', '0', '0', '0', '0', '0', '0.00');");
        $this->execute("INSERT INTO `teaser_db`.`news` (`id`, `name`, `is_active`, `campaign_id`, `create_date`, `failures`, `last_quality_week`, `deleted`, `is_deleted`, `shows`, `clicks`, `fake_clicks`, `clicks_without_externals`, `url_type`, `url_status`) VALUES ('1', 'Внешние источники', '1', '1', '2000-01-01', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0');");
        $this->execute("INSERT INTO `teaser_db`.`teasers` (`id`, `title`, `picture`, `news_id`, `is_active`, `is_deleted`, `is_external`, `cloned_id`, `create_date`) VALUES ('1', 'phoenix-widget.com', 'notfound.png', '1', '1', '0', '1', NULL, '2000-01-01');");
	}

	public function down()
	{
		echo "m160204_082203_external_integration does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}