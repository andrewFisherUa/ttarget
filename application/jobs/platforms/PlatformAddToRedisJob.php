<?php

/**
 * Добавляет платформу в редис
 */
class PlatformAddToRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('platform_id' => Platforms::$id)
     *
     */
    public $args = array();

    /**
     * @var Platforms
     */
    private $platform;


    public function perform()
    {
        if (!$this->canPerform()) {
            return;
        }

        $platform = $this->getPlatform();

        $this->addEncryptedId($platform);
        $this->setHosts($platform);
        $this->addTeasers($platform);

    }

    /**
     * Добавляет закодированный id площадки
     * @param Platforms $platform
     */
    public function addEncryptedId(Platforms $platform)
    {
        RedisPlatform::instance()->addEncryptedId($platform);
    }

    /**
     * Устанавливает новые урлы хостов площадки, предварительно удалив старые
     *
     * @param Platforms $platform
     */
    public function setHosts(Platforms $platform)
    {
        $this->redis()->multi();
            RedisPlatform::instance()->delHosts($platform->id);
            RedisPlatform::instance()->addHosts($platform);
        $this->redis()->exec();
    }

    /**
     * Добавляет тизеры на платформу в редис
     *
     * @param Platforms $platform
     */
    public function addTeasers(Platforms $platform)
    {
        $news = News::model()->notDeleted()
                             ->active()
                             ->with('campaign:notDeleted:active')
                             ->findAll();

        foreach ($news as $n) {
            $this->redis()->multi(Redis::PIPELINE);
                $this->addTeasersToPlatform($n, $platform);
            $this->redis()->exec();
        }
    }

    /**
     * Добавляет тизеры к платформе
     *
     * @param News $news
     * @param Platforms $platform
     */
    private function addTeasersToPlatform(News $news, Platforms $platform)
    {
        $teasers = Teasers::model()->notDeleted()
                                   ->active()
                                   ->findAllByNewsIdAndPlatformId($news->id, $platform->id);

        if (empty($teasers)) {
            return;
        }

        foreach ($teasers as $teaser) {
            RedisPlatform::instance()->addTeaserToNewsSets($teaser, array($platform->id));
            RedisPlatform::instance()->addTeaserToCampaignSets($teaser, array($platform->id));
        }

        $cities     = Cities::model()->getAllByCampaignId($news->campaign_id);
        $countries  = Countries::model()->getAllCodesCampaignId($news->campaign_id);

        if (!empty($cities) || !empty($countries)) {
            RedisPlatform::instance()->addCampaignToCitiesSets($news->campaign_id, $platform->id, $cities);
            RedisPlatform::instance()->addCampaignToCountriesSets($news->campaign_id, $platform->id, $countries);
            RedisPlatform::instance()->addNewsToCitiesSets($news->id, $platform->id, $cities);
            RedisPlatform::instance()->addNewsToCountriesSets($news->id, $platform->id, $countries);
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($platform = $this->getPlatform()) &&
                $platform->is_active &&
                !$platform->is_deleted;
    }

    /**
     * Возвращает объект платформы по переданному идентификатору
     *
     * @return Platforms
     */
    private function getPlatform()
    {
        if (!isset($this->platform)) {

            if (!isset($this->args['platform_id'])) {
                return null;
            }

            $this->platform = Platforms::model()->findByPk($this->args['platform_id']);
        }

        return $this->platform;
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}