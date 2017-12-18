<?php
/*******************************************************************
*	file: TeaserLuaGoTest.php
*	freated: 17 мая 2015 г. - 23:43:21
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/

require_once('TtargetLuaTest.php');


/**
*	Тест, проверяющий взаимодействие скрипта lua.go и Redis
**/
class TtargetLuaGoBaseTest extends TtargetLuaTest
{
	//Base url for lua.go
	protected static $_LuaScriptBaseUrl = '/go';
	
	
	/**
	*	Base test
	*	url: /go?qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc..qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc
	**/
 	public function testClickBase()
	{
		$_teaser = array(
			'teaser_id'   => self::$Data->teasers->baseNoRefererCheck->id,
			'platform_id' => self::$Data->platforms->base1->id,
			//'campaign_id' => self::$Data->campaigns->activeBase->id,
			//'news_id'     => self::$Data->news->base1->id,
			'go_link'     => self::getScriptUrl(self::$Data->teasers->baseNoRefererCheck->encryptedLink . '..' .
											self::$Data->platforms->base1->encryptedId)
		);
		
		try{
			$_job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'), $_teaser['go_link']);
			$_time = time();
			$_ip = self::$IP;
		} catch( Exception $e ) {
			$this->fail("Unexpected exception: {$e->getMessage()}");
		}
			
		//Validating redis job
		$this->assertObjectHasAttribute('class', $_job, "Job object has not 'class' attribute");
		$this->assertAttributeEquals('ClicksJob', 'class', $_job, "Job object 'class' attribute is not 'ClicksJob'");
		$this->assertObjectHasAttribute('args', $_job, "Job object has not 'args' attribute");
		$this->assertAttributeInternalType('array', 'args', $_job, "Job object 'args' attribute is not an array");
		$this->assertAttributeNotEmpty('args', $_job, "Job object 'args' attribute is empty");
		
		//Validating redis job args
		$_args = $_job -> args[0];
		
		$this->assertInstanceOf('StdClass', $_args, "Job object attribute 'args' is not an instance of StdClass");
		
		$this->assertObjectHasAttribute('id', $_job, "Job object has not attribute 'id'");
		
		$this->assertObjectHasAttribute('platform_id', $_args, "Job object has not attribute 'platform_id'");
		$this->assertAttributeEquals($_teaser['platform_id'], 'platform_id', $_args, "Job object attribute 'platform_id' contains unexpected value");
		$this->assertObjectHasAttribute('teaser_id', $_args, "Job object has not attribute 'teaser_id'");
		$this->assertAttributeEquals($_teaser['teaser_id'], 'teaser_id', $_args, "Job object attribute 'teaser_id' contains unexpected value");
		
		$this->assertObjectHasAttribute('city_id', $_args, "Job object has not attribute 'city_id'");
		$this->assertAttributeEquals(0, 'city_id', $_args, "Job object attribute 'city_id' contains unexpected value");
		$this->assertObjectHasAttribute('country_code', $_args, "Job object has not attribute 'country_code'");
		$this->assertAttributeEquals('ZZ', 'country_code', $_args, "Job object attribute 'country_code' contains unexpected value");
		
		$this->assertObjectHasAttribute('timestamp', $_args, "Job object has not attribute 'timestamp'");
		$this->assertAttributeEquals($_time, 'timestamp', $_args, "Job object attribute 'timestamp' contains unexpected value");
		$this->assertObjectHasAttribute('remote_addr', $_args,"Job object has not attribute 'remote_addr'");
		$this->assertAttributeEquals($_ip, 'remote_addr', $_args, "Job object attribute 'remote_addr' contains unexpected value");
		
	}
	
	/**
	 *	Base referer check test
	 *	url: /go?qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc
	 **/
	public function testClickBaseRefererCheck()
	{
		$_teaser = array(
				'teaser_id' => self::$Data->teasers->baseRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1_withHosts->id,
				//'campaign_id' => self::$Data->campaigns->activeBase2->id,
				//'news_id' => self::$Data->news->base2->id,
				'go_link' => self::getScriptUrl(self::$Data->teasers->baseRefererCheck->encryptedLink)
		);
	
		$_hosts = explode("\n",self::$Data->platforms->base1_withHosts->hosts);
		foreach($_hosts as $k => $_host){
			
			try{
				$_job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'), $_teaser['go_link'], array(CURLOPT_REFERER=>'http://'.$_host));
				
				//print_r($_job);
				
			} catch( Exception $e ) {
				$this->fail("Unexpected exception: {$e->getMessage()}");
			}
				
			//Validating redis job
			$this->assertObjectHasAttribute('class', $_job, "Job object has not 'class' attribute");
			$this->assertAttributeEquals('ClicksJob', 'class', $_job, "Job object 'class' attribute is not 'ClicksJob'");
			$this->assertObjectHasAttribute('args', $_job, "Job object has not 'args' attribute");
			$this->assertAttributeInternalType('array', 'args', $_job, "Job object 'args' attribute is not an array");
			$this->assertAttributeNotEmpty('args', $_job, "Job object 'args' attribute is empty");
			
			//Validating redis job args
			$_args = $_job -> args[0];
			
			$this->assertInstanceOf('StdClass', $_args, "Job object attribute 'args' is not an instance of StdClass");
			
			$this->assertObjectHasAttribute('id', $_job, "Job object has not attribute 'id'");
			
			$this->assertObjectHasAttribute('platform_id', $_args, "Job object has not attribute 'platform_id'");
			$this->assertAttributeEquals($_teaser['platform_id'], 'platform_id', $_args, "Job object attribute 'platform_id' contains unexpected value");
			$this->assertObjectHasAttribute('teaser_id', $_args, "Job object has not attribute 'teaser_id'");
			$this->assertAttributeEquals($_teaser['teaser_id'], 'teaser_id', $_args, "Job object attribute 'teaser_id' contains unexpected value");
			
			$this->assertObjectHasAttribute('city_id', $_args, "Job object has not attribute 'city_id'");
			$this->assertAttributeEquals(0, 'city_id', $_args, "Job object attribute 'city_id' contains unexpected value");
			$this->assertObjectHasAttribute('country_code', $_args, "Job object has not attribute 'country_code'");
			$this->assertAttributeEquals('ZZ', 'country_code', $_args, "Job object attribute 'country_code' contains unexpected value");
			
			$this->assertObjectHasAttribute('timestamp', $_args, "Job object has not attribute 'timestamp'");
			$this->assertAttributeEquals(self::$Time, 'timestamp', $_args, "Job object attribute 'timestamp' contains unexpected value");
			$this->assertObjectHasAttribute('remote_addr', $_args,"Job object has not attribute 'remote_addr'");
			$this->assertAttributeEquals(self::$IP, 'remote_addr', $_args, "Job object attribute 'remote_addr' contains unexpected value");
			
			
			//$this->checkCampaignsCounter($_teaser['campaign_id'], array('clicks' => ($k + 1), 'clicks_without_externals' => ($k + 1)));
		}
	}
	
	/**
	*	Test for inactive campaign
	**/
	public function testClickBaseInactiveCampaign()
	{
		//INACTIVE (is_active = 0)
		$_teaser = array(
			'id' => self::$Data->teasers->base_bounceCheck->id,
			'platform_id' => self::$Data->platforms->base1_bounceCheck->id,
			'campaign_id' => self::$Data->campaigns->inactiveBase1->id,
			'news_id' => self::$Data->news->base2_bounceCheck->id,
			'go_link' => self::getScriptUrl(self::$Data->teasers->base_bounceCheck->encryptedLink . '..' .
											self::$Data->platforms->base1_bounceCheck->encryptedId)
		);
		
		try{
			$_Job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'),$_teaser['go_link']);
			$this->fail('Job is created for inactive campaign');
		} catch(Exception $e){
			$this->assertStringStartsWith('Job not present in redis', $e->getmessage(), "Unknown exception with message: ".$e->getMessage());
		}
		
		//ACTIVE (is_active = 1 )
		$_teaser = array(
				'id' => self::$Data->teasers->baseNoRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1->id,
				'campaign_id' => self::$Data->campaigns->activeBase->id,
				'news_id' => self::$Data->news->base1->id,
				'go_link' => self::getScriptUrl(self::$Data->teasers->baseNoRefererCheck->encryptedLink . '..' .
						self::$Data->platforms->base1->encryptedId)
		);
		try{
			$_Job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'),$_teaser['go_link']);
		} catch(Exception $e) {
			$this->fail('Job should be created for active campaign');
		}
		
	}
	
	/**
	*	Test isPilferer
	**/
	public function testIsPilferer()
	{
		$_teaser = array(
				'teaser_id' => self::$Data->teasers->baseNoRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1->id,
				//'campaign_id' => self::$Data->campaigns->activeBase->id,
				//'news_id' => self::$Data->news->base1->id,
				'go_link' => self::getScriptUrl(self::$Data->teasers->baseNoRefererCheck->encryptedLink . '..' .
						self::$Data->platforms->base1->encryptedId)
		);
		
		
		//IS_PILFERER == 0
		self::$Redis->hSet(self::getRedisKey('ip_log', self::$IP), 'is_pilferer', 0);
		
		try{
			$_job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'),$_teaser['go_link']);
		} catch(Exception $e) {
			$this->fail("Unexpected exception with message: {$e->getMessage()}");
		}
		

		//Validating redis job
		$this->assertObjectHasAttribute('class', $_job, "Job object has not 'class' attribute");
		$this->assertAttributeEquals('ClicksJob', 'class', $_job, "Job object 'class' attribute is not 'ClicksJob'");
		$this->assertObjectHasAttribute('args', $_job, "Job object has not 'args' attribute");
		$this->assertAttributeInternalType('array', 'args', $_job, "Job object 'args' attribute is not an array");
		$this->assertAttributeNotEmpty('args', $_job, "Job object 'args' attribute is empty");
			
		//Validating redis job args
		$_args = $_job -> args[0];
			
		$this->assertInstanceOf('StdClass', $_args, "Job object attribute 'args' is not an instance of StdClass");
			
		$this->assertObjectHasAttribute('id', $_job, "Job object has not attribute 'id'");
			
		$this->assertObjectHasAttribute('platform_id', $_args, "Job object has not attribute 'platform_id'");
		$this->assertAttributeEquals($_teaser['platform_id'], 'platform_id', $_args, "Job object attribute 'platform_id' contains unexpected value");
		$this->assertObjectHasAttribute('teaser_id', $_args, "Job object has not attribute 'teaser_id'");
		$this->assertAttributeEquals($_teaser['teaser_id'], 'teaser_id', $_args, "Job object attribute 'teaser_id' contains unexpected value");
			
		$this->assertObjectHasAttribute('city_id', $_args, "Job object has not attribute 'city_id'");
		$this->assertAttributeEquals(0, 'city_id', $_args, "Job object attribute 'city_id' contains unexpected value");
		$this->assertObjectHasAttribute('country_code', $_args, "Job object has not attribute 'country_code'");
		$this->assertAttributeEquals('ZZ', 'country_code', $_args, "Job object attribute 'country_code' contains unexpected value");
			
		$this->assertObjectHasAttribute('timestamp', $_args, "Job object has not attribute 'timestamp'");
		$this->assertAttributeEquals(self::$Time, 'timestamp', $_args, "Job object attribute 'timestamp' contains unexpected value");
		$this->assertObjectHasAttribute('remote_addr', $_args,"Job object has not attribute 'remote_addr'");
		$this->assertAttributeEquals(self::$IP, 'remote_addr', $_args, "Job object attribute 'remote_addr' contains unexpected value");
			
		//IS_PILFERER == 1
		self::$Redis->hSet(self::getRedisKey('ip_log', self::$IP), 'is_pilferer', 1);
		
		try{
			$_job = self::clickAndGetRedisJob(self::getRedisKey('queue_stat'),$_teaser['go_link']);
			$this->fail("Job has been created for pilferer IP (".self::getRedisKey('ip_log', self::$IP)." = ".self::$Redis->hGet(self::getRedisKey('ip_log', self::$IP), 'is_pilferer').")");
		} catch(Exception $e) {
			
		}
	}
	
	/******************************************************************************************/
	
	
	
	
	
	
	/******************************************************************************************/
	/**
	*	Set up before each test method
	**/
	public function setUp()
	{
		parent::setUp();
	
		//Обнуляем очередь задач в redis
		self::$Redis->del(self::getRedisKey('queue_stat'));
	}
	
	/**
	 *	Set up before class test methods starts
	 **/
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
	

		
	}
	
	/**
	 *	Tear down after all class tests has been executed
	 **/
	public static function tearDownAfterClass()
	{
		
		
		
		parent::tearDownAfterClass();
	}
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: TeaserLuaGoTest.php
**/