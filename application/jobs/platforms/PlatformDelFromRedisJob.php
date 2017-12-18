<?php

/**
 * Удаляет платформу из redis
 */
class PlatformDelFromRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('platform_id' => Platforms::$id)
     *
     */
    public $args = array();

    public function perform()
    {
        if (!($platform = $this->getPlatform())) {
            return;
        }

        RedisPlatform::instance()->delete($platform);
    }

    /**
     * Возвращает объект новости по переданному идентификатору
     *
     * @return Platforms
     */
    private function getPlatform()
    {
        if (!isset($this->args['platform_id'])) {
            return null;
        }

        return Platforms::model()->findByPk($this->args['platform_id']);
    }
}