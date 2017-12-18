<?php

/**
 * Удаляет пользователя из бд
 */
class UserDelFromDbJob
{
    /**
     * @var array Параметры фоновой задачи
     *            array('user_id' => Users::$id)
     *
     */
    public $args = array();

    public function perform()
    {
        $user = $this->getUser();
        if (!$user || !$user->is_deleted) {
            return;
        }

        $transaction = $user->getDbConnection()->beginTransaction();
        try {
            $user->deleteCampaigns();
            $user->delete();

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * Возвращает объект пользователя по переданному идентификатору
     *
     * @return Users
     */
    private function getUser()
    {
        if (!isset($this->args['user_id'])) {
            return null;
        }

        return Users::model()->findByPk($this->args['user_id']);
    }
}