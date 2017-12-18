<?php

/**
 * Модель отчета по показам и кликам по компании и платформе за день
 *
 * The followings are the available columns in table 'report_daily_by_campaign_and_platform':
 * @property string $campaign_id
 * @property string $platform_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 * @property integer $actions
 * @property integer $declined_actions
 */
class ReportDailyByCampaignAndPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByCampaignAndPlatform the static model class
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
        return array('campaign_id', 'platform_id', 'date');
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
		return 'report_daily_by_campaign_and_platform';
	}

    /**
     * Возвращает суммарный отчет по кампании и платформе на заданный период
     *
     * @param integer $campaign_id
     * @param integer $platform_id
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array  array('news_id' => dbRow)
     */
    public function getTotalByPeriod($campaign_id, $platform_id, $date_from, $date_to)
    {
        if (empty($campaign_id)) return array();

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'campaign_id',
            'SUM(shows) AS shows',
            'SUM(clicks) AS clicks',
        ));
        $command->from($this->tableName());
        $command->andWhere('campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
        $command->andWhere('platform_id = :platform_id', array(':platform_id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('campaign_id');

        $dbRow = $command->queryRow();
        return is_array($dbRow) ? $dbRow : array();
    }


    /**
     * Возвращает отчет на заданный период по
     * всем рекламным площадкам
     *
     * @param string $date_from Y-m-d
     * @param string $date_to   Y-m-d
     *
     * @return array
     */
    public function getFullByPlatforms($date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'shows'         => 0,
                'clicks'        => 0,
                'ctr'           => 0,
                'clickfraud'    => 0,
                'price'         => 0
            ),
        );

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'r.platform_id',
            'r.campaign_id',
            'SUM(r.clicks) as clicks',
            'SUM(r.shows) as shows',
            'SUM(r.clicks * IFNULL(cpc.cost, 0)) as price',
            'p.server as platform_name',
            'p.currency',
            'c.name as campaign_name'
        ));
        $command->from($this->tableName() . ' r');
        $command->join(Campaigns::model()->tableName() . ' c', 'r.campaign_id = c.id');
        $command->join(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');
        $command->leftJoin(PlatformsCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . PlatformsCpc::getMaxCpcSql() . ')');
        $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('r.platform_id, r.campaign_id');
        $command->order('platform_name, campaign_name');
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        $clickfrauds = ReportDailyClickfraud::model()->countForCampaignsAndPlatformsByPeriod($date_from, $date_to);

        $totalClickfraud = 0;
        $totalPrice      = 0;
        $totalClicks     = 0;
        $totalShows      = 0;

        foreach ($dataReader as $dbRow) {

            $ctr        = ($dbRow['shows']) ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0;

            $totalPrice  += $dbRow['price'];
            $totalClicks += $dbRow['clicks'];
            $totalShows  += $dbRow['shows'];

            $clickfraud = 0;
            if (isset($clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']])) {
                $clickfraud = ($dbRow['clicks']) ? ($clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']] * 100 / $dbRow['clicks']) : 0;
                $totalClickfraud += $clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']];
            }

            $report['rows'][] = array(
                'platform_name' => $dbRow['platform_name'],
                'campaign_name' => $dbRow['campaign_name'],
                'shows'         => $dbRow['shows'],
                'clicks'        => $dbRow['clicks'],
                'ctr'           => sprintf('%.2f', round($ctr, 2)),
                'clickfraud'    => sprintf('%.2f', round($clickfraud, 2)),
                'price'         => sprintf('%.2f', round($dbRow['price'], 2)),
                'currency'      => $dbRow['currency']
            );
        }

        $totalCtr           = ($totalShows) ? ($totalClicks * 100 / $totalShows) : 0;
        $totalClickfraud    = ($totalClicks) ? ($totalClickfraud * 100 / $totalClicks) : 0;

        $report['total'] = array(
            'shows'         => $totalShows,
            'clicks'        => $totalClicks,
            'ctr'           => sprintf('%.2f', round($totalCtr, 2)),
            'price'         => sprintf('%.2f', round($totalPrice, 2)),
            'clickfraud'    => sprintf('%.2f', round($totalClickfraud, 2)),
        );

        return $report;
    }

    /**
     * Возвращает отчет на заданный период по
     * всем внешним рекламным площадкам
     *
     * @param string $date_from Y-m-d
     * @param string $date_to   Y-m-d
     * @param boolean $externals true
     *
     * @return array
     */
    public function getForPlatformsByPeriod($date_from, $date_to, $externals = true)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'clicks'        => 0,
                'clickfraud'    => 0,
            ),
        );

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'r.platform_id',
            'r.campaign_id',
            'SUM(r.clicks) as clicks',
            'SUM(r.shows) as shows',
            'p.server as platform_name',
            'c.name as campaign_name'
        ));
        $command->from($this->tableName() . ' r');
        $command->join(Campaigns::model()->tableName() . ' c', 'r.campaign_id = c.id');
        $command->join(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');
        $command->where('p.is_external = :externals', array(':externals' => $externals));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('r.platform_id, r.campaign_id');
        $command->order('platform_name, campaign_name');
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        $clickfrauds = ReportDailyClickfraud::model()->countForCampaignsAndPlatformsByPeriod($date_from, $date_to);

        $totalClickfraud = 0;
        $totalClicks     = 0;

        foreach ($dataReader as $dbRow) {

            $totalClicks += $dbRow['clicks'];

            $clickfraud = 0;
            if (isset($clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']])) {
                $clickfraud = ($dbRow['clicks']) ? ($clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']] * 100 / $dbRow['clicks']) : 0;
                $totalClickfraud += $clickfrauds[$dbRow['platform_id']][$dbRow['campaign_id']];
            }

            $report['rows'][] = array(
                'platform_name' => $dbRow['platform_name'],
                'campaign_name' => $dbRow['campaign_name'],
                'clicks'        => $dbRow['clicks'],
                'clickfraud'    => sprintf('%.2f', round($clickfraud, 2)),
            );
        }

        $totalClickfraud    = ($totalClicks) ? ($totalClickfraud * 100 / $totalClicks) : 0;

        $report['total'] = array(
            'clicks'        => $totalClicks,
            'clickfraud'    => sprintf('%.2f', round($totalClickfraud, 2)),
        );

        return $report;
    }

    /**
     * Отчет по кампании сгруппированный по дате
     *
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function getForCampaignDate($campaign_id, $use_date, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->group('r.date');
        $command->order('r.date DESC');
        return $this->getForCampaign(
            $command,
            $campaign_id,
            $use_date,
            $date_from,
            $date_to
        );
    }

    /**
     * Отчет по кампании сгруппированный по платформе
     *
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @param string $filter
     * @param bool $is_external
     * @return array
     */
    public function getForCampaignPlatform($campaign_id, $use_date, $date_from, $date_to, $filter = null, $is_external = null)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->leftJoin(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');
        if($is_external !== null){
            $command->andWhere('p.is_external = :is_external', array(':is_external' => $is_external ? '1' : '0'));
        }
        if($filter !== null){
            $command->andWhere("(p.server LIKE :filter OR r.platform_id LIKE :filter)", array(':filter' => '%'.$filter.'%'));
        }
        $command->group('r.platform_id');
        $command->order('platform_server');
        return $this->getForCampaign(
            $command,
            $campaign_id,
            $use_date,
            $date_from,
            $date_to,
            array('p.server AS platform_server', 'r.platform_id')
        );
    }

    /**
     * Базовый метод для отчета по кампании
     *
     * @param CDbCommand $command
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @param array $additionalFields
     * @return array
     */
    public function getForCampaign(CDbCommand $command, $campaign_id, $use_date, $date_from, $date_to, $additionalFields = array())
    {
        $joins = (array) $command->join;
        $command->join = array();

        $report = array(
            'rows'  => array(),
            'total' => array(
                'shows'              => '0',
                'clicks'             => '0',
                'actions'            => '0',
                'declined_actions'   => '0',
                'ctr'                => '0.00',
                'sum_price'          => '0.00',
                'avg_price'          => '0.00'
            ),
        );

        $command->select(array_merge($additionalFields, array(
            'r.campaign_id',
            'r.date',
            'SUM(r.clicks) as clicks',
            'SUM(r.shows) as shows',
            'SUM(r.actions) as actions',
            'SUM(r.declined_actions) as declined_actions',
            'SUM(r.clicks * IFNULL(cpc.cost, 0)) as sum_price',
        )));
        $command->from($this->tableName() . ' r');
        $command->leftJoin(PlatformsCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . PlatformsCpc::getMaxCpcSql() . ')');
        $command->join = array_merge($command->join, $joins);
        $command->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
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
            $report['total']['sum_price'] += $dbRow['sum_price'];
            $report['total']['clicks'] += $dbRow['clicks'];
            $report['total']['shows'] += $dbRow['shows'];
            $report['total']['actions'] += $dbRow['actions'];
            $report['total']['declined_actions'] += $dbRow['declined_actions'];

            $report['rows'][] = array_merge($dbRow, array(
                'ctr'                    => sprintf('%.2f', round($dbRow['shows'] ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0, 2)),
                'sum_price'              => sprintf('%.2f', round($dbRow['sum_price'], 2)),
                'avg_price'              => sprintf('%.2f', round($dbRow['clicks'] ? $dbRow['sum_price'] / $dbRow['clicks'] : 0, 2)),
                'avg_clicks_per_action'  => sprintf('%.2f', round($dbRow['actions'] ? $dbRow['clicks'] / $dbRow['actions'] : 0, 2)),
                'avg_action_clicks_cost' => sprintf('%.2f', round($dbRow['actions'] ? $dbRow['sum_price'] / $dbRow['actions'] : 0, 2)),
            ));

        }

        $report['total'] = array_merge($report['total'], array(
            'sum_price' => sprintf('%.2f', $report['total']['sum_price']),
            'ctr' => sprintf('%.2f', round($report['total']['shows'] ? ($report['total']['clicks'] * 100 / $report['total']['shows']) : 0,2)),
            'avg_price' => sprintf('%.2f', round($report['total']['clicks'] ? $report['total']['sum_price'] / $report['total']['clicks'] : 0, 2)),
            'avg_clicks_per_action' => sprintf('%.2f', round($report['total']['actions'] ? $report['total']['clicks'] / $report['total']['actions'] : 0, 2)),
            'avg_action_clicks_cost' => sprintf('%.2f', round($report['total']['actions'] ? $report['total']['sum_price'] / $report['total']['actions'] : 0, 2)),
        ));
        return $report;
    }


}