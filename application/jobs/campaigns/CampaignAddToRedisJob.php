<?php

/**
 * Обновляет данные кампании в редис
 */
class CampaignAddToRedisJob
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
            SyncManager::syncRelated($this->getCampaign());
            RedisLimit::instance()->del($this->getCampaign());

            $this->addTeasers();
            $this->updateScore();
            $this->addCache();
        }
    }

    public function addTeasers(){
        if(isset($this->args['addTeasers'])){
            foreach($this->getCampaign()->activeNews as $news){
                $news->addToRedis();
            }
        }
    }

    /**
     * Обновляем вес для кампании, если изменились лимиты кликов или даты.
     */
    public function updateScore()
    {
        $this->getCampaign()->updateWeight();
    }

    /**
     * Сохраняем в редис
     */
    public function addCache()
    {
        RedisCampaign::instance()->setCampaignCache($this->getCampaign());
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($campaign = $this->getCampaign()) != null && $campaign->checkIsActive($campaign->id);
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