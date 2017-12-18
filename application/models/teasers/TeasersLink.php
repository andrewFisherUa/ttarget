<?php
/**
 * Генерирует урл на тизер
 */
class TeasersLink
{
    /**
     * @var string базовый урл для ссылок на новости
     */
    private $linkBaseUrl;

    private static $instance;

    private function __construct()
    {
        $this->linkBaseUrl  = Yii::app()->params['teaserLinkBaseUrl'];
    }

    private function __clone() {}
    private function __sleep() {}
    private function __wakeup() {}

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Возвращает абсолютный шифрованный урл тизер
     *
     * @param string $link
     * @param boolean $encrypt true
     *
     * @return string
     */
    public function getAbsoluteUrl($link, $encrypt = true)
    {
        return $this->linkBaseUrl . ($encrypt ? Crypt::encryptUrlComponent($link) : $link);
    }
}