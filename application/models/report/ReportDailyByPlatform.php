<?php

/**
 * Модель отчета по показам и кликам по площадке за день
 *
 * The followings are the available columns in table 'report_daily_by_platform':
 * @property string $platform_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 */
class ReportDailyByPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByPlatform the static model class
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
        return array('platform_id', 'date');
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
		return 'report_daily_by_platform';
	}

    /**
     * Возвращает сводную статистику за период по площадке
     *
     * @param $platform_id
     * @param $date_form
     * @param $date_to
     *
     * @return array
     */
    public function totalByPeriod($platform_id, $date_form, $date_to)
    {
        $sql = <<<EOF
            SELECT SUM(clicks) as clicks, SUM(shows) as shows, SUM(clicks * IFNULL(p.cost, 0)) as price
            FROM report_daily_by_platform r
            LEFT JOIN platforms_cpc p ON r.platform_id = p.platform_id AND p.date = (
                SELECT cpc.date
                FROM platforms_cpc cpc
                WHERE cpc.platform_id = r.platform_id AND cpc.date <= r.date
                ORDER BY cpc.date DESC
                LIMIT 1
            ) WHERE r.platform_id = :platform_id AND r.date BETWEEN :date_from AND :date_to
EOF;

        $command = $this->getDbConnection()->createCommand($sql);
        $dbRow = $command->queryRow(true, array(
            'platform_id'   => $platform_id,
            'date_from'     => $date_form,
            'date_to'       => $date_to,
        ));

        if (!$dbRow) {
            return array(
                'clicks'    => 0,
                'shows'     => 0,
                'price'     => 0,
            );
        }

        return $dbRow;
    }


    /**
     * Возвращает отчет за указанный период
     *
     * В отчет в ключена информация по скликиваниям, и стоимости показов
     *
     * @param integer $platform_id
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array
     */
    public function getByPeriod($platform_id, $date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'shows'         => 0,
                'clicks'        => 0,
                'ctr'           => 0,
                'clickfraud'    => 0,
                'cost'          => 0,
                'price'         => 0
            ),
        );

        $command = $this->getDbConnection()->createCommand();
        $command->from($this->tableName());
        $command->andWhere('platform_id = :id', array(':id' => $platform_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->order('date ASC');
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        $clickfrauds = ReportDailyClickfraud::model()->countByPeriodAndPlatform($platform_id, $date_from, $date_to);

        $totalClickfraud = 0;
        $totalPrice      = 0;
        $totalClicks     = 0;
        $totalShows      = 0;

        foreach ($dataReader as $dbRow) {

            $ctr        = ($dbRow['shows']) ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0;
            $cost       = PlatformsCpc::model()->getCostPerClick($platform_id, $dbRow['date']);
            $price      = $dbRow['clicks'] * $cost;

            $totalPrice  += $price;
            $totalClicks += $dbRow['clicks'];
            $totalShows  += $dbRow['shows'];

            $clickfraud = 0;
            if (isset($clickfrauds[$dbRow['date']])) {
                $clickfraud = $clickfrauds[$dbRow['date']];
                $totalClickfraud += $clickfrauds[$dbRow['date']];
            }

            $report['rows'][] = array(
                'date'          => $dbRow['date'],
                'shows'         => $dbRow['shows'],
                'clicks'        => $dbRow['clicks'],
                'ctr'           => sprintf('%.2f', round($ctr, 2)),
                'price'         => sprintf('%.2f', round($price, 2)),
                'cost'          => sprintf('%.2f', round($cost, 2)),
                'clickfraud'    => sprintf('%d', $clickfraud),

            );
        }

        $totalCtr           = ($totalShows) ? ($totalClicks * 100 / $totalShows) : 0;
        $totalCost          = ($totalClicks) ? $totalPrice / $totalClicks : 0;
        $totalClickfraud    = ($totalClicks) ? ($totalClickfraud) : 0;

        $report['total'] = array(
            'shows'         => $totalShows,
            'clicks'        => $totalClicks,
            'ctr'           => sprintf('%.2f', round($totalCtr, 2)),
            'price'         => sprintf('%.2f', round($totalPrice, 2)),
            'cost'          => sprintf('%.2f', round($totalCost, 2)),
            'clickfraud'    => sprintf('%d', $totalClickfraud),
        );

        return $report;
    }

    /**
     * Возвращает отчет за указанный период для всех платформ пользователя
     *
     * @param integer $user_id
     * @param string  $date_from
     * @param string  $date_to
     *
     * @return array
     */
    public function getTotalsByUserId($user_id, $date_from, $date_to){
        $command = $this->getDbConnection()->createCommand();
        $command->andWhere('p.user_id = :user_id' ,array(':user_id' => $user_id));
        return $this->getTotalsByDbCommand($command, $date_from, $date_to);
    }

    /**
     * Отчет за указаный период для всех платформ с дополнительными полями для биллинга
     *
     * @param $date_from
     * @param $date_to
     * @param $is_active
     *
     * @return array
     */
    public function getTotalsForBilling($date_from, $date_to, $is_active = null)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->leftJoin(Users::model()->tableName().' u', 'p.user_id = u.id');
        $command->andWhere('p.is_external = 0');
        $command->andWhere('r.clicks > 0');
        $command->andWhere('p.is_deleted = 0');
        if($is_active !== null){
            $command->andWhere('p.is_active = :is_active', array(':is_active' => $is_active));
        }
        $additionalFields = array(
            'is_vat',
            'is_active',
            'billing_details_type',
            'billing_details_text',
        );
        $reportData = $this->getTotalsByDbCommand($command, $date_from, $date_to, $additionalFields);
        $reportData['total'] =  array_merge($reportData['total'], array(
            'price_with_vat' => 0,
            'debit' => 0,
            'debit_with_vat' => 0,
            'debit_vat' => 0,
        ));

        $paid = array();
        foreach($reportData['rows'] as &$row){
            if(!isset($paid[$row['platform_id']])){
                $paid[$row['platform_id']] = BillingIncome::model()->getPaidByPlatform($row['platform_id'],1);
            }
        }

        if(empty($paid)) return $reportData;

        $profits = $this->getPriceByPlatforms(array_keys($paid));

        foreach($reportData['rows'] as &$row){
            $row['debit'] = (float) $profits[$row['platform_id']] - $paid[$row['platform_id']];
            $row['debit_with_vat'] = $row['is_vat'] == '1' ? sprintf('%.2f', $row['debit'] * (1 + Yii::app()->params->VAT / 100)) : $row['debit'];
            $row['debit_vat'] = $row['debit_with_vat'] - $row['debit'];
            $row['price_with_vat'] = $row['is_vat'] == '1' ? sprintf('%.2f', $row['price'] * (1 + Yii::app()->params->VAT / 100)) : $row['price'];
            $row['cost'] = sprintf('%.2f', $row['clicks'] > 0 ?$row['price'] / $row['clicks'] : 0);
            $reportData['total']['price_with_vat'] += $row['price_with_vat'];
            $reportData['total']['debit'] += $row['debit'];
            $reportData['total']['debit_with_vat'] += $row['debit_with_vat'];
            $reportData['total']['debit_vat'] += $row['debit_vat'];
        }
        return $reportData;
    }

    /**
     * Базовый общий отчет
     *
     * @param CDbCommand $command
     * @param $date_from
     * @param $date_to
     * @param array $additionalFields
     *
     * @return array
     */
    public function getTotalsByDbCommand($command, $date_from, $date_to, $additionalFields = array()){
        $joins = (array) $command->join;
        $command->join = array();

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

        $command->select(array_merge($additionalFields, array(
            'r.platform_id',
            'SUM(r.clicks) as clicks',
            'SUM(r.shows) as shows',
            'SUM(r.clicks * IFNULL(cpc.cost, 0)) as price',
            'p.server as platform_name',
            'p.currency',
        )));
        $command->from($this->tableName() .' r');
        $command->join(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');
        $command->leftJoin(PlatformsCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . PlatformsCpc::getMaxCpcSql() . ')');
        $command->join = array_merge($command->join, $joins);
        $command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->group('r.platform_id');
        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        $totalClickfraud = 0;
        $totalPrice      = 0;
        $totalClicks     = 0;
        $totalShows      = 0;
        $platforms = array();

        foreach ($dataReader as $dbRow) {
            $platforms[] = $dbRow['platform_id'];
            $totalPrice  += $dbRow['price'];
            $totalClicks += $dbRow['clicks'];
            $totalShows  += $dbRow['shows'];

            $report['rows'][$dbRow['platform_id']] = array(
                'platform_id' => $dbRow['platform_id'],
                'platform_name' => $dbRow['platform_name'],
                'shows' => $dbRow['shows'],
                'clicks' => $dbRow['clicks'],
                'price' => $dbRow['price'],
                'currency' => $dbRow['currency'],
                'ctr' => sprintf('%.2f', round($dbRow['shows'] ? $dbRow['clicks'] * 100 / $dbRow['shows'] : 0, 2)),
                'clickfraud' => '0.00',
            );

            foreach($additionalFields as $field){
                $report['rows'][$dbRow['platform_id']][$field] = $dbRow[$field];
            }
        }

        $clickfrauds = ReportDailyClickfraud::model()->countTotalByPlatforms($platforms, $date_from, $date_to);
        foreach($clickfrauds as $platform_id => $clickfraud){
            if($report['rows'][$platform_id]['clicks'] > 0){
                $report['rows'][$platform_id]['clickfraud'] = sprintf('%.2f', round($clickfraud * 100 / $report['rows'][$platform_id]['clicks'],2));
            }elseif($clickfraud > 0){
                $report['rows'][$platform_id]['clickfraud'] = '100.00';
            }else{
                $report['rows'][$platform_id]['clickfraud'] = '0.00';
            }
            $totalClickfraud += $clickfraud;
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

    public function getPriceSumByAttributes($attribute)
    {
        $reportData = $this->getPriceByAttributes($attribute);
        return (float) $reportData[0]['price'];
    }

    public function getPriceByPlatforms($platforms)
    {
        $cmd = $this->getDbConnection()->createCommand();
        $cmd->group('r.platform_id');
        $result = $this->getPriceByAttributes(array('platform_id' => $platforms), $cmd);
        $prices = array();
        foreach($result as $row){
            $prices[$row['platform_id']] = $row['price'];
        }
        return $prices;
    }

    public function getPriceByAttributes($attribute, $command = null)
    {
        if($command === null){
            $command = $this->getDbConnection()->createCommand();
        }
        $command->select('SUM(r.clicks * IFNULL(cpc.cost, 0)) as price, r.platform_id as platform_id');
        $command->from($this->tableName() .' r');
        $command->leftJoin(PlatformsCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . PlatformsCpc::getMaxCpcSql() . ')');
        if(isset($attribute['user_id'])){
            $command->join(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');
            $command->andWhere('user_id = :user_id' ,array(':user_id' => $attribute['user_id']));
        }
        if(isset($attribute['platform_id'])){
            if(is_array($attribute['platform_id'])){
                $params = array();
                foreach(array_values($attribute['platform_id']) as $k => $val){
                    $params[':platform'.$k] = $val;
                }
                $command->andWhere('r.platform_id in ('.implode(', ', array_keys($params)).')', $params);
            }else{
                $command->andWhere('r.platform_id = :platform_id' ,array(':platform_id' => $attribute['platform_id']));
            }
        }
        if(isset($attribute['date'])){
            $command->andWhere('r.date = :date' ,array(':date' => $attribute['date']));
        }
        return $command->queryAll();
    }

    public function getWithdrawalReport($date_from, $date_to, $is_active = null)
    {
        $result['total'] = array(
            'sum' => 0,
            'sum_with_vat' => 0,
            'debit' => 0,
            'paid' => 0,
            'not_paid' => 0,
        );

        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'b.number',
            'b.issuing_date',
            'b.paid_date',
            'b.sum',
            'b.is_paid',
            'b.source_type',
//            'b.source_id',
            'b.source_id as platform_id',
//            'COALESCE(p.server, CONCAT(u.login, " (",u.email,")")) AS source_name',
            'p.server as platform_name',
            'p.is_vat',
            'p.is_active',
            'u.billing_details_type',
            'u.billing_details_text',
        ));
        $command->from(BillingIncome::model()->tableName().' b');
        $command->leftJoin(Platforms::model()->tableName().' p', "b.source_type='".BillingIncome::SOURCE_TYPE_PLATFORM."' AND p.id=b.source_id");
        $command->leftJoin(Users::model()->tableName().' u',
            "(b.source_type='".BillingIncome::SOURCE_TYPE_PLATFORM."' AND p.user_id = u.id)"
            . " OR (b.source_type='".BillingIncome::SOURCE_TYPE_WEBMASTER."' AND b.source_id = u.id)"
        );
        $command->andWhere("b.source_type = '".BillingIncome::SOURCE_TYPE_PLATFORM."'");
        $command->andWhere('b.issuing_date BETWEEN :date_from AND :date_to', array(
            ':date_from' => $date_from,
            ':date_to' => $date_to,
        ));
        if($is_active !== null){
            $command->andWhere('p.is_active = :is_active', array(':is_active' => $is_active));
        }
        $result['rows'] = $command->queryAll();
        if(empty($result['rows'])) return $result;

        $platforms = array();
        $paid = array();
        foreach($result['rows'] as $row){
            if(!isset($platforms[$row['platform_id']])){
                $paid[$row['platform_id']] = BillingIncome::model()->getPaidByPlatform($row['platform_id'],1);
            }
            $platforms[$row['platform_id']] = 1;
        }

        $profits = $this->getPriceByPlatforms(array_keys($platforms));
        unset($platforms);

        foreach($result['rows'] as &$row){
            $row['debit'] = (float) (isset($profits[$row['platform_id']]) ? $profits[$row['platform_id']] : 0)
                - $paid[$row['platform_id']];
            $row['sum_with_vat'] = $row['is_vat'] == '1' ? sprintf('%.2f', $row['sum'] * (1 + Yii::app()->params->VAT / 100)) : $row['sum'];

            $result['total']['sum'] += $row['sum'];
            $result['total']['sum_with_vat'] += $row['sum_with_vat'];
            $result['total']['debit'] += $row['debit'];
            if($row['is_paid']){
                $result['total']['paid'] += $row['sum'];
            }else{
                $result['total']['not_paid'] += $row['sum'];
            }
        }

        return $result;
    }

    /**
     * Отчет по сегментам площадок. Используется для отображения сегментов на странице настроек.
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param string|null $filter
     * @return array
     */
    public function getTotalsForTags($dateFrom, $dateTo, $filter = null)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            't.id',
            't.name',
            'SUM(r.clicks) as clicks',
            'SUM(r.shows) as shows',
        ));

        $command->from(Tags::model()->tableName() . ' t');
        $command->leftJoin('platforms_tags pt', 't.id = pt.tag_id');
        if($dateFrom !== null && $dateTo !== null){
            $command->leftJoin($this->tableName() . ' r', 'pt.platform_id = r.platform_id  AND r.date BETWEEN :date_from AND :date_to', array(
                ':date_from' => $dateFrom,
                ':date_to' => $dateTo,
            ));
        }else{
            $command->leftJoin($this->tableName() . ' r', 'pt.platform_id = r.platform_id');
        }
        if($filter){
            $command->where('t.name LIKE :filter', array(':filter' => '%'.$filter.'%'));
        }
        $command->order('t.name');
        $command->group('t.id');
        $result = $command->queryAll();

        // количество площадок
        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            't.id',
            'count(p.id) as count'
        ));
        $command->from(Tags::model()->tableName() . ' t');
        $command->join('platforms_tags pt', 't.id = pt.tag_id');
        $command->join(Platforms::model()->tableName() . ' p', 'pt.platform_id = p.id');
        $command->where('p.is_active = 1 AND p.is_deleted = 0 AND p.id <> '.Platforms::DELETED_PLATFORM_ID);
        $command->group('t.id');
        $rows = $command->queryAll();

        $activePlatformsCount = array();
        foreach($rows as $dbRow){
            $activePlatformsCount[$dbRow['id']] = $dbRow['count'];
        }
        unset($rows);

        foreach($result as &$dbRow){
            $dbRow = array_merge($dbRow, array(
                'clicks' => $dbRow['clicks'] ? $dbRow['clicks'] : 0,
                'shows' => $dbRow['shows'] ? $dbRow['shows'] : 0,
                'count' => isset($activePlatformsCount[$dbRow['id']]) ? $activePlatformsCount[$dbRow['id']] : 0,
                'ctr' => sprintf('%.2f', $dbRow['shows'] ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0),
            ));
        }

        return $result;
    }
}