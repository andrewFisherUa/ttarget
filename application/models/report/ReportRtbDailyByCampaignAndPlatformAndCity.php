<?php

/**
 * This is the model class for table "report_rtb_daily_by_campaign_and_platform_and_city".
 *
 * The followings are the available columns in table 'report_rtb_daily_by_campaign_and_platform_and_city':
 * @property string $campaign_id
 * @property string $platform_id
 * @property string $city
 * @property string $date
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDailyByCampaignAndPlatformAndCity extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDailyByCampaignAndPlatformAndCity the static model class
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
		return 'report_rtb_daily_by_campaign_and_platform_and_city';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, platform_id, city, date', 'required'),
			array('campaign_id, platform_id, city', 'length', 'max'=>10),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('campaign_id, platform_id, city, date, shows, clicks', 'safe', 'on'=>'search'),
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
			'city' => 'City',
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
		$criteria->compare('city',$this->city,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Отчет по кампании сгруппированный по городу
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
				'shows'            => '0',
				'clicks'           => '0',
				'ctr'              => '0.00',
				'sum_price'        => '0.00',
				'avg_price'        => '0.00'
			),
		);

		$command = $this->getDbConnection()->createCommand();
		$command->select(array(
			'r.city AS city_name',
			'SUM(r.clicks) AS clicks',
			'SUM(r.shows) AS shows',
			'SUM(r.shows * IFNULL(cpc.cost, 0)) / 1000 AS sum_price',
		));
		$command->from($this->tableName() . ' r');
		$command->leftJoin(PlatformsRtbCpc::model()->tableName() . ' cpc', 'r.platform_id = cpc.platform_id AND cpc.date = (' . $this->getRtbMaxCpcSql() . ')');
		$command->andWhere('r.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id));
		if($use_date){
			$command->andWhere('r.date BETWEEN :date_from AND :date_to', array(
				':date_from'    => $date_from,
				':date_to'      => $date_to,
			));
		}
		$command->group('r.city');
		$command->order('r.city');
		$dataReader = $command->query();


		if (!$dataReader->count()) {
			return $report;
		}

		foreach ($dataReader as $dbRow) {
			$report['total']['sum_price'] += $dbRow['sum_price'];
			$report['total']['clicks'] += $dbRow['clicks'];
			$report['total']['shows'] += $dbRow['shows'];

			$report['rows'][] = array_merge($dbRow, array(
				'city_name'     => $dbRow['city_name'] == '' ? 'Не определен' : $dbRow['city_name'],
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
			'city',
			'platform_id',
			'clicks',
			'shows',
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
			)
		);

		foreach($command->query() as $dbRow){
			if(!isset($result[$dbRow['date']][$dbRow['platform_id']])){
				$result[$dbRow['date']][$dbRow['platform_id']] = array(
					'city' => array(),
					'clicks' => 0,
					'shows' => 0,
				);
			}
			if(!isset($result[$dbRow['date']][$dbRow['platform_id']]['city'][$dbRow['city']])){
				$result[$dbRow['date']][$dbRow['platform_id']]['city'][$dbRow['city']] = array(
					'clicks' => 0,
					'shows' => 0,
					'actions' => 0,
				);
			}
			foreach(array('clicks', 'shows', 'actions') as $attr){
				$result[$dbRow['date']][$dbRow['platform_id']][$attr] += $dbRow[$attr];
				$result[$dbRow['date']][$dbRow['platform_id']]['city'][$dbRow['city']][$attr] += $dbRow[$attr];
				$result['total'][$attr] += $dbRow[$attr];
			}
		}
		return $result;
	}

	public static function addShow( $campaign_id, $yandexBidRequestData )
	{
		$currentDate = date("Y-m-d");

		$platform_id = $yandexBidRequestData->site->id;
		$city = $yandexBidRequestData->device->geo->city;

		$criteria=new CDbCriteria;
		$criteria->compare('city', $city );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCity = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCity ) == 0) {
			$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

			$reportCampaignAndPlatformAndCity->date = $currentDate;
			$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCity->city = $city;
			$reportCampaignAndPlatformAndCity->shows = 1;

			$reportCampaignAndPlatformAndCity->save();
		} else {
			if ( $reportCampaignAndPlatformAndCity->city == $city && $reportCampaignAndPlatformAndCity->date == $currentDate ) {
				$reportCampaignAndPlatformAndCity->shows = $reportCampaignAndPlatformAndCity->shows + 1;
				$reportCampaignAndPlatformAndCity->save();
			} else if ( $reportCampaignAndPlatformAndCity->city != $city ) {
				$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity();

				$reportCampaignAndPlatformAndCity->date = $currentDate;
				$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCity->city = $city;
				$reportCampaignAndPlatformAndCity->shows = 1;

				$reportCampaignAndPlatformAndCity->save();
			}
		}
	}

	public static function addClick( $campaign_id, $yandexBidRequestData )
	{
		$currentDate = date("Y-m-d");

		$platform_id = $yandexBidRequestData->site->id;
		$city = $yandexBidRequestData->device->geo->city;

		$criteria=new CDbCriteria;
		$criteria->compare('city', $city );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCity = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCity ) == 0) {
			$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

			$reportCampaignAndPlatformAndCity->date = $currentDate;
			$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCity->city = $city;
			$reportCampaignAndPlatformAndCity->clicks = 1;

			$reportCampaignAndPlatformAndCity->save();
		} else {
			if ( $reportCampaignAndPlatformAndCity->city == $city && $reportCampaignAndPlatformAndCity->date == $currentDate ) {
				$reportCampaignAndPlatformAndCity->clicks = $reportCampaignAndPlatformAndCity->clicks + 1;
				$reportCampaignAndPlatformAndCity->save();
			} else if ( $reportCampaignAndPlatformAndCity->city != $city ) {
				$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

				$reportCampaignAndPlatformAndCity->date = $currentDate;
				$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCity->city = $city;
				$reportCampaignAndPlatformAndCity->clicks = 1;

				$reportCampaignAndPlatformAndCity->save();
			}
		}
	}


	#--------------------------------------------
	public static function addGoogleShow( $campaign_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 928374; //$yandexBidRequestData->site->id;
		$city = "RU MSK"; //$yandexBidRequestData->device->geo->city;

		$criteria=new CDbCriteria;
		$criteria->compare('city', $city );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCity = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCity ) == 0) {
			$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

			$reportCampaignAndPlatformAndCity->date = $currentDate;
			$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCity->city = $city;
			$reportCampaignAndPlatformAndCity->shows = 1;

			$reportCampaignAndPlatformAndCity->save();
		} else {
			if ( $reportCampaignAndPlatformAndCity->city == $city && $reportCampaignAndPlatformAndCity->date == $currentDate ) {
				$reportCampaignAndPlatformAndCity->shows = $reportCampaignAndPlatformAndCity->shows + 1;
				$reportCampaignAndPlatformAndCity->save();
			} else if ( $reportCampaignAndPlatformAndCity->city != $city ) {
				$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

				$reportCampaignAndPlatformAndCity->date = $currentDate;
				$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCity->city = $city;
				$reportCampaignAndPlatformAndCity->shows = 1;

				$reportCampaignAndPlatformAndCity->save();
			}
		}
	}

	public static function addGoogleClick( $campaign_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 928374; //$yandexBidRequestData->site->id;
		$city = "RU MSK"; //$yandexBidRequestData->device->geo->city;

		$criteria=new CDbCriteria;
		$criteria->compare('city', $city );
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportCampaignAndPlatformAndCity = self::model()->find( $criteria );

		if ( count( $reportCampaignAndPlatformAndCity ) == 0) {
			$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

			$reportCampaignAndPlatformAndCity->date = $currentDate;
			$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
			$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
			$reportCampaignAndPlatformAndCity->city = $city;
			$reportCampaignAndPlatformAndCity->clicks = 1;

			$reportCampaignAndPlatformAndCity->save();
		} else {
			if ( $reportCampaignAndPlatformAndCity->city == $city && $reportCampaignAndPlatformAndCity->date == $currentDate ) {
				$reportCampaignAndPlatformAndCity->clicks = $reportCampaignAndPlatformAndCity->clicks + 1;
				$reportCampaignAndPlatformAndCity->save();
			} else if ( $reportCampaignAndPlatformAndCity->city != $city ) {
				$reportCampaignAndPlatformAndCity = new ReportRtbDailyByCampaignAndPlatformAndCity;

				$reportCampaignAndPlatformAndCity->date = $currentDate;
				$reportCampaignAndPlatformAndCity->campaign_id = $campaign_id;
				$reportCampaignAndPlatformAndCity->platform_id = $platform_id;
				$reportCampaignAndPlatformAndCity->city = $city;
				$reportCampaignAndPlatformAndCity->clicks = 1;

				$reportCampaignAndPlatformAndCity->save();
			}
		}
	}

}