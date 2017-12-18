<?php

/**
 * Фиксирует цели
 */
class ActionsJob
{
    public $args = array();

    public function perform()
    {
        if (!$this->validateArgs()) {
            return;
        }

        $action = CampaignsActions::getById($this->args['encrypted_id']);
        $teaser = Teasers::getById($this->args['teaser_id']);
        if ($action && $teaser) {
            $params = array(
                'action_id'    => $action['id'],
                'campaign_id'  => $action['campaign_id'],
//                'teaser_id'    => $teaser['id'],
                'news_id'      => $teaser['news_id'],
                'platform_id'  => $this->args['platform_id'],
                'city_id'      => $this->args['city_id'],
                'country_code' => $this->args['country_code'],
                'date'         => date('Y-m-d', $this->args['timestamp']),
                'ip'           => sprintf('%u', ip2long($this->args['ip']))
            );
            ReportHandler::addCounter(
                'actions',
                $params,
                1
            );

            $sql = ReportHandler::createUpdateCounterSql('actions');
            $sql .= ActionsLog::model()->createInsertSql(
                array_merge($params, array(
                    'date' => date('Y-m-d H:i:s', $this->args['timestamp']),
                    'track_id' => $this->args['track_id'],
                    'status' => ActionsLog::STATUS_ACCEPTED
                ))
            );
            $sql .= Campaigns::createUpdateSql($action['campaign_id'], 'actions', 1);

//            $sql .= Tracks::revokeSql(array(
//                'id' => $this->args['track_id'],
//                'revoked_date' => date('Y-m-d H:i:s', $this->args['timestamp']),
//            ));

            // Отправляем все завпросы одной пачкой
            Yii::app()->mysqli->multiQuery($sql);
            Yii::app()->mysqli->client()->close();

            $c = Campaigns::model()->findByPk($action['campaign_id']);
            $c->updateWeight();
            $c->handleLimit();
        }
    }

    /**
     * Проверяет переданные параметры
     *
     * @return bool
     * @throws CException
     */
    private function validateArgs()
    {
        if (
            isset($this->args['track_id']) &&
            isset($this->args['action_id']) &&
            isset($this->args['encrypted_id']) &&
            isset($this->args['timestamp']) &&
            isset($this->args['campaign_id']) &&
            isset($this->args['platform_id']) &&
            isset($this->args['city_id']) &&
            isset($this->args['country_code']) &&
            isset($this->args['teaser_id']) &&
            isset($this->args['ip'])
        ){
            return true;
        }else{
            throw new CException('Not valid args: '.print_r($this->args, true));
        }
    }
}