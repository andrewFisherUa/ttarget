<?php
/*******************************************************************
*	file: PlatformCheckTeaserStatusInitJobTest.php
*	freated: 19 июня 2015 г. - 2:18:29
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


/**
 *	Тест проверяет корректность работы PlatformCheckTeaserStatusInitJob
 **/
class PlatformCheckTeaserStatusInitJobTest extends TtargetUnitTest
{
	const JOB_CLASSNAME = 'PlatformCheckTeaserStatusInitJob';
	
	public function testInsertValidPlatformId()
	{
		$_job = (object)array(
				'class' => self::JOB_CLASSNAME,
				'args'  => array(
						(object)array(
								'platform_id'  => self::$Data->platforms->base1->id,
								'timestamp'    => self::$Time,
						)
				)
		);
		
		self::executeRedisJob($_job);
		
		
	}
	
	public function testUpdateValidPlatformId()
	{
		$_job = (object)array(
				'class' => self::JOB_CLASSNAME,
				'args'  => array(
						(object)array(
								'platform_id'  => 1000,
								'timestamp'    => self::$Time,
						)
				)
		);
		
		self::executeRedisJob($_job);
	}
	
	public function testInsertInvalidPlatformId()
	{
		
	}
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: PlatformCheckTeaserStatusJobTest.php
**/