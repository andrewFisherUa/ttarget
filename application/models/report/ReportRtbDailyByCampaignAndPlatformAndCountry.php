<?php

/**
 * This is the model class for table "report_rtb_daily_by_campaign_and_platform_and_country".
 *
 * The followings are the available columns in table 'report_rtb_daily_by_campaign_and_platform_and_country':
 * @property string $campaign_id
 * @property string $platform_id
 * @property string $country
 * @property string $date
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDailyByCampaignAndPlatformAndCountry extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDailyByCampaignAndPlatformAndCountry the static model class
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
		return 'report_rtb_daily_by_campaign_and_platform_and_country';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, platform_id, country, date', 'required'),
			array('campaign_id, platform_id', 'length', 'max'=>10),
			array('country', 'length', 'max'=>2),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('campaign_id, platform_id, country, date, shows, clicks', 'safe', 'on'=>'search'),
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
			'country' => 'Country',
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
		$criteria->compare('country',$this->country,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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
			'SUM(r.shows * IFNULL(cpc.cost, 0)) / 1000 as sum_price',
		));
		$command->from($this->tableName() . ' r');
		$command->leftJoin(PlatformsRtbCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . $this->getRtbMaxCpcSql() . ')');
		$command->leftJoin(Countries::model()->tableName() . ' c', 'r.country = c.code');
		$command->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
		if($use_date){
			$command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
				':date_from'    => $date_from,
				':date_to'      => $date_to,
			));
		}
		$command->group('r.country');
		$command->order('c.name');
		$dataReader = $command->query();

		if (!$dataReader->count()) {
			return $report;
		}

		foreach ($dataReader as $dbRow) {
			$report['total']['sum_price'] += $dbRow['sum_price'];
			$report['total']['clicks'] += $dbRow['clicks'];
			$report['total']['shows'] += $dbRow['shows'];

			$report['rows'][] = array_merge($dbRow, array(
				'country_name'     => $dbRow['country_name'] == '' ? 'Не определена' : $dbRow['country_name'],
				'ctr'           => sprintf('%.2f', round($dbRow['shows'] ? ($dbRow['clicks'] * 100 / $dbRow['shows']) : 0, 2)),
				'sum_price'     => sprintf('%.2f', round($dbRow['sum_price'], 2)),
				'avg_price'     => sprintf('%.2f', round($dbRow['clicks'] ? $dbRow['sum_price'] / $dbRow['clicks'] : 0, 2)),
			));

		}

		$report['total']['sum_price'] = sprintf('%.2f', $report['total']['sum_price']);
		$report['total']['ctr'] = sprintf('%.2f', round($report['total']['shows'] ? ($report['total']['clicks'] * 100 / $report['total']['shows']) : 0,2));
		$report['total']['avg_price'] = sprintf('%.2f', round($report['total']['clicks'] ? $report['total']['sum_price'] / $report['total']['clicks'] : 0, 2));

		return $report;
	}

	public function getForCorrection($campaign_id, $date_from, $date_to)
	{
		$command = $this->getDbConnection()->createCommand();
		$command->select(array(
			'date',
			'country',
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
		$command->andWhere('clicks > 0 OR shows > 0');
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
			if(!isset($result[$dbRow['date']][$dbRow['platform_id']]['country'][$dbRow['country']])){
				$result[$dbRow['date']][$dbRow['platform_id']]['country'][$dbRow['country']] = array(
					'clicks' => 0,
					'shows' => 0,
					'actions' => 0,
				);
			}
			foreach(array('clicks', 'shows', 'actions') as $attr){
				$result[$dbRow['date']][$dbRow['platform_id']][$attr] += $dbRow[$attr];
				$result[$dbRow['date']][$dbRow['platform_id']]['country'][$dbRow['country']][$attr] += $dbRow[$attr];
				$result['total'][$attr] += $dbRow[$attr];
			}
		}
		return $result;
	}

	public static function addShow( $campaign_id, $yandexBidRequestData )
	{
		$currentDate = date("Y-m-d");

		$platform_id = $yandexBidRequestData->site->id;
		$country = $yandexBidRequestData->device->geo->country;

		$criteria=new CDbCriteria;
		$criteria->compare('country', $country );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCountry = self::model()->find( $criteria );



		if ( count( $reportCampaignAndPlatformAndCountry ) == 0) {
			$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

			$reportCampaignAndPlatformAndCountry->date = $currentDate;
			$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCountry->country = $country;
			$reportCampaignAndPlatformAndCountry->shows = 1;

			$reportCampaignAndPlatformAndCountry->save();
		} else {
			if ( $reportCampaignAndPlatformAndCountry->country == $country && $reportCampaignAndPlatformAndCountry->date == $currentDate ) {
				$reportCampaignAndPlatformAndCountry->shows++;
				$reportCampaignAndPlatformAndCountry->save();
			} else if ( $reportCampaignAndPlatformAndCountry->country != $country ) {
				$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

				$reportCampaignAndPlatformAndCountry->date = $currentDate;
				$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCountry->country = $country;
				$reportCampaignAndPlatformAndCountry->shows = 1;

				$reportCampaignAndPlatformAndCountry->save();
			}
		}
	}

	public static function addClick( $campaign_id, $yandexBidRequestData )
	{
		$currentDate = date("Y-m-d");

		$platform_id = $yandexBidRequestData->site->id;
		$country = $yandexBidRequestData->device->geo->country;

		$criteria=new CDbCriteria;
		$criteria->compare('country', $country );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCountry = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCountry ) == 0) {
			$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

			$reportCampaignAndPlatformAndCountry->date = $currentDate;
			$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCountry->country = $country;
			$reportCampaignAndPlatformAndCountry->clicks = 1;

			$reportCampaignAndPlatformAndCountry->save();
		} else {
			if ( $reportCampaignAndPlatformAndCountry->country == $country && $reportCampaignAndPlatformAndCountry->date == $currentDate ) {
				$reportCampaignAndPlatformAndCountry->clicks++;
				$reportCampaignAndPlatformAndCountry->save();
			} else if ( $reportCampaignAndPlatformAndCountry->country != $country ) {
				$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

				$reportCampaignAndPlatformAndCountry->date = $currentDate;
				$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCountry->country = $country;
				$reportCampaignAndPlatformAndCountry->clicks = 1;

				$reportCampaignAndPlatformAndCountry->save();
			}
		}
	}

	#------------------------------
	public static function addGoogleShow( $campaign_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 118547;
		$country = "RU";

		$criteria=new CDbCriteria;
		$criteria->compare('country', $country );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCountry = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCountry ) == 0) {
			$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

			$reportCampaignAndPlatformAndCountry->date = $currentDate;
			$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCountry->country = $country;
			$reportCampaignAndPlatformAndCountry->shows = 1;

			$reportCampaignAndPlatformAndCountry->save();
		} else {
			if ( $reportCampaignAndPlatformAndCountry->country == $country && $reportCampaignAndPlatformAndCountry->date == $currentDate ) {
				$reportCampaignAndPlatformAndCountry->shows++;
				$reportCampaignAndPlatformAndCountry->save();
			} else if ( $reportCampaignAndPlatformAndCountry->country != $country ) {
				$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

				$reportCampaignAndPlatformAndCountry->date = $currentDate;
				$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCountry->country = $country;
				$reportCampaignAndPlatformAndCountry->shows = 1;

				$reportCampaignAndPlatformAndCountry->save();
			}
		}
	}

	public static function addGoogleClick( $campaign_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 928374;
		$country = "RU";

		$criteria=new CDbCriteria;
		$criteria->compare('country', $country );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCountry = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCountry ) == 0) {
			$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

			$reportCampaignAndPlatformAndCountry->date = $currentDate;
			$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCountry->country = $country;
			$reportCampaignAndPlatformAndCountry->clicks = 1;

			$reportCampaignAndPlatformAndCountry->save();
		} else {
			if ( $reportCampaignAndPlatformAndCountry->country == $country && $reportCampaignAndPlatformAndCountry->date == $currentDate ) {
				$reportCampaignAndPlatformAndCountry->clicks++;
				$reportCampaignAndPlatformAndCountry->save();
			} else if ( $reportCampaignAndPlatformAndCountry->country != $country ) {
				$reportCampaignAndPlatformAndCountry = new ReportRtbDailyByCampaignAndPlatformAndCountry;

				$reportCampaignAndPlatformAndCountry->date = $currentDate;
				$reportCampaignAndPlatformAndCountry->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCountry->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCountry->country = $country;
				$reportCampaignAndPlatformAndCountry->clicks = 1;

				$reportCampaignAndPlatformAndCountry->save();
			}
		}
	}
}