<?php

/**
 * Модель отчета по количеству действий для действия за день
 *
 * The followings are the available columns in table 'report_daily_by_platform':
 * @property string $action_id
 * @property string $date
 * @property integer $actions
 * @property integer $declined_actions
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 */
class ReportDailyByCampaignAndPlatformAndAction extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByCampaignAndPlatformAndAction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array Первичный ключ таблицы
     */
    public function primaryKey()
    {
        return array('date', 'campaign_id', 'platform_id', 'action_id');
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
		return 'report_daily_by_campaign_and_platform_and_action';
	}

    public function getForCampaignByDate($campaign_id, $use_date, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'r.date',
                'sum(r.actions) AS actions',
                'sum(r.declined_actions) AS declined_actions',
            ))
            ->order('r.date DESC, ca.name ASC')
            ->group('r.date, r.action_id');
        return $this->getForCampaign($command, $campaign_id, $use_date, $date_from, $date_to);
    }

    public function getForCampaignByAction($campaign_id, $use_date, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'sum(r.actions) AS actions'
            ))
            ->order('ca.name ASC')
            ->group('r.action_id');
        return $this->getForCampaign($command, $campaign_id, $use_date, $date_from, $date_to);
    }

    /**
     * Отчет по кампании сгруппированный по дате
     *
     * @param CDbCommand $command
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function getForCampaign(CDbCommand $command, $campaign_id, $use_date, $date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'actions'           => '0',
                'declined_actions'  => '0',
                'sum_cost'          => '0.00',
            ),
        );

        $select = $command->select;
        $command->select(
            (empty($select) ? '' : str_replace('`', '', $select).', ')
            .'r.action_id, ca.name, ca.cost'
        )
            ->from($this->tableName() . ' r')
            ->join(CampaignsActions::model()->tableName() . ' ca', 'ca.id = r.action_id')
            ->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));

        if($use_date){
            $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
                ':date_from'    => $date_from,
                ':date_to'      => $date_to,
            ));
        }
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        foreach ($dataReader as $dbRow) {
            $report['total']['sum_cost'] += $dbRow['cost'] * $dbRow['actions'];
            $report['total']['actions'] += $dbRow['actions'];
            $report['total']['declined_actions'] += $dbRow['declined_actions'];

            $report['rows'][] = array_merge($dbRow, array(
                'sum_cost'              => sprintf('%.2f', round($dbRow['cost'] * $dbRow['actions'], 2)),
            ));
        }

        $report['total'] = array_merge($report['total'], array(
            'sum_cost' => sprintf('%.2f', $report['total']['sum_cost']),
        ));

        return $report;
    }

    public function getForCorrection($campaign_id, $date_from, $date_to, $platform_id, $group = false)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'r.date',
                'r.platform_id',
                'p.server as platform_server',
                'r.action_id',
                'a.name as action_name',
                'r.actions'
            ))
            ->from($this->tableName() . ' r')
            ->leftJoin(Platforms::model()->tableName() . ' p', 'p.id = r.platform_id')
            ->leftJoin(CampaignsActions::model()->tableName() . ' a', 'a.id = r.action_id')
            ->where('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id))
            ->andWhere('date BETWEEN :date_from AND :date_to', array(':date_from' => $date_from, ':date_to' => $date_to))
            ->andWhere('r.actions > 0')
            ->order('r.date, r.action_id, r.platform_id');
        if(!empty($platform_id)){
            $command->andWhere(
                $this->getDbConnection()->commandBuilder
                    ->createInCondition($this->getTableSchema(), 'platform_id', (array) $platform_id, 'r.')
            );
        }
        $result = array(
            'rows' => array(),
            'total' => array(
                'actions' => 0,
            )
        );
        foreach($command->queryAll() as $dbRow){
            if($group){
                if(!isset($result['rows'][$dbRow['action_id']])){
                    $result['rows'][$dbRow['action_id']] = array(
                        'action_name' => $dbRow['action_name']
                    );
                }

                if(!isset($result['rows'][$dbRow['action_id']]['platforms'][$dbRow['platform_id']])){
                    $result['rows'][$dbRow['action_id']]['platforms'][$dbRow['platform_id']] = array(
                        'platform_server' => $dbRow['platform_server'],
                        'actions' => 0,
                    );
                }
                $result['rows'][$dbRow['action_id']]['platforms'][$dbRow['platform_id']]['actions'] += $dbRow['actions'];
            }else {
                $result['rows'][] = $dbRow;
            }
            $result['total']['actions'] += $dbRow['actions'];
        }

        return $result;
    }
}