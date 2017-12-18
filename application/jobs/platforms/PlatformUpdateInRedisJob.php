<?php

/**
 * Обновляет данные платформы в редис
 */
class PlatformUpdateInRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'platform_id' => Platforms::$id,
     *              'clean_attributes' => array('attribute' => 'value'),
     *            )
     */
    public $args = array();

    /**
     * @var Platforms
     */
    private $platform;

    public function perform()
    {
        if ($this->canPerform()) {
            $this->updateHosts($this->getPlatform());
            $this->updateSegment($this->getPlatform());
        }
    }

    /**
     * Обноваляет список хостов площадки
     *
     * @param Platforms $platform
     */
    private function updateHosts(Platforms $platform)
    {
        if ($this->isDirty('hosts')) {
            $addJob = new PlatformAddToRedisJob();
            $addJob->setHosts($platform);
        }
    }

    /**
     * Обновляет сегмент платформы, если изменился
     *
     * @param Platforms $platform
     */
    private function updateSegment(Platforms $platform)
    {
        // изменились сегменты
        if(array_diff($this->args['tagIds'], $this->args['cleanTagIds']) !== array_diff($this->args['cleanTagIds'], $this->args['tagIds'])){
            RedisPlatform::instance()->deleteNewsByPlatform($platform);
            RedisPlatform::instance()->deleteCampaignsByPlatform($platform);
            RedisPlatform::instance()->deleteTeasersByPlatform($platform);
            RedisPlatform::instance()->deleteCampaignTeasersByPlatform($platform);
            $addJob = new PlatformAddToRedisJob();
            $addJob->addTeasers($platform);
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($platform = $this->getPlatform()) &&
                !$platform->is_deleted &&
                $platform->is_active;
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
     * Проверяет изменился ли атрибут
     *
     * @param string $attribute
     *
     * @return bool
     */
    private function isDirty($attribute)
    {
        return $this->getCleanAttribute($attribute) != $this->getPlatform()->$attribute;
    }

    /**
     * Возвращает исходное значение аттрибута
     *
     * @param string $attribute
     *
     * @return mixed
     */
    private function getCleanAttribute($attribute)
    {
        return (isset($this->args['clean_attributes'][$attribute]) ? $this->args['clean_attributes'][$attribute] : null);
    }
}