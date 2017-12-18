<?php

/**
 * Удаляет тизер из бд и редиса
 */
class TeaserDelFromDbJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'teaser_id' => Teasers::$id
     *            )
     *
     */
    public $args = array();

    public function perform()
    {
        $teaser = $this->getTeaser();
        if ($teaser && $teaser->is_deleted) {

            RedisTeaser::instance()->delLink($teaser);

            $delJob = new TeaserDelFromRedisJob();
            $delJob->args = array(
                'teaser_id' => $teaser->id,
                'cities' => $this->args['cities'],
                'countries' => $this->args['countries']
            );
            $delJob->perform();

            $teaser->delete();
        }
    }

    /**
     * Возвращает объект тизера по переданному идентификатору
     *
     * @return Teasers
     */
    private function getTeaser()
    {
        if (!isset($this->args['teaser_id'])) {
            return null;
        }

        return Teasers::model()->findByPk($this->args['teaser_id']);
    }
}