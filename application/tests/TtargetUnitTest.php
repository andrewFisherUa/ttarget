<?php
/*******************************************************************
*	file: TtargetUnitTestCase.php
*	freated: 18 мая 2015 г. - 4:16:48
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


class TtargetUnitTest extends CDbTestCase
{
	//LUA redis keys, specified in provider.lua
	protected static $_RedisKeys = array(
			'queue_stat'           => "ttarget:queue:stat",
			'teaser_html'          => "ttarget:teasers:%s:html",
			'teaser_encrypted'     => "ttarget:teasers:link:%s",
			'offer'                => "ttarget:offers:%s",
			'offer_countries'      => "ttarget:offers:%s:countries",
			'offer_cities'         => "ttarget:offers:%s:cities",
			'offer_user_encrypted' => "ttarget:offers_users:%s",
			'teaser_score'         => "ttarget:teasers:%d:score",
			'campaign'             => "ttarget:campaigns:%d",
			'campaign_actions'     => "ttarget:campaigns:%d:actions",
			'action'               => "ttarget:actions:%s",
			'track'                => "ttarget:tracks:%d",
			'track_sequence'       => "ttarget:tracks:sequence",
			'ip_log'               => "ttarget:ip:%s",
			'track_action_timeout' => "ttarget:tracks:%d:actions:%s:timeout",
			'platforms_hosts'      => "ttarget:platforms:hosts",
			'platforms_encrypted'  => "ttarget:platforms:encrypted",
			'rotation_version'     => "ttarget:version",
			'shows_counter'        => "ttarget:shows:%d:%d:%d:%s",
	);
	
	//время таймаута для трекинга действий,
	//при котором повторно выполненые действия засчитываться не будут
	protected static $_LuaTrackActionTimeout = 43200;
	
	
	protected static $Redis;
	protected static $DB;
	protected static $Data;
	protected static $IP;
	protected static $Time;
	
	protected $fixtures = array();
	
	/**
	 *	Format and return redis key
	 **/
	protected static function getRedisKey( $key )
	{
		if(!empty($key)){
			if(isset(self::$_RedisKeys[$key])){
				$_args = func_get_args();
				$_args[0] = self::$_RedisKeys[$key];
				return call_user_func_array('sprintf', $_args);
			}
			throw new Exception("Redis key '$key' is undefined");
		}
		throw new Exception('Redis key is not specified');
	}
	
	/**
	 *	Send HTTP request
	 *	@return bool
	 *	@throws Exception
	 **/
	protected static function sendRequest( $url = null, $curlOptions = array() )
	{
		if(!empty($url)){
			if( $curl = curl_init() ) {
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
				if(!empty($curlOptions)){
					foreach($curlOptions as $opt => $value){
						curl_setopt($curl, $opt, $value);
					}
				}
				$_c = curl_exec($curl);
				curl_close($curl);
				
				//print_r($url);
				//print_r($_c);
				
				return $_c;
			} else {
				throw new Exception('Unable initialize CURL connection');
			}
		}
		return false;
	}

	/**
	*	Get job from redis queue
	*
	*	@param string $redisKeyName
	*	@return StdClass
	*	@throws Exception
	**/
	protected static function getRedisJob( $redisKeyName )
	{
		$_json = self::$Redis->rPop($redisKeyName);
		if(!empty($_json)){
			$_job = json_decode($_json);
			if( $_job instanceof StdClass ){
				return $_job;
			} else {
				throw new Exception("Job json is not a StdClass serialized object. Queue: [$redisKeyName]");
			}
		} else {
			throw new Exception("Job not present in redis (key: $redisKeyName)");
		}
	}
	
	/**
	*	Send HTTP request and return job object from redis queue
	*
	*	@param string $redisKeyName
	*	@param string $requestUrl
	*	@param array  $requestOptions Options for CURL request
	*	@return StdClass
	*	@throws Exception
	**/
	public static function clickAndGetRedisJob( $redisKeyName, $requestUrl, $requestOptions = array() )
	{
		if( $_r = self::sendRequest( $requestUrl, $requestOptions ) ){
			$_job = self::getRedisJob($redisKeyName);
			$_job->response = $_r;
			return $_job;
		} else {
			throw new Exception("HTTP request failed for url: $requestUrl");
		}
	}
	
	/**
	*	Send HTTP request and perform job from redis queue
	*
	*	@param string $redisKeyName
	*	@param string $requestUrl
	*	@param array  $requestOptions Options for CURL request
	*	@return
	*	@throws Exception
	**/
	public static function clickAndPerformRedisJob( $redisKeyName, $requestUrl, $requestOptions = array() )
	{
		$_job = self::clickAndGetRedisJob($redisKeyName, $requestUrl, $requestOptions);
		self::executeRedisJob($_job);
		return $_job;
	}
	
	/**
	*	Execute job
	*	@param StdClass $job
	*	@return StdClass executed job
	*	@throws Exception
	**/
	public static function executeRedisJob( &$job )
	{
		if(!empty($job->class)){
			
			$_className = $job->class;
			$_args      = !empty($job->args[0]) ? $job->args[0] : array();
			
			$_command = YIIC_TESTS_COMMAND." job perform --job=$_className --json_args='".json_encode($_args)."'";
			
			$_output = array();
			$_returnVar = 0;
			
			exec($_command, $_output, $_returnVar);
			
			//print_r($_output);
			
			$job -> __execResult = (object)array('status' => $_returnVar, 'output' => $_output);
			
		} else {
			throw new Exception('Job classname is undefined');
		}
	}

	
	/**
	 *	Perform jb execution and return result
	 *	@return int
	 **/
	protected static function executeJob( $className, $args = null, $returnOutput = false )
	{
		$_command = YIIC_TESTS_COMMAND." job perform --job=$className --json_args='".json_encode($args)."'";
		//print_r("\n".$_command."\n");
		$_output = array();
		$_returnVar = 0;
		exec($_command, $_output, $_returnVar);
		if($returnOutput){
			return $_output;
		}
		return $_returnVar;
	}
	
	/**
	*	Get current machine IP addr (eth0)
	**/
	protected static function getCurrentIP()
	{
		return trim(shell_exec('ifconfig eth0 | grep \'inet addr:\' | cut -d: -f2 | awk \'{ print $1}\''));
	}
	
	
	/**********************************************************************************************/
	
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();
		
		self::$Redis = Yii::app()->redis;
		self::$DB = Yii::app()->db;
		self::$IP = self::getCurrentIP();
	}
	
	public function setUp()
	{
		parent::setUp();
		
		self::$Data = Yii::app()->params->testData;
		self::$Time = time();
	}
	
	public function tearDown()
	{
		
		
		parent::tearDown();
	}
	
	public static function tearDownAfterClass()
	{
		
		
		
		parent::tearDownAfterClass();
	}
	
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: TtargetUnitTestCase.php
**/
