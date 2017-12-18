<?php

/**
 * Лог действий
 *
 * @property string $date
 * @property integer $action_id
 * @property integer $campaign_id
 * @property integer $target_id
 * @property integer $source_id
 * @property integer $source_type
 * @property integer $city_id
 * @property string $country_code
 * @property integer $ip
 * @property integer $status
 * @property integer $track_id
 *
 * @method ActionsLog findByPk()
 */
class ActionsLog extends Report
{
    const SOURCE_TYPE_TEASER = 'teaser';
    const SOURCE_TYPE_OFFER = 'offer';
    const STATUS_MODERATION = 0;
    const STATUS_ACCEPTED = 1;
    const STATUS_DECLINED = 2;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ActionsLog the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    private function dataFields()
    {
        return array(
            'date',
            'campaign_id',
            'source_type',
            'source_id',
            'target_id',
            'city_id',
            'country_code',
            'action_id',
            'ip',
            'track_id',
            'status'
        );
    }

    /**
     * @return string Возвращает название таблицы
     */
    public function tableName()
    {
        return self::getTableName();
    }

    /**
     * @return string Возвращает название таблицы отчета
     */
    protected static function getTableName()
	{
		return 'actions_log';
	}

    /**
     * Меняет статус дествия на "отклонено" и обратно
     *
     * @param $status
     * @return bool
     * @throws CDbException
     */
    public function changeStatus($status)
    {
        if($this->status != $status) {
            $transaction = $this->getDbConnection()->beginTransaction();
            try {
                $oldCounter = $this->_getCounter();
                $this->status = $status;
                if ($this->save()) {
                    $newCounter = $this->_getCounter();
                    $params = array(
                        'date' => date('Y-m-d', strtotime($this->date)),
                        'city_id' => $this->city_id,
                        'country_code' => $this->country_code,
                        'action_id' => $this->action_id,
                        'campaign_id' => $this->campaign_id,
                    );
                    if($this->source_type == self::SOURCE_TYPE_OFFER){
                        $params = array_merge($params, array(
                            'offer_id' => $this->target_id,
                            'offer_user_id' => $this->source_id,
                        ));
                    }else{
                        $params = array_merge($params, array(
                            'news_id' => $this->target_id,
                            'platform_id' => $this->source_id,
                        ));
                    }
                    ReportHandler::addCounter($oldCounter, $params, -1);
                    ReportHandler::addCounter($newCounter, $params, 1);
                    $sql = array();
                    /** @todo refactor this to use 'total' reports */
                    $row = Campaigns::createUpdateSql($this->campaign_id, $oldCounter, -1);
                    if(!empty($row)) $sql[] = $row;
                    $row = Campaigns::createUpdateSql($this->campaign_id, $newCounter, 1);
                    if(!empty($row)) $sql[] = $row;

                    $sql = array_merge($sql, ReportHandler::createUpdateCounterSql($oldCounter, true));
                    $sql = array_merge($sql, ReportHandler::createUpdateCounterSql($newCounter, true));

                    foreach($sql as $row){
                        $this->getDbConnection()->createCommand($row)->execute();
                    }
                    $transaction->commit();
                    $this->handleLimit();
                }else{
                    $transaction->rollback();
                    Yii::log("Cant change action status: ".implode("\n", $this->getErrors()), CLogger::LEVEL_ERROR);
                    return false;
                }
            }catch (Exception $e){
                $transaction->rollback();
                Yii::log("Cant change action status: ".$e->__toString(), CLogger::LEVEL_ERROR);
                return false;
            }
        }
        return true;
    }

    private function _getCounter(){
        if($this->status == self::STATUS_ACCEPTED){
            $counter = 'actions';
        }elseif($this->status == self::STATUS_DECLINED){
            $counter = 'declined_actions';
        }elseif($this->status == self::STATUS_MODERATION){
            $counter = 'moderation_actions';
        }else{
            throw new CException('Unknown ActionLog status: '.$this->status);
        }
        if($this->source_type == self::SOURCE_TYPE_OFFER){
            $counter = 'offers_' . $counter;
        }
        return $counter;
    }

    /**
     * Возвращает Sql добавления информации о действии
     *
     * {@inheritdoc}
     */
    public function createInsertSql($params)
    {
        if(isset($params['offer_id']) && isset($params['offer_user_id'])){
            $params['source_type'] = self::SOURCE_TYPE_OFFER;
            $params['source_id'] = $params['offer_user_id'];
            $params['target_id'] = $params['offer_id'];
        }elseif(isset($params['platform_id']) && isset($params['news_id'])){
            $params['source_type'] = self::SOURCE_TYPE_TEASER;
            $params['source_id'] = $params['platform_id'];
            $params['target_id'] = $params['news_id'];
        }else{
            throw new CException('Source type not known');
        }
        
        $params = array_intersect_key($params, array_flip($this->dataFields()));
        $sql = "INSERT INTO `" . $this->getTableName() . "` (" . implode(', ', array_keys($params)) .") ";
        $sql .= "VALUES ('" . implode("', '", array_values($params)) . "');";
        return $sql;
    }

    /**
     * Отчет по действиям для кампании
     *
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function getForCampaign($campaign_id, $use_date, $date_from, $date_to, $source_type = null, $platform_id = null, $user_id = null, $status = null)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'payment' => '0.00',
                'reward'  => '0.00',
                'debit'   => '0.00'
            ),
        );

        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'r.id',
                'r.date',
                'r.ip',
                'r.status',
//                'r.referrer_url',
                'r.source_type',
                'COALESCE(p.server, CONCAT(u.login, " (",u.email,")")) AS source_name',
                'COALESCE(n.name, o.name) AS target_name',
                'COALESCE(n.url, o.url) AS target_url',
                'co.name as country_name',
                'cy.name as city_name',
                'COALESCE(o.payment, ca.cost) AS payment',
                'o.reward',
                'ca.name AS action_name'
            ))
            ->from($this->tableName() . ' r')
        //source
            ->leftJoin(
                Platforms::model()->tableName() . ' p',
                "r.source_type = '" . self::SOURCE_TYPE_TEASER . "' AND r.source_id = p.id"
            )
            ->leftJoin(
                OffersUsers::model()->tableName() . ' ou',
                "r.source_type = '" . self::SOURCE_TYPE_OFFER . "' AND r.source_id = ou.id"
            )
            ->leftJoin(
                Users::model()->tableName() . ' u',
                "r.source_type = '" . self::SOURCE_TYPE_OFFER . "' AND ou.user_id = u.id"
            )
        //target
            ->leftJoin(
                News::model()->tableName() . ' n',
                "r.source_type = '" . self::SOURCE_TYPE_TEASER . "' AND r.target_id = n.id"
            )
            ->leftJoin(
                Offers::model()->tableName() . ' o',
                "r.source_type = '" . self::SOURCE_TYPE_OFFER . "' AND r.target_id = o.id"
            )
        //
            ->leftJoin(CampaignsActions::model()->tableName() . ' ca', "r.action_id = ca.id")
            ->leftJoin(Countries::model()->tableName() . ' co', 'r.country_code = co.code')
            ->leftJoin(Cities::model()->tableName() . ' cy', 'r.city_id = cy.id')
            ->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id))
            ->order('r.date');
        if(null !== $status && array_key_exists($status, self::getAvailableStatuses())){
            $command->andWhere('r.status = :status', array(':status' => $status));
        }
        if($use_date){
            $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
                ':date_from'    => $date_from.' 00:00:00',
                ':date_to'      => $date_to.' 23:59:59',
            ));
        }
        if($source_type == self::SOURCE_TYPE_TEASER){
            $command->andWhere("r.source_type = '".self::SOURCE_TYPE_TEASER."'");
            if(!empty($platform_id)){
                $command->andWhere('r.source_id = :platform_id', array(':platform_id' => (int) $platform_id));
            }
        }elseif($source_type == self::SOURCE_TYPE_OFFER){
            $command->andWhere("r.source_type = '".self::SOURCE_TYPE_OFFER."'");
            if(!empty($user_id)){
                $command->andWhere('u.id = :offer_user_id', array(':offer_user_id' => (int) $user_id));
            }
        }
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        foreach ($dataReader as $dbRow) {
            $dbRow['debit'] = sprintf('%.2f', $dbRow['reward'] ? $dbRow['payment'] - $dbRow['reward'] : 0);
            $dbRow['ip'] = long2ip($dbRow['ip']);
            $dbRow['geo'] = GEO::getStringByName($dbRow['country_name'], $dbRow['city_name']);
            $dbRow['source_type_name'] = Arr::ad($this->getAvailableSourceTypes(), $dbRow['source_type']);
            $dbRow['target_url_decoded'] = IDN::decodeUrl($dbRow['target_url']);
            $report['rows'][] = $dbRow;

            if($dbRow['status'] == self::STATUS_ACCEPTED) {
                $report['total']['payment'] += $dbRow['payment'];
                $report['total']['reward'] += $dbRow['reward'];
                $report['total']['debit'] += $dbRow['debit'];
            }
        }

        $report['total']['payment'] = sprintf('%.2f', $report['total']['payment']);
        $report['total']['reward'] = sprintf('%.2f', $report['total']['reward']);
        $report['total']['debit'] = sprintf('%.2f', $report['total']['debit']);

        return $report;
    }

    public function getForWebmaster($user_id, $use_date, $date_from, $date_to, $status = null)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'reward'  => '0.00',
            ),
        );

        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'r.id',
                'r.campaign_id',
                'c.name AS campaign_name',
                'r.date',
                'r.ip',
                'r.status',
//                'r.referrer_url',
                'o.name AS target_name',
                'o.url AS target_url',
                'co.name as country_name',
                'cy.name as city_name',
                'o.reward'
            ))
            ->from($this->tableName() . ' r')
            ->leftJoin(
                OffersUsers::model()->tableName() . ' ou',
                "r.source_type = '" . self::SOURCE_TYPE_OFFER . "' AND r.source_id = ou.id"
            )
            ->leftJoin(
                Offers::model()->tableName() . ' o',
                "r.source_type = '" . self::SOURCE_TYPE_OFFER . "' AND r.target_id = o.id"
            )
            ->leftJoin(Campaigns::model()->tableName() . ' c', "r.campaign_id = c.id")
            ->leftJoin(Countries::model()->tableName() . ' co', 'r.country_code = co.code')
            ->leftJoin(Cities::model()->tableName() . ' cy', 'r.city_id = cy.id')
            ->andWhere('r.source_type = :source_type', array(':source_type' => self::SOURCE_TYPE_OFFER))
            ->andWhere('ou.user_id = :user_id', array(':user_id' => $user_id))
            ->order('r.date');
        if(null !== $status && array_key_exists($status, self::getAvailableStatuses())){
            $command->andWhere('r.status = :status', array(':status' => $status));
        }
        if($use_date){
            $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
                ':date_from'    => $date_from.' 00:00:00',
                ':date_to'      => $date_to.' 23:59:59',
            ));
        }

        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        foreach ($dataReader as $dbRow) {
            $dbRow['ip'] = long2ip($dbRow['ip']);
            $dbRow['geo'] = GEO::getStringByName($dbRow['country_name'], $dbRow['city_name']);
            $dbRow['target_url_decoded'] = IDN::decodeUrl($dbRow['target_url']);
            $report['rows'][] = $dbRow;

            if($dbRow['status'] == self::STATUS_ACCEPTED) {
                $report['total']['reward'] += $dbRow['reward'];
            }
        }

        $report['total']['reward'] = sprintf('%.2f', $report['total']['reward']);

        return $report;
    }

    public function handleLimit()
    {
        if($this->source_type == self::SOURCE_TYPE_OFFER){
            $source = OffersUsers::model()->findByPk($this->source_id);
        }else{
            $source = Campaigns::model()->findByPk($this->campaign_id);
        }
        return $source->handleLimit();
    }

    public function getAvailableSourceTypes()
    {
        return array(
            self::SOURCE_TYPE_TEASER => 'Площадка',
            self::SOURCE_TYPE_OFFER => 'Вебмастер',
        );
    }

    public static function getAvailableStatuses()
    {
        return array(
            self::STATUS_ACCEPTED => "Подтвержденные",
            self::STATUS_MODERATION => "В ожидании",
            self::STATUS_DECLINED => "Отклоненные",
        );
    }
}