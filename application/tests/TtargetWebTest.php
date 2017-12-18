<?php


/**
 * The base class for functional test cases.
 * In this class, we set the base URL for the test application.
 * We also provide some common methods to be used by concrete test classes.
 */
class TtargetWebTest extends CWebTestCase
{
	 
	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(Yii::app()->params->testBaseUrl);
		$this->setBrowser('*firefox');
	}
	
	protected function tearDown()
	{
		parent::tearDown();
	}
}
