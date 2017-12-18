<?php

/**
 * Фиксирует клики
 */
class OffersClicksJob
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

        if (isset($this->args['track_id'])) {
            $track = Tracks::getById($this->args['track_id']);
        }

        /**
         * Тут надо быть осторожным. Добавление ключей может привести к обновлению не связанных с офферами отчетов.
         * Например, если добавить campaign_id то обновится ReportDailyByCampaign.
         * Похоже этот механизм в будущем придется переработать.
         */
        ReportHandler::addCounter(
            'offers_clicks',
            array(
                'offer_id' => $offerUser->offer_id,
                'offer_user_id' => $offerUser->id,
//                'city_id' => $this->args['city_id'],
//                'country_code' => $this->args['country_code'],
            ),
            1
        );
        $sql = ReportHandler::createUpdateCounterSql('offers_clicks');
        $sql .= Campaigns::createUpdateSql($offerUser->offer->campaign_id, 'offers_clicks', 1);

        // Добавляем данные трэк-кода в БД (для востановления и ссылок)
        if (isset($track)) {
            $sql .= Tracks::createSql(array_merge($track, array(
                'referrer_url' => isset($this->args['referrer']) ? $this->args['referrer'] : ''
            )));
        }

        // Отправляем все завпросы одной пачкой
        Yii::app()->mysqli->multiQuery($sql);
        Yii::app()->mysqli->client()->close();

//        IpLog::model()->add($this->args['remote_addr'], $this->args['timestamp'], $news['id'], $platform['id']);
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
            if (!isset($this->args['offer_user_id'])) {
                return null;
            }
            $this->offerUser = OffersUsers::model()->with("offer")->findByPk($this->args['offer_user_id']);
        }

        return $this->offerUser;
    }

}