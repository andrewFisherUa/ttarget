<?php
/*******************************************************************
*	file: init.campaigns.php
*	freated: 20 мая 2015 г. - 6:29:14
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


$_fixtures = (object)array(
	'activeBase' => (object)array(
		'id' => 1,
		'client_id' => $_users->platformActive->id,
		'date_start' => date('Y-m-d'),
		'date_end' => date('Y-m-d'),
		'max_clicks' => 1000,
		'is_active' => 1,
		'name' => 'testcamp1',
		'limit_per_day' => 1000,
		'day_clicks' => 0,
		'is_deleted' => 0,
		'fake_clicks' => 0,
		'clicks_without_externals' => 0,
		'is_notified' => 0,
		'ga_access_token' => '',
		'ga_profile_id' => 1,
		'cost_type' => 'click',
		'bounce_check' => null,
		'shows' => 0,
		'clicks' => 0,
		'actions' => 0,
		'declined_actions' => 0,
		'offers_actions' => 0,
		'offers_declined_actions' => 0,
		'bounces' => 0,
		'bounce_rate_diff' => 0.00,
	),
	'activeBase2' => (object)array(
		'id' => 2,
		'client_id' => $_users->platformActive->id,
		'date_start' => date('Y-m-d'),
		'date_end' => date('Y-m-d'),
		'max_clicks' => 1000,
		'is_active' => 1,
		'name' => 'testcamp1',
		'limit_per_day' => 1000,
		'day_clicks' => 0,
		'is_deleted' => 0,
		'fake_clicks' => 0,
		'clicks_without_externals' => 0,
		'is_notified' => 0,
		'ga_access_token' => '',
		'ga_profile_id' => 1,
		'cost_type' => 'click',
		'bounce_check' => null,
		'shows' => 0,
		'clicks' => 0,
		'actions' => 0,
		'declined_actions' => 0,
		'offers_actions' => 0,
		'offers_declined_actions' => 0,
		'bounces' => 0,
		'bounce_rate_diff' => 0.00,
	),
	'inactiveBase1' => (object)array(
		'id' => 3,
		'client_id' => $_users->platformActive->id,
		'date_start' => date('Y-m-d'),
		'date_end' => date('Y-m-d'),
		'max_clicks' => 1000,
		'is_active' => 0,
		'name' => 'testcamp1',
		'limit_per_day' => 1000,
		'day_clicks' => 0,
		'is_deleted' => 0,
		'fake_clicks' => 0,
		'clicks_without_externals' => 0,
		'is_notified' => 0,
		'ga_access_token' => '',
		'ga_profile_id' => 1,
		'cost_type' => 'click',
		'bounce_check' => 0,
		'shows' => 0,
		'clicks' => 0,
		'actions' => 0,
		'declined_actions' => 0,
		'offers_actions' => 0,
		'offers_declined_actions' => 0,
		'bounces' => 0,
		'bounce_rate_diff' => 0.00,
	)
);

Yii::app()->db->createCommand()->delete('campaigns');
foreach($_fixtures as $k => $_row){
	Yii::app()->db->createCommand()->insert('campaigns',$_row);
	$Campaign = Campaigns::model()->findByPk($_row->id);
	RedisCampaign::instance()->setCampaignCache($Campaign);
}

return $_fixtures;

/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.campaigns.php
**/