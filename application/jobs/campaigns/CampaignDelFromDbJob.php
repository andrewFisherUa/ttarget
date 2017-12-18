<?php

/**
 * Удаляет кампанию из бд и редиса
 */
class CampaignDelFromDbJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('campaign_id' => Campaigns::$id)
     *
     */
    public $args = array();

    public function perform()
    {
        $campaign = $this->getCampaign();
        if (!$campaign || !$campaign->is_deleted) {
            return;
        }

        $transaction = $campaign->getDbConnection()->beginTransaction();
        try {
            $campaign->deleteNews();
            $campaign->delete();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * Возвращает объект кампании по переданному идентификатору
     *
     * @return Campaigns
     */
    private function getCampaign()
    {
        if (!isset($this->args['campaign_id'])) {
            return null;
        }

        return Campaigns::model()->findByPk($this->args['campaign_id']);
    }
}