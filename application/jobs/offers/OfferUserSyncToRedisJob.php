<?php

/**
 * Синхронизирует состояние OffersUsers в redis
 */
class OfferUserSyncToRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('offer_id' => Offers::$id)
     *
     */
    public $args = array();

    /**
     * @var OffersUsers
     */
    private $offerUser;

    public function perform()
    {
        if (!$this->canPerform()) {
            return;
        }

        $offerUser = $this->getOfferUser();
        if($offerUser->isActive() && !$offerUser->isLimitExceeded(true)) {
            RedisLimit::instance()->del($offerUser);
            RedisOffer::instance()->addOfferUser($offerUser);
        }else{
            RedisOffer::instance()->delOfferUser($offerUser);
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return $this->getOfferUser() !== null;
    }

    private function getOfferUser()
    {
        if (!isset($this->offerUser)) {
            if (!isset($this->args['offer_user_id'])) {
                return null;
            }
            $this->offerUser = OffersUsers::model()->findByPk($this->args['offer_user_id']);
        }

        return $this->offerUser;
    }
}