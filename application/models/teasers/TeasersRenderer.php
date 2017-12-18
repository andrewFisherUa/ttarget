<?php
/**
 * Возвращает html-код тизера
 */
class TeasersRenderer
{
    /**
     * @var string Загруженный файл шаблона тизеров
     */
    private $template;

    private static $instance;

    private function __construct()
    {
        if (!is_file(Yii::app()->params['teaserTemplatePath'])) {
            throw new Exception("Wrong teaser template path");
        }
        $this->template = file_get_contents(Yii::app()->params['teaserTemplatePath']);
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
     * Подготавливает html-вывод тизера
     *
     * @param Teasers $teaser
     *
     * @return mixed
     */
    public function render(Teasers $teaser)
    {
        return str_replace(
            array('{url}', '{link}', '{image_url}', '{title}', '{description}', '{teaser_id}'),
            array(
                Yii::app()->params['teaserLinkBaseUrl'],
                $teaser->getEncryptedLink(),
                (@filesize(Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . $teaser->picture) > 0 ?
                    addcslashes($teaser->getImageUrl(), '\'"')
                    : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs%3D'
                ),
                $teaser->title,
                $teaser->description,
                $teaser->id
            ),
            $this->template
        );
    }
}