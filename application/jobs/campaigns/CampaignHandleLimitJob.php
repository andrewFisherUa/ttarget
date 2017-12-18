<?php

/**
 * Удаляет кампанию из редис если это необходимо
 * @todo необходимость в этом отпадет когда переделаем добавление/обновление/удаление на синхронизацию (как в offers)
 */
class CampaignHandleLimitJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'campaign_id' => Campaigns::$id,
     *            )
     */
    public $args = array();

    /**
     * @var Campaigns
     */
    private $campaign;

    public function perform()
    {
        if ($this->canPerform()) {
            $this->getCampaign()->handleLimit();
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return $this->getCampaign() != null;
    }

    /**
     * Возвращает объект кампании по переданному идентификатору
     *
     * @return Campaigns
     */
    private function getCampaign()
    {
        if (!isset($this->campaign)) {
            if (!isset($this->args['campaign_id'])) {
                return null;
            }
            $this->campaign = Campaigns::model()->findByPk($this->args['campaign_id']);
        }
        return $this->campaign;
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}