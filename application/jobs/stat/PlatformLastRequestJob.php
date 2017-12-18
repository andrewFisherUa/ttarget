<?php
class PlatformLastRequestJob
{
	public function perform()
	{
        $platformIds = RedisPlatform::instance()->getLastRequests();
        $params = array('last_request_date' => date('Y-m-d H:i:s'));
        foreach($platformIds as $platformId){
            Platforms::model()->updateByPk($platformId, $params);
        }
	}
}