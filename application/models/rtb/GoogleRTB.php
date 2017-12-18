<?php

require_once(dirname(__FILE__) . '/../../extensions/Google/autoload.php');

class GoogleRTB
{
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function creativeCreate()
	{
		$service = new Google_Service_AdExchangeBuyer;

		$creative = new Google_Service_AdExchangeBuyer_Creative();

		$creative->accountId = 324423;
		$creative->buyerCreativeId = "fg_4782";
		$creative->advertiserName = "Evne Developers";
		$creative->HTMLSnippet = "<html><body><a href=&quot;https://www.example.com&quot;>Hi there!</a></body></html>";
		$creative->clickThroughUrl = "http://tox.ttarget.ru/RTBGoogleReceiver/action/1";
		$creative->width = 250;
		$creative->height = 400;

		$creative = $service->creatives->insert($creative);

		print_r($creative);
	}

	public static function getCreative()
	{
		$service = new Google_Service_AdExchangeBuyer;

		try {
			$creative = $service->creatives->get($values['account_id'], $values['buyer_creative_id']);
			print_r($creative);
		} catch (apiException $ex) {
			if ($ex->getCode() == 404 || $ex->getCode() == 403) {
				print '<h1>Creative not found or can\'t access creative</h1>';
			} else {
				throw $ex;
			}
		}
	}

	public static function test() {
		//require_once 'Google/Service/AdExchangeBuyer.php';

		session_start();
		/*
		 * You can retrieve these from the Google Developers Console.
		 *
		 * See README.md for details.
		 */

		$service_account_name = '827350512882-54p88kfeg46o01htvkejepg256uu6gl4@developer.gserviceaccount.com';
		$key_file_location = dirname(__FILE__) . '/../../extensions/Google/API.p12';

		$client = new Google_Client();
		$client->setApplicationName('Ad Exchange Buyer REST First API Request');
		if (isset($_SESSION['service_token'])) {
			$client->setAccessToken($_SESSION['service_token']);
		}

// Use the P12 to create credentials
		$key = file_get_contents($key_file_location);
		$cred = new Google_Auth_AssertionCredentials(
			$service_account_name,
			array('https://www.googleapis.com/auth/adexchange.buyer'),
			$key
		);

// add the credentials to the client
		$client->setAssertionCredentials($cred);
		if($client->getAuth()->isAccessTokenExpired()) {
			$client->getAuth()->refreshTokenWithAssertion($cred);
		}
		$_SESSION['service_token'] = $client->getAccessToken();

// Use the authorized client to create a client for the API service
		$service = new Google_Service_AdExchangeBuyer($client);
		if ($client->getAccessToken()) {
			// Call the Accounts resource on the service to retrieve a list of
			// Accounts for the service account.
			$result = $service->accounts->listAccounts();
			print '<h2>Listing of user associated accounts</h2>';
			if (!isset($result['items']) || !count($result['items'])) {
				print '<p>No accounts found</p>';
				return;
			} else {
				foreach ($result['items'] as $account) {
					printf('<pre>');
					print_r($account);
					printf('</pre>');
				}
			}
		}

		echo "\n";

	}
}
?>
