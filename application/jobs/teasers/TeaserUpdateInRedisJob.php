<?php

/**
 * Обновляет данные тизера в редис
 */
class TeaserUpdateInRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'teaser_id' => Teasers::$id,
     *              'clean_attributes' => array('attribute' => 'value'),
     *              'excepted_platforms'  => array(Platforms::$id)
     *              'added_platforms'  => array(Platforms::$id)
     *            )
     */
    public $args = array();

    /**
     * @var Teasers
     */
    private $teaser;

    public function perform()
    {
        if ($this->canPerform()) {
            if($this->getTeaser()->is_external && !$this->isDirty('is_external')){
                return;
            }

            $this->updateOutput($this->getTeaser());
            if(!empty($this->args['excepted_platforms']) || $this->args['added_platforms']){
                $this->updatePlatforms($this->getTeaser());
            }
            if($this->args['new_tags_count'] !== null){
                $this->updateScore();
            }
        }
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($teaser = $this->getTeaser()) &&
               !$teaser->is_deleted &&
               $teaser->is_active &&
               News::model()->checkIsActive($teaser->news_id);
    }

    private function updateScore()
    {
        $teaser = $this->getTeaser();
        $data = RedisTeaser::instance()->getTeaserScore($teaser->id, array('shows', 'clicks'));
        $data['tagsCount'] = $this->args['new_tags_count'];
        RedisTeaser::instance()->setTeaserScore($teaser->id, $data);
    }

    /**
     * Обновить шаблон, если изменилось изображение или название тизера
     *
     * @param Teasers $teaser
     */
    private function updateOutput(Teasers $teaser)
    {
        if ((!$this->getTeaser()->is_external && $this->isDirty("is_external"))
            || $this->isDirty('picture')
            || $this->isDirty('title')
            || $this->isDirty('description')
        ) {
            $this->redis()->multi();
            RedisTeaser::instance()->setOutputStr($teaser);
            $this->redis()->exec();
        }
    }

    /**
     * Обновляет список платформ, на которых показывается тизер
     *
     * @param Teasers $teaser
     */
    private function updatePlatforms(Teasers $teaser)
    {
        $cities     = Cities::model()->getAllByCampaignId($teaser->news->campaign_id);
        $countries  = Countries::model()->getAllCodesCampaignId($teaser->news->campaign_id);

        if (!empty($cities) || !empty($countries)) {
            $this->remFromPlatforms($teaser, $cities, $countries);
            $this->addToPlatforms($teaser, $cities, $countries);
        }
    }

    /**
     * Удаляет связи тизера и площадок
     *
     * @param Teasers $teaser
     * @param array $cities
     * @param array $countries
     */
    public function remFromPlatforms(Teasers $teaser, array $cities, array $countries)
    {
        if (!empty($this->args['excepted_platforms'])) {
            $this->redis()->multi(Redis::PIPELINE);
                foreach($this->args['excepted_platforms'] as $platformId){
                    RedisPlatform::instance()->remTeaserFromCampaignSets($teaser, $platformId, $cities);
                }
                RedisPlatform::instance()->remTeaserFromNewsSets($teaser, $this->args['excepted_platforms']);
            $this->redis()->exec();

            $delJob = new TeaserDelFromRedisJob();
            $delJob->removeCampaignsIfNoTeasers($teaser->news->campaign_id, $this->args['excepted_platforms'], $cities, $countries);
            $delJob->removeNewsIfNoTeasers($teaser->news_id, $this->args['excepted_platforms'], $cities, $countries);
        }
    }

    /**
     * Добавляет связи тизера и площадок
     *
     * @param Teasers $teaser
     * @param array $cities
     * @param array $countries
     */
    public function addToPlatforms(Teasers $teaser, array $cities, array $countries)
    {
        if (!empty($this->args['added_platforms'])) {
            $addJob = new TeaserAddToRedisJob();
            $addJob->addToPlatforms($teaser, $this->args['added_platforms'], $cities, $countries);
        }
    }

    /**
     * Возвращает объект тизера по переданному идентификатору
     *
     * @return Teasers
     */
    private function getTeaser()
    {
        if (!isset($this->teaser)) {

            if (!isset($this->args['teaser_id'])) {
                return null;
            }

            $this->teaser = Teasers::model()->with('news')->findByPk($this->args['teaser_id']);
        }

        return $this->teaser;
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
        return $this->getCleanAttribute($attribute) != $this->getTeaser()->$attribute;
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