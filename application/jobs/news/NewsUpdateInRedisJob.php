<?php

/**
 * Обновляет данные новости в редис
 */
class NewsUpdateInRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'news_id' => News::$id,
     *              'clean_attributes'      => array('attribute' => 'value'),
     *            )
     */
    public $args = array();

    /**
     * @var News
     */
    private $news;

    public function perform()
    {
        if ($this->canPerform()) {

            $this->updateUrl();
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($news = $this->getNews()) &&
                !$news->is_deleted &&
                $news->is_active &&
                Campaigns::model()->checkIsActive($news->campaign_id);
    }

    /**
     * Обновить урл новости
     */
    private function updateUrl()
    {
        if ($this->isDirty('url') || $this->isDirty('url_type')) {
            $this->redis()->multi();
                foreach ($this->getNews()->activeTeasers as $teaser) {
                    RedisTeaser::instance()->addLink($teaser);
                }
            $this->redis()->exec();
        }
    }


    /**
     * Возвращает объект новости по переданному идентификатору
     *
     * @return News
     */
    private function getNews()
    {
        if (!isset($this->news)) {

            if (!isset($this->args['news_id'])) {
                return null;
            }

            $this->news = News::model()->findByPk($this->args['news_id']);
        }

        return $this->news;
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
        return $this->getCleanAttribute($attribute) != $this->getNews()->$attribute;
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

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}