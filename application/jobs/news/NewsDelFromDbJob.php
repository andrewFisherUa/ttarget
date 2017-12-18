<?php

/**
 * Удаляет новость из бд и редиса
 */
class NewsDelFromDbJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('teaser_id' => Teasers::$id)
     *
     */
    public $args = array();

    public function perform()
    {
        $news = $this->getNews();
        if (!$news || !$news->is_deleted) {
            return;
        }

        $news->delete();
    }

    /**
     * Возвращает объект новости по переданному идентификатору
     *
     * @return News
     */
    private function getNews()
    {
        if (!isset($this->args['news_id'])) {
            return null;
        }

        return News::model()->findByPk($this->args['news_id']);
    }
}