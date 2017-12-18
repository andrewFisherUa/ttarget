<?php
	return array(
	   'guest' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'Guest',
	        'bizRule' => null,
	        'data' => null
	    ),
	    'user' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'User',
	        'children' => array(
	            'guest', // унаследуемся от гостя
	        ),
	        'bizRule' => null,
	        'data' => null
	    ),
		'platform' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'Platform',
	        'children' => array(
	            'guest', // унаследуемся от гостя
	        ),
	        'bizRule' => null,
	        'data' => null
	    ),/*
	    'moderator' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'Moderator',
	        'children' => array(
	            'user',          // позволим модератору всё, что позволено пользователю
	        ),
	        'bizRule' => null,
	        'data' => null
	    ),*/
	    'admin' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'Admin',
	        'children' => array(
	            'user',
	        ),
	        'bizRule' => null,
	        'data' => null
	    ),
		'webmaster' => array(
	        'type' => CAuthItem::TYPE_ROLE,
	        'description' => 'Webmaster',
	        'children' => array(
	            'user',
	        ),
	        'bizRule' => null,
	        'data' => null
	    ),
);