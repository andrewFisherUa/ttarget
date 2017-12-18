<?php

/**
 * Добавляет данные цели в редис
 */
class ActionAddToRedisJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array(
     *              'id' => CampaignsActions::$id,
     *            )
     */
    public $args = array();

    /**
     * @var CampaignsActions
     */
    private $action;

    public function perform()
    {
        if ($this->canPerform()) {
            $this->addAction();
        }
    }

    public function addAction(){
        $this->redis()->multi(Redis::PIPELINE);
            RedisAction::instance()->addAction($this->getAction());
        $this->redis()->exec();
    }

    /**
     * @return bool Проверяет можно ли выполнить фоновое задание
     */
    private function canPerform()
    {
        return ($action = $this->getAction()) != null && $action->checkIsActive();
    }

    /**
     * Возвращает объект цели по переданному идентификатору
     *
     * @return CampaignsActions
     */
    private function getAction()
    {
        if (!isset($this->action)) {
            if (!isset($this->args['action_id'])) {
                return null;
            }
            $this->action = CampaignsActions::model()->findByPk($this->args['action_id']);
        }
        return $this->action;
    }

    /**
     * @return Redis
     */
    private function redis()
    {
        return Yii::app()->redis;
    }
}