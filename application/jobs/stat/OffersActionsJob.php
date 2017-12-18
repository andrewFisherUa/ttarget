<?php

/**
 * Фиксирует клики
 */
class OffersActionsJob
{
    /**
     */
    public $args = array();

    /** @var  OffersUsers */
    private $offerUser;

    public function perform()
    {
        if (!$this->validateArgs()) {
            return;
        }

        $offerUser = $this->getOfferUser();

        $params = array(
            'campaign_id' => $offerUser->offer->campaign_id,
            'offer_user_id' => $offerUser->id,
            'offer_id' => $offerUser->offer_id,
            'city_id' => $this->args['city_id'],
            'country_code' => $this->args['country_code'],
        );
        $counter = 'offers_actions';
        if($offerUser->offer->lead_status == Offers::LEAD_STATUS_MODERATION) {
            $counter = 'offers_moderation_actions';
        }
        ReportHandler::addCounter($counter, $params, 1);

        $sql = ReportHandler::createUpdateCounterSql($counter);
        if($offerUser->offer->lead_status != Offers::LEAD_STATUS_MODERATION) {
            $sql .= Campaigns::createUpdateSql($offerUser->offer->campaign_id, $counter, 1);
        }

        $sql .= ActionsLog::model()->createInsertSql(
            array_merge($params, array(
                'date' => date('Y-m-d H:i:s', $this->args['timestamp']),
                'action_id'   => $offerUser->offer->action_id,
                'status'      => $offerUser->offer->lead_status == Offers::LEAD_STATUS_MODERATION ?
                    ActionsLog::STATUS_MODERATION : ActionsLog::STATUS_ACCEPTED,
                'ip'          => sprintf('%u', ip2long($this->args['ip'])),
                'track_id'    => $this->args['track_id']
            ))
        );

        // Отправляем все завпросы одной пачкой
        Yii::app()->mysqli->multiQuery($sql);
        Yii::app()->mysqli->client()->close();

        $offerUser->handleLimit();
    }

    /**
     * @return bool Валидирует переданные параметры
     * @throws CException
     */
    private function validateArgs()
    {
        if(isset($this->args['offer_user_id']) && $this->getOfferUser() !== null){
            return true;
        }else{
            throw new CException('Not valid args: '.print_r($this->args, true));
        }
    }

    private function getOfferUser()
    {
        if (!isset($this->offerUser)) {
            if (
                !isset($this->args['offer_user_id']) ||
                !isset($this->args['track_id'])
            ) {
                return null;
            }
            $this->offerUser = OffersUsers::model()->with("offer")->findByPk($this->args['offer_user_id']);
        }

        return $this->offerUser;
    }

}