<?php

/**
 * Модель отчета по действиям и кликам по предложению за день
 *
 * The followings are the available columns in table 'report_daily_by_offer':
 * @property string $offer_id
 * @property string $date
 * @property integer $offers_clicks
 * @property integer $offers_actions
 * @property integer $offers_declined_actions
 *
 * @method ReportDailyByOfferUser findByPk()
 */
class ReportDailyByOffer extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByOffer the static model class
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
        return array('offer_id', 'date');
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
		return 'report_daily_by_offer';
	}

    public function getForCampaignByDate($campaign_id, $use_date, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'SUM(r.offers_clicks) AS clicks',
                'SUM(r.offers_actions) AS actions',
                'SUM(r.offers_declined_actions) AS declined_actions',
                'SUM(r.offers_actions * o.reward) AS sum_reward',
                'SUM(r.offers_actions * o.payment) AS sum_payment',
            ))
            ->group('r.date');
        return $this->_getForCampaign($command, $campaign_id, $use_date, $date_from, $date_to);
    }

    public function getForCampaignByOffer($campaign_id, $use_date, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'r.offers_clicks AS clicks',
                'r.offers_actions AS actions',
                'r.offers_declined_actions AS declined_actions',
                'o.name',
                '(r.offers_actions * o.reward) AS sum_reward',
                '(r.offers_actions * o.payment) AS sum_payment',
            ));
        return $this->_getForCampaign($command, $campaign_id, $use_date, $date_from, $date_to);
    }

    private function _getForCampaign(CDbCommand $command, $campaign_id, $use_date, $date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'clicks'            => '0',
                'actions'           => '0',
                'declined_actions'  => '0',
                'sum_payment'       => '0.00',
                'sum_reward'        => '0.00',
            ),
        );

        $command
            ->select(str_replace('`', '', $command->select) . ', r.date')
            ->from($this->tableName() . ' r')
            ->join(Offers::model()->tableName() . ' o', 'o.id = r.offer_id')
            ->andWhere('o.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));

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
            $report['total']['sum_payment'] += $dbRow['sum_payment'];
            $report['total']['sum_reward'] += $dbRow['sum_reward'];
            $report['total']['clicks'] += $dbRow['clicks'];
            $report['total']['actions'] += $dbRow['actions'];
            $report['total']['declined_actions'] += $dbRow['declined_actions'];

            $dbRow['sum_payment'] = sprintf('%.2f', $dbRow['sum_payment']);
            $dbRow['sum_reward'] = sprintf('%.2f', $dbRow['sum_reward']);
            $dbRow['avg_conversions'] = sprintf('%.2f', round(($dbRow['actions'] && $dbRow['clicks']) ? $dbRow['actions'] / $dbRow['clicks'] * 100 : 0, 2));

            $report['rows'][] = $dbRow;
        }

        $report['total'] = array_merge($report['total'], array(
            'sum_payment' => sprintf('%.2f', $report['total']['sum_payment']),
            'sum_reward' => sprintf('%.2f', $report['total']['sum_reward']),
        ));

        return $report;
    }
}