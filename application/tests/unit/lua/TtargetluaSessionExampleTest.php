<?php
/*******************************************************************
*	file: TtargetluaSessionExampleTest.php
*	freated: 03 авг. 2015 г. - 13:39:31
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/
require_once('TtargetLuaTest.php');

class TtargetluaSessionExampleTest extends TtargetLuaTest
{
    protected static $_LuaScriptBaseUrl = '/session.lua';
    
    public function testIsWorking()
    {
        
        
        
        $_linkRandom = '?test='.mt_rand(0, 1000);
        $_link = self::getScriptUrl().$_linkRandom;
        
        $_curlOpt = array(
            CURLOPT_USERAGENT => 'test user agent'
        );
        
        
        $_ip = self::$IP;
        
        $_job = self::clickAndPerformRedisJob(self::getRedisKey('queue_stat'), $_link, $_curlOpt);
        
        $_time = $_job->args[0]->timestamp;
        
        //print_r($_job);
        
        $this->assertObjectHasAttribute('class', $_job, "Job object has not 'class' attribute");
		$this->assertAttributeEquals('SessionsJob', 'class', $_job, "Job object 'class' attribute is not 'ClicksJob'");
		$this->assertObjectHasAttribute('args', $_job, "Job object has not 'args' attribute");
		$this->assertAttributeInternalType('array', 'args', $_job, "Job object 'args' attribute is not an array");
		$this->assertAttributeNotEmpty('args', $_job, "Job object 'args' attribute is empty");
		
		//Validating redis job args
		$_args = $_job -> args[0];
		
		$this->assertInstanceOf('StdClass', $_args, "Job object attribute 'args' is not an instance of StdClass");
		$this->assertObjectHasAttribute('id', $_job, "Job object has not attribute 'id'");
		
		$_session = Yii::app()->db->createCommand('SELECT * FROM user_session')->queryAll(true);
		$this->assertNotEmpty($_session, 'Session table is empty');
		
		if(!empty($_session)){
		    $_session = $_session[0];
		    
		    $this->assertEquals(trim($_session['uuid']), trim($_job->response), 'Session UUID is not ['.$_job->response.']');
		    //$this->assertEquals(trim($_session['created_date']), date('Y-m-d H:i:s', $_time), 'Session created_date is not ['.date('Y-m-d H:i:s', $_time).']');
		    //$this->assertEquals(trim($_session['last_visit']), date('Y-m-d H:i:s', $_time), 'Session last_visit is not ['.date('Y-m-d H:i:s', $_time).']');
		
		    $_log = Yii::app()->db->createCommand('SELECT * FROM user_session_log')->queryAll(true);
		    $this->assertNotEmpty($_log, 'Session table log is empty');
		    
		    if(!empty($_log)){
		        $_log = $_log[0];
		    }
		    
		    //print_r($_log);
		    
		    $this->assertEquals($_log['session_id'], $_session['id'], 'Log session_id is invalid');
		    //$this->assertEquals($_log['timestamp'], $_session['last_visit'], 'Log timestamp is not equals to session last_visit');
		    $this->assertEquals($_log['remote_addr'], $_ip, 'Log remote_addr is invalid');
		    $this->assertEquals($_log['request_uri'], self::$_LuaScriptBaseUrl.$_linkRandom, 'Log request_uri is invalid');
		    $this->assertEquals($_log['http_user_agent'], $_curlOpt[CURLOPT_USERAGENT], 'Log http_user_agent is invalid');
		
		    $this->assertNotEmpty($_log['request_data'], 'Session log request_data is empty');
		    
		    if(!empty($_log['request_data'])){
		        
		        $_request_data = unserialize($_log['request_data']);
		        $this->assertInternalType('array',$_request_data);
		        $this->assertArrayHasKey('param1', $_request_data);
		        $this->assertEquals($_request_data['param1'], 'param1');
		        $this->assertArrayHasKey('param2', $_request_data);
		        $this->assertEquals($_request_data['param2'], 'param2');
		        
		        //print_r($_request_data);
		    }
		
		}
		
		
		
		
		
		
		
    }
    
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Yii::app()->db->createCommand('DELETE FROM user_session')->execute();
    }
    
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: TtargetluaSessionExampleTest.php
**/