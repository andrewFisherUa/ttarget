<?php

/**
 * Модель отчета по показам и кликам по компании за день
 *
 * The followings are the available columns in table 'report_daily_by_campaign':
 * @property string $campaign_id
 * @property string $date
 * @property integer $shows
 * @property integer $clicks
 * @property integer $bounces
 * @property integer $fake_clicks
 * @property integer $actions
 * @property integer $declined_actions
 * @property integer $clicks_without_externals
 *
 * @method ReportDailyByCampaign findByPk()
 */
class ReportDailyByCampaign extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportDailyByCampaign the static model class
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
        return array('campaign_id', 'date');
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
		return 'report_daily_by_campaign';
	}

    /**
     * Инициализирует учет отказов
     *
     * @param $campaign_id
     */
    public function initBounces($campaign_id)
    {
        $this->updateAll(
            array(
                'bounces' => new CDbExpression('`clicks`')
            ),
            'campaign_id = :campaign_id AND bounces != clicks',
            array(':campaign_id' => $campaign_id)
        );
    }

    /**
     * Метод увеличивающий поддельные клики
     *
     * @param News $news
     * @param Platforms $platform
     * @param int $amount 1
     * @param string $date 'YYYY-mm-dd'
     *
     * @return int
     */
    public function incrFakeClicks(News $news, Platforms $platform, $amount = 1, $date = null)
    {
        if($amount > 0){
            $this->getDbConnection()->createCommand(
                "INSERT INTO `" . $this->tableName() . "` (campaign_id, date, fake_clicks) "
                ."VALUES (:campaign_id, :date, :amount) ON DUPLICATE KEY UPDATE fake_clicks = fake_clicks + :amount;"
            )->execute(array(
                ':campaign_id'  => $news->campaign->id,
                ':date'         => $date ? $date : date('Y-m-d'),
                ':amount'       => $amount,
            ));
        }else{
            $rep = $this->getDbConnection()
                ->createCommand('SELECT clicks, fake_clicks FROM `'.$this->tableName().'` WHERE `campaign_id` = :campaign_id AND `date` = :date')
                ->queryRow(true, array(
                    ':campaign_id'  => $news->campaign->id,
                    ':date'         => $date ? $date : date('Y-m-d'),
                ));

            if(!isset($rep['clicks']) || $rep['clicks'] + $rep['fake_clicks'] + $amount < 0) return false;

            $this->getDbConnection()->createCommand(
                "UPDATE `".$this->tableName()."` SET fake_clicks = fake_clicks + :amount "
                ."WHERE `campaign_id` = :campaign_id AND `date` = :date"
            )->execute(array(
                ':campaign_id'  => $news->campaign->id,
                ':date'         => $date ? $date : date('Y-m-d'),
                ':amount'       => $amount,
            ));

        }
        return true;
    }

    /**
     * @return string Возвращает количество кликов за день, с учетом поддельных
     */
    public function totalClicks()
    {
        return $this->clicks + $this->fake_clicks;
    }

    /**
     * Возвращает суммарный отчет по кампании на заданный период
     *
     * @param integer $campaign_id
     * @param string  $date_from
     * @param string  $date_to
     * @param array   $data
     *
     * @return array  array('news_id' => dbRow)
     */
    public function getTotalByPeriod($campaign_id, $date_from, $date_to, $data = null)
    {
        if (empty($campaign_id)) return array();

        $total = array(
            'campaign_id' => $campaign_id,
            'shows' => 0,
            'clicks' => 0,
            'clicks_without_externals' => 0,
            'fake_clicks' => 0,
            'actions' => 0,
        );
        if($data === null){
            $data = $this->getByPeriod($campaign_id, $date_from, $date_to);
        }
        foreach($data as $row){
            $total['shows'] += $row['shows'];
            $total['clicks'] += $row['clicks'];
            $total['clicks_without_externals'] += $row['clicks_without_externals'];
            $total['fake_clicks'] += $row['fake_clicks'];
            $total['actions'] += $row['actions'];
        }

        return $total;
    }

    public function getByPeriod($campaign_id, $date_from, $date_to)
    {
        $command = $this->getDbConnection()->createCommand();
        $command->select(array(
            'date',
            'shows',
            'clicks',
            'clicks_without_externals',
            'fake_clicks',
            'actions',
        ));
        $command->from($this->tableName());
        $command->andWhere('campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
        $command->andWhere('date BETWEEN :date_from AND :date_to', array(
            ':date_from'    => $date_from,
            ':date_to'      => $date_to,
        ));
        $command->order('date');

        return $command->queryAll();
    }

    private function _createActiveCampaignsCommand($costType, $isActive){
        $cmd = $this->getDbConnection()->createCommand()
            ->from(Campaigns::model()->tableName() . ' c')
            ->andWhere('c.is_deleted = 0');
        if($isActive){
            $cmd->andWhere('c.is_active = 1')
                ->andWhere('c.date_end >= CURDATE()');
        }
        if($costType){
            $cmd->andWhere('c.cost_type = :cost_type', array(':cost_type' => $costType));
        }
        return $cmd;
    }

    public function getForActiveCampaigns($use_date, $date_from, $date_to, $costType = null, $isActive = null)
    {
        $cmd = $this->_createActiveCampaignsCommand($costType, $isActive)
            ->join(Users::model()->tableName() . ' u', 'c.client_id = u.id')
            ->group('c.id')
            ->order('days_left');

        $select = array(
            'c.id',
            'c.name',
            'c.bounce_check',
            'c.cost_type',
            'c.max_clicks',
            '(c.clicks + c.offers_clicks) AS total_clicks',
            'c.actions AS total_actions',
            'c.ga_profile_id',
            'DATEDIFF(c.date_end, CURDATE()) AS days_left',
            'u.id AS user_id',
            'u.login AS user_login',
        );

        if($use_date){
            $cmd->leftJoin(
                $this->tableName() . ' r',
                'r.campaign_id = c.id AND r.date BETWEEN :date_from AND :date_to',
                array(
                    ':date_from'    => $date_from,
                    ':date_to'      => $date_to,
                )
            );
            array_push($select,
                'IFNULL(SUM(r.shows), 0) AS shows',
                'IFNULL(SUM(r.clicks + r.offers_clicks), 0) AS clicks',
                'IFNULL(SUM(r.bounces), 0) AS bounces',
                'IFNULL(SUM(r.actions), 0) AS actions',
                'IFNULL(SUM(r.declined_actions), 0) AS declined_actions',
                'IFNULL(SUM(r.offers_actions), 0) AS offers_actions',
                'IFNULL(SUM(r.offers_declined_actions), 0) AS offers_declined_actions'
            );
        }else{
            array_push($select, 'c.shows', '(c.clicks + c.offers_clicks) AS clicks' , 'c.bounces', 'c.actions', 'c.declined_actions', 'c.offers_actions', 'c.offers_declined_actions');
        }
        $cmd->select($select);

        $dataReader = $cmd->query();

        $report = array();
        
        foreach($dataReader as $dbRow){

        	$report[] = array_merge($dbRow, array(
                'value_left' => $this->_getValueLeft($dbRow),
                'days_left' => $dbRow['days_left'],
                'bounces' => $this->_getBounces($dbRow),
                'actions' => $dbRow['cost_type'] == Campaigns::COST_TYPE_ACTION ? $dbRow['actions'] + $dbRow['offers_actions'] : '-',
                'declined_actions' => $dbRow['cost_type'] == Campaigns::COST_TYPE_ACTION ? $dbRow['declined_actions'] + $dbRow['offers_declined_actions'] : '-',
            ));
        }
        
        return $report;
    }

    public function getGAForActiveCampaigns($use_date, $date_from, $date_to, $costType = null, $isActive = null)
    {
        $cmd = $this->_createActiveCampaignsCommand($costType, $isActive)
            ->andWhere('c.ga_profile_id IS NOT NULL')
            ->select('id');

        $result = array();

        foreach($cmd->query() as $dbRow){
            $gaData = $this->_getGAStats($dbRow['id'], $use_date, $date_from, $date_to);
            $result[$dbRow['id']] = array(
                'average_time' => $gaData['ga:avgSessionDuration'],
                'page_depth'   => $gaData['ga:pageviewsPerSession']
            );
        }

        return $result;
    }

    private function _getGAStats($campaignId, $use_date, $date_from, $date_to)
    {
        $_ga_data = array(
            'ga:avgSessionDuration' => '!',
            'ga:pageviewsPerSession' => '!'

        );
        $_cacheExpire = 7200;

        $_campaign = Campaigns::model()->notDeleted()->findByPk($campaignId);
        $GA = new CampaignGoogleAnalytics($_campaign, null, null, Yii::app()->params->GoogleAPIConfigFilename);
        if($use_date){
            $GA -> setDateRange($date_from, $date_to);
        }
        try {
            $_authUrl = $GA->authorize();
            if (!$_authUrl) {
                $_ga_data = $GA -> getTotal($_cacheExpire);
            }
        } catch (Google_Exception $e){
            Yii::log($e->__toString(), CLogger::LEVEL_ERROR);
        }

        return $_ga_data;
    }

    private function _getValueLeft($dbRow)
    {
        if($dbRow['max_clicks'] > 0){
            $value_left = $dbRow['max_clicks'];
            if($dbRow['cost_type'] == Campaigns::COST_TYPE_ACTION){
                $value_left = max($value_left - $dbRow['total_actions'], 0);
            }else{
                $value_left = max($value_left - $dbRow['total_clicks'], 0);
            }
        }else{
            $value_left = '-';
        }
        return $value_left;
    }

    private function _getBounces($dbRow)
    {
        if($dbRow['bounce_check'] > 0){
            $bounces = max($dbRow['clicks'] - $dbRow['bounces'], 0);
        }else{
            $bounces = '-';
        }
        return $bounces;
    }

    public static function addShow( $campaign_id )
    {
        $currentDate = date("Y-m-d");

        $criteria=new CDbCriteria;
        $criteria->compare('date', $currentDate );

        $reportCampaign = self::model()->find( $criteria );

        if ( count( $reportCampaign ) == 0) {
            $reportCampaign = new ReportDailyByCampaign;

            $reportCampaign->campaign_id = $campaign_id;
            $reportCampaign->date = $currentDate;
            $reportCampaign->shows = 1;

            $reportCampaign->save();
        } else {
            $reportCampaign->shows++;
            $reportCampaign->saveAttributes( array('shows') );
        }
    }

    public static function addClick( $campaign_id )
    {
        $currentDate = date("Y-m-d");

        $criteria=new CDbCriteria;
        $criteria->compare('date', $currentDate );

        $reportCampaign = self::model()->find( $criteria );

        if ( count( $reportCampaign ) == 0 ) {
            $reportCampaign = new ReportDailyByCampaign;

            $reportCampaign->date = $currentDate;
            $reportCampaign->campaign_id = $campaign_id;
            $reportCampaign->clicks = 1;

            $reportCampaign->save();
        } else {
            $reportCampaign->clicks++;
            $reportCampaign->saveAttributes( array('clicks') );
        }
    }
}