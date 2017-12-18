<?php

/**
 *
 */
class RTBReceiverController extends Controller
{
    public function actionIndex()
    {
        $time = -microtime(true);
        if (Yii::app()->request->isPostRequest) {
            $request = file_get_contents("php://input");

            if (!empty($request)) {
                $request = CJSON::decode($request, true);

                $bidRequestId = YandexBidRequest::createBidRequest($request);
                if (isset($request['id'])) {
                    $places = array();
                    foreach ($request['imp'] as $imp) {
                        $places[$imp['id']] = array(
                            'id' => $imp['id'],
                            'width' => $imp['banner']['w'],
                            'height' => $imp['banner']['h'],
                            'bidfloor' => isset($imp['bidfloor']) ? $imp['bidfloor'] : 0,
                        );
                    }

                    $results = CampaignsCreatives::model()->getCreativesForRTB($places, $request['device']['userdata']);

                    if (!empty($results)) {
                        CampaignsCreatives::model()->updateCreativesBidRequestId($bidRequestId, array_keys($results));
                        $bids = array();
                        foreach ($results as $result) {
                            /** @var CampaignsCreatives $creative */
                            $creative = $result['creative'];
                            $creativeData = json_decode($creative->creative_data);

                            if (isset($creativeData, $creativeData->Data, $creativeData->Properties, $creativeData->Token)) {
                                $bids[] = (object)array(
                                    "id" => $result['placeId'],
                                    "adid" => $creative->rtb_id,
                                    "price" => $creative->cost,
                                    "adm" => base64_decode($creativeData->Data),
                                    "properties" => $creativeData->Properties,
                                    "token" => $creativeData->Token,
                                    "view_notice" => Yii::app()->params['YandexRTBShowUrl'] . $creative->id,
                                    "banner" => (object)array(
                                        "w" => $places[$result['placeId']]['width'],
                                        "h" => $places[$result['placeId']]['height'],
                                    ),
                                );
                            }
                        }
                        //file_put_contents('/home/tox/log.txt', json_encode($view_notice_url) );
                        $time += microtime(true);
//						file_put_contents('/home/tox/log.txt', json_encode(number_format($time, 3, '.', '')) );
                        $time = number_format($time, 3, '.', '');

                        if ($time <= 0.250 && !empty($bids)) {
                            $responseData = (object)array(
                                "bidid" => $bidRequestId,
                                "id" => $request['id'],
                                "cur" => "RUB",
                                "units" => 1, // bid per 1k
                                "bidset" => array(
                                    (object)array(
                                        "bid" => $bids,
                                    ),
                                ),
                            );

                            header('Content-type: text/html; charset=UTF-8');
                            echo json_encode($responseData);
                        } else {
                            http_response_code(204);
                        }
                    } else {
                        http_response_code(204);
                    }
                }
            }
        } else {

        }
    }

	public function actionShow($id = null) {
		if ( !empty($id) ){
			$creative = CampaignsCreatives::model()->findByAttributes(array('id' => $id));

			if ( $creative->last_bid_request_id != 0 ) {
				Campaigns::addShow($creative->campaign_id);
				CampaignsCreatives::addShow($id);
				CampaignsCreativeViewYandex::addShow($id);

				$yandexBidRequestData = YandexBidRequest::getBidRequestDataById($creative->last_bid_request_id);
				ReportRtbDaily::addShow($creative->campaign_id, $creative->id, $yandexBidRequestData);
				ReportRtbDailyByCampaignAndPlatform::addShow($creative->campaign_id, $yandexBidRequestData);
				ReportRtbDailyByCampaignAndPlatformAndCountry::addShow($creative->campaign_id, $yandexBidRequestData);
				ReportRtbDailyByCampaignAndPlatformAndCity::addShow($creative->campaign_id, $yandexBidRequestData);

				ReportDailyByCampaign::addShow($creative->campaign_id);
			}
		}
	}

	public function actionAction( $id = null ) {
		if ($id != null ) {
			$creative = CampaignsCreatives::model()->findByAttributes(array('id' => $id));

			if ( $creative->last_bid_request_id != 0 ) {
				Campaigns::addClick($creative->campaign_id);
				CampaignsCreatives::addClick( $id );
				CampaignsCreativeClickYandex::addClick( $id );

				$yandexBidRequestData = YandexBidRequest::getBidRequestDataById( $creative->last_bid_request_id );
				ReportRtbDaily::addClick( $creative->campaign_id, $creative->id, $yandexBidRequestData );
				ReportRtbDailyByCampaignAndPlatform::addClick( $creative->campaign_id, $yandexBidRequestData );
				ReportRtbDailyByCampaignAndPlatformAndCountry::addClick( $creative->campaign_id, $yandexBidRequestData );
				ReportRtbDailyByCampaignAndPlatformAndCity::addClick( $creative->campaign_id, $yandexBidRequestData );
				PlatformsRtbCpc::addCost( $creative->cost, $yandexBidRequestData);

				ReportDailyByCampaign::addClick( $creative->campaign_id);
			}

			header("Location: $creative->link");
		}
	}
}