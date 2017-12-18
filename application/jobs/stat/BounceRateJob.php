<?php

/**
 * Следит за увеличением отказов
 */
class BounceRateJob
{
    public $args = array();
    public function perform()
    {
        $campaigns = Campaigns::model()->active()->findAll('bounce_check IS NOT NULL');
        foreach($campaigns as $campaign){
            $rateTotal = $campaign->getBounceRate();
            $rateCurrent = BounceLog::getRate($campaign->id, $campaign->bounce_check);
            $rateDiff = $rateCurrent - $rateTotal;
            if($rateDiff - $campaign->bounce_rate_diff > Campaigns::BOUNCE_NOTIFY_RATE){
                SMail::sendMail(
                    Yii::app()->params->notifyEmail,
                    'Увеличение показателя отказов для кампании "'.$campaign->name.'" до +'.round($rateDiff, 2).'%',
                    'CampaignBounceRate',
                    array(
                        'rateTotal' => $rateTotal,
                        'rateCurrent' => $rateCurrent,
                    )
                );
            }
            if($rateDiff > Campaigns::BOUNCE_NOTIFY_RATE){
                $campaign->updateByPk($campaign->id, array('bounce_rate_diff' => $rateDiff));
            }elseif($campaign->bounce_rate_diff != 0){
                $campaign->updateByPk($campaign->id, array('bounce_rate_diff' => 0));
            }
        }
    }
}