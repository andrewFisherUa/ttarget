<?php
/*******************************************************************
*	file: ClicksJobTest.php
*	freated: 20 мая 2015 г. - 22:01:04
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


require_once(dirname(__FILE__).'/JobStatUnitTest.php');

class ClicksJobTest extends JobStatUnitTest
{
	const JOB_CLASSNAME = 'ClicksJob';
	
	public function testJobValidateArgs()
	{
		
		$_requiredArgs = array(
				'platform_id' => self::$Data->platforms->base1->id,
				'teaser_id'   => self::$Data->teasers->baseNoRefererCheck->id,
				'timestamp'   => self::$Time,
				'remote_addr' => self::$IP
		);
		
		$_job = (object)array(
				'class' => self::JOB_CLASSNAME,
				'args'  => array(
						(object)array(
								
						)
				)
		);
		
		//execute
		self::executeRedisJob($_job);
		if(isset($_job->__execResult)){
			$this->assertEquals(1, $_job->__execResult->status, "Job exit status is not '1'");
			if(!empty($_job->__execResult->output)){
				$this->assertStringStartsWith('exception',$_job->__execResult->output[0],'Expected job exception is missing');
				$this->assertRegexp('/Not valid args\:/',$_job->__execResult->output[0],'Expected job exception message "Not valid args" is missing');
			} else {
				throw new Exception("Job exec result output is empty");
			}
		} else {
			throw new Exception("Can't find __execResult in job object");
		}
		
		
		foreach($_requiredArgs as $_arg => $_value){
			//add by one
			$_job->args[0]->$_arg = $_value;
			self::executeRedisJob($_job);
			if(isset($_job->__execResult)){
				$this->assertEquals(1, $_job->__execResult->status, "Job exit status is not '1'");
				if(!empty($_job->__execResult->output)){
					$this->assertStringStartsWith('exception',$_job->__execResult->output[0],'Expected job exception is missing');
					$this->assertRegexp('/Not valid args\:/',$_job->__execResult->output[0],'Expected job exception message "Not valid args" is missing');
				} else {
					throw new Exception("Job exec result output is empty");
				}
			} else {
				throw new Exception("Can't find __execResult in job object");
			}
			
			$_job->args[0] = (object)array();
		}
		
		$_keys = array_keys($_requiredArgs);
		$_i = 0;
		while($_i++ < count($_requiredArgs) - 1){
			$_job->args[0]->$_keys[$_i-1] = $_requiredArgs[$_keys[$_i-1]];
			self::executeRedisJob($_job);
			if(isset($_job->__execResult)){
				$this->assertEquals(1, $_job->__execResult->status, "Job exit status is not '1'");
				if(!empty($_job->__execResult->output)){
					$this->assertStringStartsWith('exception',$_job->__execResult->output[0],'Expected job exception is missing');
					$this->assertRegexp('/Not valid args\:/',$_job->__execResult->output[0],'Expected job exception message "Not valid args" is missing');
				} else {
					throw new Exception("Job exec result output is empty");
				}
			} else {
				throw new Exception("Can't find __execResult in job object");
			}
		}
		
		//All required arguments are exists
		$_job->args[0]->$_keys[$_i-1] = $_requiredArgs[$_keys[$_i-1]];
		self::executeRedisJob($_job);
		if(isset($_job->__execResult)){
			$this->assertEquals(0, $_job->__execResult->status, "Job exit status is not '0' (args: [".var_export($_job->args[0], true)."])");
		} else {
			throw new Exception("Can't find __execResult in job object");
		}
	}
	
	/**
	 *	Check report tables for base click test
	 *	url: /go?qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc..qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc
	 **/
	public function testClickBase()
	{
		$_teaser = array(
				'teaser_id' => self::$Data->teasers->baseNoRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1->id,
				'campaign_id' => self::$Data->campaigns->activeBase->id,
				'news_id' => self::$Data->news->base1->id,
				//'go_link' => self::getScriptUrl(self::$Data->teasers->baseNoRefererCheck->encryptedLink . '..' .
				//		self::$Data->platforms->base1->encryptedId)
		);
	
		$_job = (object)array(
				'class' => self::JOB_CLASSNAME,
				'args'  => array(
						(object)array(
							'platform_id'  => $_teaser['platform_id'],
							'teaser_id'    => $_teaser['teaser_id'],
							'city_id'      => 0,
							'country_code' => 'ZZ',
							'timestamp'    => self::$Time,
							'remote_addr'  => self::$IP
						)
				)
		);
		
		self::executeRedisJob($_job);
	
		$this->checkCampaignsCounter($_teaser['campaign_id'], array('clicks' => 1));
		$this->checkReports(array_merge(array('city_id' => $_job->args[0]->city_id, 'country_code' => $_job->args[0]->country_code),$_teaser), 1);
	}
	
	/**
	 *	Base referer check test
	 *	url: /go?qFdhsrOp0qIOeSJmh6hnSaUd1rOjoQhL1OrBGkEg9Mc
	 **/
	public function testReportsTables()
	{
		$_teasers1 = array(
			'refererCheck' => array(
				'teaser_id' => self::$Data->teasers->baseRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1_withHosts->id,
				'campaign_id' => self::$Data->campaigns->activeBase2->id,
				'news_id' => self::$Data->news->base2->id,
				//'go_link' => self::getScriptUrl(self::$Data->teasers->baseRefererCheck->encryptedLink)
			),
			'refererCheck2' => array(
				'teaser_id' => self::$Data->teasers->baseRefererCheck->id,
				'platform_id' => self::$Data->platforms->base1_withHosts->id,
				'campaign_id' => self::$Data->campaigns->activeBase2->id,
				'news_id' => self::$Data->news->base2->id,
				//'go_link' => self::getScriptUrl(self::$Data->teasers->baseRefererCheck->encryptedLink)
			)
		);
		
		$i = 0;
		foreach($_teasers1 as $k => $_teaser){
			
			$_job = (object)array(
					'class' => self::JOB_CLASSNAME,
					'args'  => array(
							(object)array(
								'platform_id'  => $_teaser['platform_id'],
								'teaser_id'    => $_teaser['teaser_id'],
								'city_id'      => 0,
								'country_code' => 'ZZ',
								'timestamp'    => self::$Time,
								'remote_addr'  => self::$IP
							)
					)
			);
			
			//print_r($_job);
			
			self::executeRedisJob($_job);
			$i++;
			
			$this->checkCampaignsCounter($_teaser['campaign_id'], array('clicks' => $i));
			$this->checkReports(array_merge(array('city_id' => $_job->args[0]->city_id, 'country_code' => $_job->args[0]->country_code),$_teaser), $i);
		}
	}
	
	/**
	 *	Check counters in `campaigns` table
	 **/
	protected function checkCampaignsCounter( $campaignId, $counterFields = array() )
	{
		$_campaign = Yii::app()->db->createCommand()
		->select()
		->from('campaigns')
		->where('id = :id')
		->queryRow(true,array(':id' => $campaignId));
		if(!empty($_campaign)){
			foreach($counterFields as $field => $value){
				$this->assertEquals($value,$_campaign[$field],"campaigns.$field must be '$value'");
			}
		}
	}
	
	/**
	 *	Check counters in reports tables
	 **/
	protected function checkReports( $data, $counter )
	{
		/**
		 *	TODO INSERT INTO `report_daily_by_teaser_and_platform`
		 *	(teaser_id, platform_id, date, clicks) VALUES ('1', '1', '2015-05-19', 1)
		 *	ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_teaser_and_platform')
		->where('teaser_id = :teaser_id AND platform_id = :platform_id')
		->queryRow(true, array(':teaser_id' => $data['teaser_id'], ':platform_id' => $data['platform_id']));
		$this->assertNotEmpty($_r,'report_daily_by_teaser_and_platform is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_teaser_and_platform.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_teaser_and_platform.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_platform` (platform_id, date, clicks) VALUES ('1', '2015-05-19', 1)
		 *	ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_platform')
		->where('platform_id = :platform_id')
		->queryRow(true, array(':platform_id' => $data['platform_id']));
		$this->assertNotEmpty($_r,'report_daily_by_platform is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_platform.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_platform.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_news_and_platform` (news_id, platform_id, date, clicks) VALUES ('1', '1', '2015-05-19', 1)
		 *	ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_news_and_platform')
		->where('news_id = :news_id AND platform_id = :platform_id')
		->queryRow(true, array(':news_id' => $data['news_id'],':platform_id' => $data['platform_id']));
		$this->assertNotEmpty($_r,'report_daily_by_news_and_platform is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_news_and_platform.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_news_and_platform.date is invalid');
	
		/**
		 *	TODO INTO `report_daily_by_news` (news_id, date, clicks) VALUES ('1', '2015-05-19', 1)
		 *	ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_news')
		->where('news_id = :news_id')
		->queryRow(true, array(':news_id' => $data['news_id']));
		$this->assertNotEmpty($_r,'report_daily_by_news is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_news.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_news.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_campaign_and_platform_and_country`
		 *	(campaign_id, platform_id, country_code, date, clicks)
		 *	VALUES ('1', '1', 'ZZ', '2015-05-19', 1) ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_campaign_and_platform_and_country')
		->where('campaign_id = :campaign_id AND platform_id = :platform_id AND country_code = :country_code')
		->queryRow(true, array( ':campaign_id' => $data['campaign_id'],
				':platform_id' => $data['platform_id'],
				':country_code' => $data['country_code']
		));
		$this->assertNotEmpty($_r,'report_daily_by_campaign_and_platform_and_country is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_campaign_and_platform_and_country.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_campaign_and_platform_and_country.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_campaign_and_platform_and_city`
		 *	(campaign_id, platform_id, city_id, date, clicks)
		 *	VALUES ('1', '1', '0', '2015-05-19', 1) ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_campaign_and_platform_and_city')
		->where('campaign_id = :campaign_id AND platform_id = :platform_id AND city_id = :city_id')
		->queryRow(true, array( ':campaign_id' => $data['campaign_id'],
				':platform_id' => $data['platform_id'],
				':city_id' => $data['city_id']
		));
		$this->assertNotEmpty($_r,'report_daily_by_campaign_and_platform_and_city is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_campaign_and_platform_and_city.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_campaign_and_platform_and_city.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_campaign_and_platform` (campaign_id, platform_id, date, clicks)
		 *	VALUES ('1', '1', '2015-05-19', 1) ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_campaign_and_platform')
		->where('campaign_id = :campaign_id AND platform_id = :platform_id')
		->queryRow(true, array(':campaign_id' => $data['campaign_id'], ':platform_id' => $data['platform_id']));
		$this->assertNotEmpty($_r,'report_daily_by_campaign_and_platform is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_campaign_and_platform.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_campaign_and_platform.date is invalid');
	
		/**
		 *	TODO INSERT INTO `report_daily_by_campaign` (campaign_id, date, clicks)
		 *	VALUES ('1', '2015-05-19', 1) ON DUPLICATE KEY UPDATE clicks = clicks + 1
		**/
		$_r = Yii::app()->db->createCommand()
		->select()
		->from('report_daily_by_campaign')
		->where('campaign_id = :campaign_id')
		->queryRow(true, array(':campaign_id' => $data['campaign_id']));
		$this->assertNotEmpty($_r,'report_daily_by_campaign is empty');
		$this->assertEquals($counter,$_r['clicks'],'report_daily_by_campaign.clicks must be '.$counter);
		$this->assertEquals(date('Y-m-d'),$_r['date'],'report_daily_by_campaign.date is invalid');
	}
	
	
	/**
	 *	Set up before each test method
	 **/
	public function setUp()
	{
		parent::setUp();
	
		//Обнуляем счетчики
		self::$DB->createCommand()->delete('report_daily_by_campaign');
		self::$DB->createCommand()->delete('report_daily_by_campaign_and_platform');
		self::$DB->createCommand()->delete('report_daily_by_campaign_and_platform_and_city');
		self::$DB->createCommand()->delete('report_daily_by_campaign_and_platform_and_country');
		self::$DB->createCommand()->delete('report_daily_by_news');
		self::$DB->createCommand()->delete('report_daily_by_news_and_platform');
		self::$DB->createCommand()->delete('report_daily_by_platform');
		self::$DB->createCommand()->delete('report_daily_by_teaser_and_platform');
		self::$DB->createCommand()->delete('report_daily');
	
		self::$DB->createCommand()->update('campaigns',array('clicks' => 0, 'clicks_without_externals' => 0));
		
		//Обнуляем очередь задач в redis
		self::$Redis->del(self::getRedisKey('queue_stat'));
	}
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: ClicksJobTest.php
**/
