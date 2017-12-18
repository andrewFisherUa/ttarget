<?php

/**
 * This is the model class for table "report_rtb_daily_by_campaign_and_platform".
 *
 * The followings are the available columns in table 'report_rtb_daily_by_campaign_and_platform':
 * @property string $campaign_id
 * @property string $platform_id
 * @property string $date
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDailyByCampaignAndPlatform extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDailyByCampaignAndPlatform the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'report_rtb_daily_by_campaign_and_platform';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, platform_id, date', 'required'),
			array('campaign_id, platform_id', 'length', 'max'=>10),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('campaign_id, platform_id, date, shows, clicks', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'campaign_id' => 'Campaign',
			'platform_id' => 'Platform',
			'date' => 'Date',
			'shows' => 'Shows',
			'clicks' => 'Clicks',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

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
	public function getForCampaignPlatform($campaign_id, $use_date, $date_from, $date_to)
	{
		$command = $this->getDbConnection()->createCommand();
		$command->leftJoin(Platforms::model()->tableName() . ' p', 'r.platform_id = p.id');

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
			'SUM(r.shows * IFNULL(cpc.cost, 0)) / 1000 as sum_price',
		)));

		$command->from($this->tableName() . ' r');
		$command->leftJoin(PlatformsRtbCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . $this->getRtbMaxCpcSql() . ')');
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

			$report['rows'][] = array_merge($dbRow, array(
				'ctr'                    => sprintf('%.2f', round($dbRow['shows'] ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0, 2)),
				'sum_price'              => sprintf('%.2f', round($dbRow['sum_price'], 2)),
				'avg_price'              => sprintf('%.2f', round($dbRow['clicks'] ? $dbRow['sum_price'] / $dbRow['clicks'] : 0, 2)),
			));

		}

		$report['total'] = array_merge($report['total'], array(
			'sum_price' => sprintf('%.2f', $report['total']['sum_price']),
			'ctr' => sprintf('%.2f', round($report['total']['shows'] ? ($report['total']['clicks'] * 100 / $report['total']['shows']) : 0,2)),
			'avg_price' => sprintf('%.2f', round($report['total']['clicks'] ? $report['total']['sum_price'] / $report['total']['clicks'] : 0, 2)),
		));


		return $report;
	}


	public static function addShow( $campaign_id, $yandexBidRequestData )
	{
		$platform_id = $yandexBidRequestData->site->id;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatform = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatform ) == 0) {
			$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

			$reportCampaignAndPlatform->date = $currentDate;
			$reportCampaignAndPlatform->campaign_id = $campaign_id;
			$reportCampaignAndPlatform->platform_id = $platform_id;
			$reportCampaignAndPlatform->shows = 1;

			$reportCampaignAndPlatform->save();
		} else {
			if ( $reportCampaignAndPlatform->platform_id == $platform_id && $reportCampaignAndPlatform->date == $currentDate ) {
				$reportCampaignAndPlatform->shows = $reportCampaignAndPlatform->shows + 1;
				$reportCampaignAndPlatform->save();
			} else if ( $reportCampaignAndPlatform->platform_id != $platform_id ) {
				$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

				$reportCampaignAndPlatform->date = $currentDate;
				$reportCampaignAndPlatform->campaign_id = $campaign_id;
				$reportCampaignAndPlatform->platform_id = $platform_id;
				$reportCampaignAndPlatform->shows = 1;

				$reportCampaignAndPlatform->save();
			}
		}
	}

	public static function addClick( $campaign_id, $yandexBidRequestData )
	{
		$platform_id = $yandexBidRequestData->site->id;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatform = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatform ) == 0) {
			$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

			$reportCampaignAndPlatform->date = $currentDate;
			$reportCampaignAndPlatform->campaign_id = $campaign_id;
			$reportCampaignAndPlatform->platform_id = $platform_id;
			$reportCampaignAndPlatform->clicks = 1;

			$reportCampaignAndPlatform->save();
		} else {
			if ( $reportCampaignAndPlatform->platform_id == $platform_id && $reportCampaignAndPlatform->date == $currentDate ) {
				$reportCampaignAndPlatform->clicks = $reportCampaignAndPlatform->clicks + 1;
				$reportCampaignAndPlatform->save();
			} else if ( $reportCampaignAndPlatform->platform_id != $platform_id ) {
				$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

				$reportCampaignAndPlatform->date = $currentDate;
				$reportCampaignAndPlatform->campaign_id = $campaign_id;
				$reportCampaignAndPlatform->platform_id = $platform_id;
				$reportCampaignAndPlatform->clicks = 1;

				$reportCampaignAndPlatform->save();
			}
		}
	}

	#--------------------------------------------
	public static function addGoogleShow( $campaign_id )
	{
		$platform_id = 928374;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;

		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatform = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatform ) == 0) {
			$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

			$reportCampaignAndPlatform->date = $currentDate;
			$reportCampaignAndPlatform->campaign_id = $campaign_id;
			$reportCampaignAndPlatform->platform_id = $platform_id;
			$reportCampaignAndPlatform->shows = 1;

			$reportCampaignAndPlatform->save();
		} else {
			if ( $reportCampaignAndPlatform->platform_id == $platform_id && $reportCampaignAndPlatform->date == $currentDate ) {
				$reportCampaignAndPlatform->shows = $reportCampaignAndPlatform->shows + 1;
				$reportCampaignAndPlatform->save();
			} else if ( $reportCampaignAndPlatform->platform_id != $platform_id ) {
				$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

				$reportCampaignAndPlatform->date = $currentDate;
				$reportCampaignAndPlatform->campaign_id = $campaign_id;
				$reportCampaignAndPlatform->platform_id = $platform_id;
				$reportCampaignAndPlatform->shows = 1;

				$reportCampaignAndPlatform->save();
			}
		}
	}

	public static function addGoogleClick( $campaign_id )
	{
		$platform_id = 928374;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;

		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatform = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatform ) == 0) {
			$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

			$reportCampaignAndPlatform->date = $currentDate;
			$reportCampaignAndPlatform->campaign_id = $campaign_id;
			$reportCampaignAndPlatform->platform_id = $platform_id;
			$reportCampaignAndPlatform->clicks = 1;

			$reportCampaignAndPlatform->save();
		} else {
			if ( $reportCampaignAndPlatform->platform_id == $platform_id && $reportCampaignAndPlatform->date == $currentDate ) {
				$reportCampaignAndPlatform->clicks = $reportCampaignAndPlatform->clicks + 1;
				$reportCampaignAndPlatform->save();
			} else if ( $reportCampaignAndPlatform->platform_id != $platform_id ) {
				$reportCampaignAndPlatform = new ReportRtbDailyByCampaignAndPlatform;

				$reportCampaignAndPlatform->date = $currentDate;
				$reportCampaignAndPlatform->campaign_id = $campaign_id;
				$reportCampaignAndPlatform->platform_id = $platform_id;
				$reportCampaignAndPlatform->clicks = 1;

				$reportCampaignAndPlatform->save();
			}
		}
	}
}