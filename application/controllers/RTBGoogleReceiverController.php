<?php

require_once (dirname(__FILE__) . '/../extensions/Google/message/realtime-bidding.proto.php');

/**
 *
 */
class RTBGoogleReceiverController extends Controller
{
	public function actionIndex()
	{
		$time = -microtime(true);

		if ( Yii::app()->request->isPostRequest) {
			$request = BidRequest::parseFromString(file_get_contents("php://input"));

			$bidRequestId = GoogleBidRequest::createBidRequest($request);

			if (!empty($request)) {
				$time += microtime(true);
				//file_put_contents('/home/tox/log.txt', json_encode(0-$time) );
				try {
					$response = new BidResponse();
					$response->setProcessingTimeMs( 0 - $time );
					$slots = $request->getAdslot();

					$ad = new BidResponse_Ad();
					$ad->setHtmlSnippet("<a href='%%CLICK_URL_UNESC%%http%3A%2F%2Fmy.adserver.com%2Fsome%2Fpath%2Fhandleclick%3Fclick%3Dclk'><img src='http://i.imgur.com/OJx87.png' width='120'/></a>");
					$ad->setBuyerCreativeId("my-creative-1234ABCD");
					$ad->appendVendorType(113);
					$ad->appendCategory(3);
					$ad->appendClickThroughUrl("http://www.google.com");
					$adslot = new BidResponse_Ad_AdSlot(array("id"=>$slots[0]->getId(), "max_cpm_micros"=>1000));
					$ad->appendAdslot($adslot);

					$response->appendAd($ad);
					$response->setDebugString("Helo World");

					echo $response->serializeToString();
				} catch (Exception $e) {
					error_log($e->getMessage());
					error_log($e->getTraceAsString());
				}
			}
		}
	}

		public function actionShow($id = null) {
		if ( !empty($id) ) {
			$creative = CampaignsCreatives::model()->findByAttributes(array('id' => $id));
			$click_url = Yii::app()->params['GoogleRTBClickUrl'] . $creative->id;
			$file = '/i/creatives/'.$creative->filename;

			YandexPlatforms::addGoolePlatform();

			echo "<a href='$click_url'><img src='$file'></a>";

			if ( $creative->last_bid_request_id != 0 || 1==1 ) {
				Campaigns::addShow($creative->campaign_id);
				CampaignsCreatives::addShow($id);
				//CampaignsCreativeViewGoogle::addShow($id);

				//$googleBidRequestData = YandexBidRequest::getBidRequestDataById( $creative->last_bid_request_id );

				ReportRtbDaily::addGoogleShow( $creative->campaign_id, $creative->id);
				ReportRtbDailyByCampaignAndPlatform::addGoogleShow( $creative->campaign_id);
				ReportRtbDailyByCampaignAndPlatformAndCountry::addGoogleShow( $creative->campaign_id);
				ReportRtbDailyByCampaignAndPlatformAndCity::addGoogleShow( $creative->campaign_id);

				ReportDailyByCampaign::addShow($creative->campaign_id);
			}
		}
	}

	public function actionAction( $id = null ) {
		if ($id != null ) {
			$creative = CampaignsCreatives::model()->findByAttributes(array('id' => $id));

			if ( $creative->last_bid_request_id != 0 || 1==1 ) {
				Campaigns::addClick($creative->campaign_id);
				CampaignsCreatives::addClick( $id );
				//CampaignsCreativeClickGoogle::addClick( $id );

				//$googleBidRequestData = GoogleBidRequest::getBidRequestDataById( $creative->last_bid_request_id );

				ReportRtbDaily::addGoogleClick( $creative->campaign_id, $creative->id );
				ReportRtbDailyByCampaignAndPlatform::addGoogleClick( $creative->campaign_id );
				ReportRtbDailyByCampaignAndPlatformAndCountry::addGoogleClick( $creative->campaign_id );
				ReportRtbDailyByCampaignAndPlatformAndCity::addGoogleClick( $creative->campaign_id );
				PlatformsRtbCpc::addCost( $creative->cost, array() );

				ReportDailyByCampaign::addClick( $creative->campaign_id);

			}

			header("Location: $creative->link");
		}
	}
}