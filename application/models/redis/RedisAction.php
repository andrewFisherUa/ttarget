<?php

/**
 * Класс для работы с данными цели в редис
 */
class RedisAction extends RedisAbstract
{
    /**
     * hash для данных цели
     */
    const KEY_ACTION = 'ttarget:actions:{action_id}';

    /**
     * list для id целей кампании
     */
    const KEY_CAMPAIGN_ACTIONS = 'ttarget:campaigns:{campaign_id}:actions';

    /**
     * @param string $class
     *
     * @return RedisAction
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * Возващает ключ данных цели
     *
     * @param int $actionId
     *
     * @return string
     */
    public function getActionKey($actionId)
    {
        return str_replace(
            '{action_id}',
            $actionId,
            self::KEY_ACTION
        );
    }

    /**
     * Возващает ключ целей кампании
     *
     * @param int $campaignId
     *
     * @return string
     */
    public function getCampaignActionsKey($campaignId)
    {
        return str_replace(
            '{campaign_id}',
            $campaignId,
            self::KEY_CAMPAIGN_ACTIONS
        );
    }

    /**
     * Добавляет цель
     *
     * @param CampaignsActions $action
     *
     * @return bool
     */
    public function addAction(CampaignsActions $action)
    {
        $encryptedId = $action->getEncryptedId();

        $key = $this->getCampaignActionsKey($action->campaign_id);
        $this->redis()->sAdd($key, $encryptedId);
        $key = $this->getActionKey($encryptedId);
        $this->redis()->hMset($key, $action->getAttributes(array('id', 'target_type', 'target_match_type', 'target', 'campaign_id')));
    }

    /**
     * Удаляет цель
     *
     * @param CampaignsActions $action
     *
     * @return bool
     */
    public function delAction(CampaignsActions $action)
    {
        $encryptedId = Crypt::encryptUrlComponent($action->id);

        $key = $this->getCampaignActionsKey($action->campaign_id);
        $this->redis()->sRem($key, $encryptedId);
        $key = $this->getActionKey($encryptedId);
        $this->redis()->del($key);
    }
}