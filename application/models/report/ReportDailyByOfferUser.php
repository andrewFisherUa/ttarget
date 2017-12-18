<?php

/**
 * Модель отчета по действиям и кликам по предложению-пользователю за день
 *
 * The followings are the available columns in table 'report_daily_by_offer_user':
 * @property string $offer_user_id
 * @property string $date
 * @property integer $offers_clicks
 * @property integer $offers_actions
 *
 * @method ReportDailyByOfferUser findByPk()
 */
class ReportDailyByOfferUser extends Report
{
	
	/**
	*	Общий отчет для пользователя
	**/
	public function getTotalReportByUserId( $user_id )
	{
		$report = array(
			'actions_months'    => '0',
			'actions_year'  => '0',
			'sum_cost_months'       => '0.00',
			'sum_cost_year'        => '0.00'
		);
		
		$params = array(':user_id' => $user_id);
		
		$report['actions_year'] = Yii::app()->db->createCommand( 'SELECT SUM(R.offers_actions) as actions_total FROM report_daily_by_offer_user R WHERE R.offer_user_id IN (
												SELECT DISTINCT (OU.id) FROM offers_users OU WHERE OU.user_id = :user_id
												) AND DATE_FORMAT(R.date,\'%Y\') = DATE_FORMAT(CURRENT_DATE,\'%Y\')') -> queryScalar($params);
		
		
		$report['actions_months'] = Yii::app()->db->createCommand('  SELECT SUM(R.offers_actions) as actions_total FROM report_daily_by_offer_user R WHERE R.offer_user_id IN (
																	SELECT DISTINCT (OU.id) FROM offers_users OU WHERE OU.user_id = :user_id
																	) AND DATE_FORMAT(R.date,\'%Y-%m\') = DATE_FORMAT(CURRENT_DATE,\'%Y-%m\')') -> queryScalar($params);
		
		
		//TODO: getTotalReportByUserId
		/**
		*	SELECT SUM(clicks) as clicks_total FROM `report_daily_by_offer_user` WHERE offer_user_id IN (
			SELECT DISTINCT (OU.id) FROM offers_users OU WHERE OU.user_id = $user_id
			)
			
			SELECT SUM(R.clicks) as clicks_total FROM report_daily_by_offer_user R WHERE R.offer_user_id IN (
			SELECT DISTINCT (OU.id) FROM offers_users OU WHERE OU.user_id = $user_id
			) AND DATE_FORMAT(R.date,'%Y') = DATE_FORMAT(CURRENT_DATE,'%Y');
			
			;
		**/
		
		return $report;
	}
	
	public function getForCampaignByUser($campaign_id, $use_date, $date_from, $date_to)
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

        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'u.login AS user_login',
                'u.email AS user_email',
                'SUM(r.offers_clicks) AS clicks',
                'SUM(r.offers_actions) AS actions',
                'SUM(r.offers_declined_actions) AS declined_actions',
                'SUM(r.offers_actions * o.payment) AS sum_payment',
                'SUM(r.offers_actions * o.reward) AS sum_reward',
            ))
            ->from($this->tableName() . ' r')
            ->join(OffersUsers::model()->tableName() . ' ou', 'ou.id = r.offer_user_id')
            ->join(Offers::model()->tableName() . ' o', 'o.id = ou.offer_id')
            ->join(Users::model()->tableName() . ' u', 'u.id = ou.user_id')
            ->andWhere('o.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id))
            ->group('ou.user_id');


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
            $dbRow['avg_conversions'] = sprintf('%.2f', round(($dbRow['actions']  && $dbRow['clicks']) ? $dbRow['actions'] / $dbRow['clicks'] * 100 : 0, 2));

            $report['rows'][] = $dbRow;
        }

        $report['total'] = array_merge($report['total'], array(
            'sum_payment' => sprintf('%.2f', $report['total']['sum_payment']),
            'sum_reward' => sprintf('%.2f', $report['total']['sum_reward']),
        ));

        return $report;
    }
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByOfferUser the static model class
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
        return array('offer_user_id', 'date');
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
		return 'report_daily_by_offer_user';
	}
}
