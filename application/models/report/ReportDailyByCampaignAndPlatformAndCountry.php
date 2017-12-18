<?php

/**
 * Модель отчета по показам и кликам по компании, платформе и стране за день
 *
 * The followings are the available columns in table 'report_daily_by_campaign_and_platform':
 * @property string $campaign_id
 * @property string $city_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 * @property integer $actions
 * @property integer $declined_actions
 */
class ReportDailyByCampaignAndPlatformAndCountry extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByCampaignAndPlatformAndCountry the static model class
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
        return array('campaign_id', 'platform_id', 'country_code', 'date');
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
		return 'report_daily_by_campaign_and_platform_and_country';
	}

    /**
     * Отчет по кампании сгруппированный по стране
     *
     * @param $campaign_id
     * @param $use_date
     * @param $date_from
     * @param $date_to
     * @return array
     */
    public function getForCampaign($campaign_id, $use_date, $date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'shows'              => '0',
                'clicks'             => '0',
                'actions'            => '0',
                'declined_actions'   => '0',
                'ctr'                => '0.00',
                'sum_price'          => '0.00',
                'avg_price'          => '0.00',
            ),
        );

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'c.name AS country_name',
            'SUM(r.clicks) AS clicks',
            'SUM(r.shows) AS shows',
            'SUM(r.actions) AS actions',
            'SUM(r.declined_actions) AS declined_actions',
            'SUM(r.clicks * IFNULL(cpc.cost, 0)) as sum_price',
        ));
        $command->from($this->tableName() . ' r');
        $command->leftJoin(PlatformsCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . PlatformsCpc::getMaxCpcSql() . ')');
        $command->leftJoin(Countries::model()->tableName() . ' c', 'r.country_code = c.code');
        $command->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
        if($use_date){
            $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
                ':date_from'    => $date_from,
                ':date_to'      => $date_to,
            ));
        }
        $command->group('r.country_code');
        $command->order('c.name');
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
                'country_name'     => $dbRow['country_name'] == '' ? 'Не определена' : $dbRow['country_name'],
                'ctr'           => sprintf('%.2f', round($dbRow['shows'] ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0, 2)),
                'sum_price'     => sprintf('%.2f', round($dbRow['sum_price'], 2)),
                'avg_price'     => sprintf('%.2f', round($dbRow['clicks'] ? $dbRow['sum_price'] / $dbRow['clicks'] : 0, 2)),
                'avg_clicks_per_action'  => sprintf('%.2f', round($dbRow['actions'] ? $dbRow['clicks'] / $dbRow['actions'] : 0, 2)),
                'avg_action_clicks_cost' => sprintf('%.2f', round($dbRow['actions'] ? $dbRow['sum_price'] / $dbRow['actions'] : 0, 2)),
            ));

        }

        $report['total']['sum_price'] = sprintf('%.2f', $report['total']['sum_price']);
        $report['total']['ctr'] = sprintf('%.2f', round($report['total']['shows'] ? ($report['total']['clicks'] * 100 / $report['total']['shows']) : 0,2));
        $report['total']['avg_price'] = sprintf('%.2f', round($report['total']['clicks'] ? $report['total']['sum_price'] / $report['total']['clicks'] : 0, 2));
        $report['total']['avg_clicks_per_action'] = sprintf('%.2f', round($report['total']['actions'] ? $report['total']['clicks'] / $report['total']['actions'] : 0, 2));
        $report['total']['avg_action_clicks_cost'] = sprintf('%.2f', round($report['total']['actions'] ? $report['total']['sum_price'] / $report['total']['actions'] : 0, 2));

        return $report;
    }

    public function getForCorrection($campaign_id, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'date',
            'country_code',
            'platform_id',
            'clicks',
            'shows',
            'actions'
        ));
        $command->from($this->tableName());
        $command->andWhere('campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->andWhere('clicks > 0 OR shows > 0 OR actions > 0');
        $result = array(
            'rows' => array(),
            'total' => array(
                'clicks' => 0,
                'shows' => 0,
                'actions' => 0,
            )
        );

        foreach($command->query() as $dbRow){
            if(!isset($result[$dbRow['date']][$dbRow['platform_id']])){
                $result[$dbRow['date']][$dbRow['platform_id']] = array(
                    'country_code' => array(),
                    'clicks' => 0,
                    'shows' => 0,
                    'actions' => 0,
                );
            }
            if(!isset($result[$dbRow['date']][$dbRow['platform_id']]['country_code'][$dbRow['country_code']])){
                $result[$dbRow['date']][$dbRow['platform_id']]['country_code'][$dbRow['country_code']] = array(
                    'clicks' => 0,
                    'shows' => 0,
                    'actions' => 0,
                );
            }
            foreach(array('clicks', 'shows', 'actions') as $attr){
                $result[$dbRow['date']][$dbRow['platform_id']][$attr] += $dbRow[$attr];
                $result[$dbRow['date']][$dbRow['platform_id']]['country_code'][$dbRow['country_code']][$attr] += $dbRow[$attr];
                $result['total'][$attr] += $dbRow[$attr];
            }
        }
        return $result;
    }
}