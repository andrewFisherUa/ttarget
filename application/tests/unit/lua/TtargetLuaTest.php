<?php
/*******************************************************************
*	file: TtargetLuaTestCase.php
*	freated: 19 мая 2015 г. - 13:36:56
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


class TtargetLuaTest extends TtargetUnitTest
{
	protected static $_LuaScriptBaseUrl;
	
	
	/**
	*	Return lua script base url
	**/
	protected static function getScriptBaseUrl()
	{
		if(!empty(static::$_LuaScriptBaseUrl)){
			return static::$_LuaScriptBaseUrl;
		}
		throw new Exception('$_LuaScriptBaseUrl is undefined');
	}
	
	/**
	*	Return script url with params
	**/
	protected static function getScriptUrl( $queryParams = array() )
	{
		$_url = Yii::app()->params->testBaseUrl . self::getScriptBaseUrl();
		if(!empty($queryParams)){
			if(is_scalar($queryParams)){
				return $_url . '?' . $queryParams;
			}
			return $_url . '?' . http_build_query($queryParams);
		}
		return $_url;
	}
	
	
	/***********************************************************************************************/
	
	
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		
		
		
	}
	
	public static function tearDownAfterClass()
	{
		
		
		
		parent::tearDownAfterClass();
	}
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: TtargetLuaTestCase.php
**/