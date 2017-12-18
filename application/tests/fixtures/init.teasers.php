<?php
/*******************************************************************
*	file: init.teasers.php
*	freated: 20 мая 2015 г. - 6:47:42
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


$_fixtures = (object)array(
		'baseNoRefererCheck' => (object)array(
				'id' => 1,
				'title' => 'test_teaser_1',
				'news_id' => $_news->base1->id,
				'type' => 0,
				'cloned_id' => null,
				'create_date' => date('Y-m-d'),
				'is_external' => 0,
		),
		'baseRefererCheck' => (object)array(
				'id' => 2,
				'title' => 'test_teaser_2',
				'news_id' => $_news->base2->id,
				'type' => 0,
				'cloned_id' => null,
				'create_date' => date('Y-m-d'),
				'is_external' => 0,
		),
		'base_bounceCheck' => (object)array(
				'id' => 3,
				'title' => 'test_teaser_2',
				'news_id' => $_news->base2_bounceCheck->id,
				'type' => 0,
				'cloned_id' => null,
				'create_date' => date('Y-m-d'),
				'is_external' => 0,
		)
);

Yii::app()->db->createCommand()->delete('teasers');
foreach($_fixtures as $k => $_row){
	Yii::app()->db->createCommand()->insert('teasers',$_row);
	$Teaser = Teasers::model()->findByPk($_row->id);
	RedisTeaser::instance()->addLink($Teaser);
	$_fixtures->$k->encryptedLink = $Teaser->getEncryptedLink();
}

return $_fixtures;




/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: init.teasers.php
**/