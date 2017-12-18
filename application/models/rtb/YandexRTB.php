<?php


class YandexRTB
{
	public static $client;

	const AUDIO_TEMPLATE_ID = 23;
	const FLASH_TEMPLATE_ID = 23;
	const IMAGE_TEMPLATE_ID = 23;

	const DEBUG = 1;

	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	public static function logonGet()
	{
		self::$client = Yii::app()->xmlrpc->load(Yii::app()->params['YandexRTBUrl']);
		$respLogonId = self::$client->send(xmlrpc_encode_request('BannerStore.CreateLogon', array('name' => Yii::app()->params['YandexRTBName'], 'ragoldfish', 'password' => Yii::app()->params['YandexRTBPassword'])));

		if (!$respLogonId->faultCode()) {
			$v = $respLogonId->value();
			return $v->scalarval();
		} else {
			if ( self::DEBUG == true) {
				print '-> CreativeCreate | An error occurred: ';
				print 'Code: ' . htmlspecialchars($respLogonId->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function creativeCreate($mediaFileType, $mediaFileType, $creativeName, $expireDate, $fileId, $click_url)
	{
		$logonId = self::logonGet();

		$validation = True;

		xmlrpc_set_type($expireDate, 'datetime');

		if ($logonId) {
			if ($mediaFileType == CampaignsCreatives::TYPE_IMAGE) {
				$params = array($logonId, array(
					'templateId' => (int)self::IMAGE_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array("")
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'STUB',
							'paramValue' => array($fileId)
						),
					)
				), $validation);
			} elseif ($mediaFileType == CampaignsCreatives::TYPE_VIDEO) {
				$params = array($logonId, array(
					'templateId' => (int)self::VIDEO_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array("")
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'STUB',
							'paramValue' => array($fileId)
						),
						array(
							'paramName' => 'VIDEO',
							'paramValue' => array($fileId)
						),
					)
				), $validation);

			} elseif ($mediaFileType == CampaignsCreatives::TYPE_AUDIO) {
				$params = array($logonId, array(
					'templateId' => (int)self::AUDIO_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array("")
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'AUDIO',
							'paramValue' => array($fileId)
						),
					)
				), $validation);
			}


			$request = xmlrpc_encode_request('BannerStore.CreativeCreate',
				$params,
				array('encoding' => 'utf-8', 'escaping' => 'markup')
			);

			$creative = self::$client->send($request);

			if (!$creative->faultCode()) {
				return php_xmlrpc_decode($creative->value());
			} else {
				if ( self::DEBUG == true) {
					print '-> CreativeCreate | An error occurred: ';
					print 'Code: ' . htmlspecialchars($creative->faultCode());
					echo "\n";
				}
			}

			return false;
		}
	}

	public static function creativeGet($RTBCreativeId, $versionType)
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeGet', array($logonId, $RTBCreativeId, $versionType)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> CreativeGet | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function creativeGetList($sortField, $windowStart, $windowLength)
	{
		$logonId = self::logonGet();

		$list = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeGetList', array($logonId, $sortField, $windowStart, $windowLength)));

		if (!$list->faultCode()) {
			$creativeInScope = php_xmlrpc_decode($list->value()->scalarval()['creativesInScope']);
			$list = php_xmlrpc_decode($list->value()->scalarval()['list']);

			return array('creativeInScope' => $creativeInScope, 'list' => $list);
		} else {
			if ( self::DEBUG == true) {
				print '-> GetCreativeByNmb | An error occurred: ';
				print 'Code: ' . htmlspecialchars($list->faultCode());
				echo "\n";
			}

		}

		return false;
	}

	public static function getCreativeByNmb($RTBCreativeId)
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.GetCreativeByNmb', array($logonId, $RTBCreativeId)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> GetCreativeByNmb | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}
		return false;
	}

	public static function fileGetList($sortField, $windowStart, $windowLength)
	{
		$logonId = self::logonGet();

		$list = self::$client->send(xmlrpc_encode_request('BannerStore.FileGetList', array($logonId, $sortField, $windowStart, $windowLength)));

		if (!$list->faultCode()) {
			return php_xmlrpc_decode($list->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> FileGetList | An error occurred: ';
				print 'Code: ' . htmlspecialchars($list->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function fileUpload($fileName, $tag, $file)
	{
		$logonId = self::logonGet();

		$handle = fopen(Yii::app()->params->rtbCreativeFileUploadsPath . DIRECTORY_SEPARATOR . $file, "rb");
		$data = fread($handle, filesize(Yii::app()->params->rtbCreativeFileUploadsPath . DIRECTORY_SEPARATOR . $file));

		$request = new xmlrpcmsg('BannerStore.FileUpload');
		$request->addParam(new xmlrpcval($logonId));
		$request->addParam(new xmlrpcval($fileName));
		$request->addParam(new xmlrpcval($tag));
		$request->addParam(new xmlrpcval($data, 'base64'));

		//print_r($request);
		//print_r( xmlrpc_encode($request) );

		$file = self::$client->send($request);

		if (!$file->faultCode()) {
			return $file->value()->scalarval();
		} else {
			if ( self::DEBUG == true) {
				print '-> FileUpload | An error occurred: ';
				print 'Code: ' . htmlspecialchars($file->faultCode());
				echo "\n";
			}

		}

		return false;
	}

	public static function creativeUpdate($RTBCreativeId, $mediaFileType, $creativeName, $fileId, $click_url)
	{
		$logonId = self::logonGet();

		$validation = true;
		$checkConflicts = false;
		$dontSetParamValues = false;

		xmlrpc_set_type($expireDate, 'datetime');

		if ($logonId) {

			if ($mediaFileType == CampaignsCreatives::TYPE_IMAGE) {
				$params = array($logonId, array(
					'id' => $RTBCreativeId,
					'templateId' => (int)self::IMAGE_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array()
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'STUB',
							'paramValue' => array($fileId)
						),
					)
				), $validation, $checkConflicts, $dontSetParamValues);
			} elseif ($mediaFileType == CampaignsCreatives::TYPE_VIDEO) {
				$params = array($logonId, array(
					'id' => $RTBCreativeId,
					'templateId' => (int)self::VIDEO_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array()
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'STUB',
							'paramValue' => array($fileId)
						),
						array(
							'paramName' => 'VIDEO',
							'paramValue' => array($fileId)
						),
					)
				), $validation);
			} elseif ($mediaFileType == CampaignsCreatives::TYPE_AUDIO) {
				$params = array($logonId, array(
					'id' => $RTBCreativeId,
					'templateId' => (int)self::AUDIO_TEMPLATE_ID,
					'name' => $creativeName,
					'ExpireDate' => $expireDate,
					'paramValues' => array(
						array(
							'paramName' => 'LINK',
							'paramValue' => array($click_url)
						),
//						array(
//							'paramName' => 'PIXEL',
//							'paramValue' => array()
//						),
						array(
							'paramName' => 'ALT',
							'paramValue' => array($creativeName)
						),
						array(
							'paramName' => 'AUDIO',
							'paramValue' => array($fileId)
						),
					)
				));
			}

			$request = xmlrpc_encode_request('BannerStore.CreativeUpdate', $params, array('encoding' => 'utf-8', 'escaping' => 'markup'));

			$creative = self::$client->send($request);

			if (!$creative->faultCode()) {
				return php_xmlrpc_decode($creative->value());
			} else {
				if ( self::DEBUG == true) {
					print '-> CreativeUpdate | An error occurred: ';
					print 'Code: ' . htmlspecialchars($creative->faultCode());
					echo "\n";
				}
			}

			return false;
		}
	}

	public static function creativeUpdateTnsArticle($RTBCreativeId, array $TnsArticles)
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeUpdateTnsArticle', array($logonId, $RTBCreativeId, $TnsArticles)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> CreativeUpdateTnsArticle | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function creativeUpdateTnsBrand($RTBCreativeId, array $TnsBrands)
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeUpdateTnsBrand', array($logonId, $RTBCreativeId, $TnsBrands)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> CreativeUpdateTnsBrand | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function creativeUpdateGeo( $RTBCreativeId, array $geoItems )
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeUpdateGeo', array($logonId, $RTBCreativeId, $geoItems)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> CreativeUpdateGeo | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function templateGet($RTBTemplateId)
	{
		$logonId = self::logonGet();

		$template = self::$client->send(xmlrpc_encode_request('BannerStore.TemplateGet', array($logonId, $RTBTemplateId)));

		if (!$template->faultCode()) {
			return php_xmlrpc_decode($template->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> TemplateGet | An error occurred: ';
				print 'Code: ' . htmlspecialchars($template->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function templateGetList(array $types)
	{
		$logonId = self::logonGet();

		$templateList = self::$client->send(xmlrpc_encode_request('BannerStore.TemplateGetList', array($logonId, $types)));

		if (!$templateList->faultCode()) {
			return php_xmlrpc_decode($templateList->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> TemplateGetList | An error occurred: ';
				print 'Code: ' . htmlspecialchars($templateList->faultCode());
				echo "\n";
			}

		}

		return false;
	}

	public static function creativeRequestModeration( $RTBCreativeId )
	{
		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeRequestModeration', array($logonId, $RTBCreativeId )));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode( $creative->value() );
		} else {
			if ( self::DEBUG == true) {
				print '-> creativeRequestModeration | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function creativeRequestEdit( $RTBCreativeId )
	{

		$logonId = self::logonGet();

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.CreativeRequestEdit', array($logonId, $RTBCreativeId )));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode( $creative->value() );
		} else {
			if ( self::DEBUG == true) {
				print '-> creativeRequestEdit | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	// Справочники
	public static function geoLocationGetList()
	{
		$logonId = self::logonGet();

		$geoLocation = self::$client->send(xmlrpc_encode_request('BannerStore.GeoLocationGetList', array($logonId)));

		if (!$geoLocation->faultCode()) {
			return php_xmlrpc_decode( $geoLocation->value() );
		} else {
			if ( self::DEBUG == true) {
				print '-> GeoLocationGetList | An error occurred: ';
				print 'Code: ' . htmlspecialchars($geoLocation->faultCode());
				echo "\n";
			}

		}

		return false;
	}

	public static function tnsArticleGetList()
	{
		$logonId = self::logonGet();

		$tnsArticleList = self::$client->send(xmlrpc_encode_request('BannerStore.TnsArticleGetList', array($logonId)));

		if (!$tnsArticleList->faultCode()) {
			//return $tnsArticleList;
			return php_xmlrpc_decode( $tnsArticleList->value() );
		} else {
			if ( self::DEBUG == true) {
				print '-> TnsArticleGetList | An error occurred: ';
				print 'Code: ' . htmlspecialchars($tnsArticleList->faultCode());
				echo "\n";
			}

		}

		return false;
	}

	public static function tnsBrandGetList()
	{
		$logonId = self::logonGet();

		$tnsBrandList = self::$client->send(xmlrpc_encode_request('BannerStore.TnsBrandGetList', array($logonId)));

		if (!$tnsBrandList->faultCode()) {
			return php_xmlrpc_decode($tnsBrandList->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> TnsBrandGetList | An error occurred: ';
				print 'Code: ' . htmlspecialchars($tnsBrandList->faultCode());
				echo "\n";
			}
		}

		return false;
	}

	public static function updateCreativeSignedExpireDate( $RTBCreativeId, $newDate )
	{
		$logonId = self::logonGet();
		xmlrpc_set_type($newDate, 'datetime');

		$data = array(
			'CreativeNmb' => $RTBCreativeId,
			'ExpireDate'=> $newDate
		);

		$creative = self::$client->send(xmlrpc_encode_request('BannerStore.UpdateCreativeSignedExpireDate', array($logonId, $data)));

		if (!$creative->faultCode()) {
			return php_xmlrpc_decode($creative->value());
		} else {
			if ( self::DEBUG == true) {
				print '-> UpdateCreativeSignedExpireDate | An error occurred: ';
				print 'Code: ' . htmlspecialchars($creative->faultCode());
				echo "\n";
			}
		}
	}
}
