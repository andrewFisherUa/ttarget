<?php
/*******************************************************************
*	file: init.news.php
*	freated: 20 мая 2015 г. - 6:45:24
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


$_fixtures = (object)array(
		'base1' => (object)array(
				'id' => 1,
				'name' => 'test_news_1',
				'url' => 'http://google.ru',
				'campaign_id' => $_campaigns->activeBase->id,
				'create_date' => date('Y-m-d'),
				'shows' => 0,
				'clicks' => 0,
				'fake_clicks' => 0,
				'clicks_without_externals' => 0,
				'url_type' => 0,
		),
		'base2' => (object)array(
				'id' => 2,
				'name' => 'test_news_1',
				'url' => 'http://google.ru',
				'campaign_id' => $_campaigns->activeBase2->id,
				'create_date' => date('Y-m-d'),
				'shows' => 0,
				'clicks' => 0,
				'fake_clicks' => 0,
				'clicks_without_externals' => 0,
				'url_type' => 0,
		),
		'base2_bounceCheck' => (object)array(
				'id' => 3,
				'name' => 'test_news_3',
				'url' => 'http://google.ru',
				'campaign_id' => $_campaigns->inactiveBase1->id,
				'create_date' => date('Y-m-d'),
				'shows' => 0,
				'clicks' => 0,
				'fake_clicks' => 0,
				'clicks_without_externals' => 0,
				'url_type' => 0,
		)
);

Yii::app()->db->createCommand()->delete('news');
foreach($_fixtures as $k => $_row){
	Yii::app()->db->createCommand()->insert('news',$_row);
}

return $_fixtures;

/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.news.php
**/