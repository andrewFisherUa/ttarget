<?php

/**
 * This is the model class for table "report_rtb_daily".
 *
 * The followings are the available columns in table 'report_rtb_daily':
 * @property string $date
 * @property string $campaign_id
 * @property string $creative_id
 * @property string $platform_id
 * @property string $city
 * @property string $region
 * @property string $country
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDaily extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDaily the static model class
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
		return 'report_rtb_daily';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('date, campaign_id, city, region, country', 'required'),
			array('campaign_id, creative_id, platform_id', 'length', 'max'=>10),
			array('city, region, country', 'length', 'max'=>100),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('date, campaign_id, creative_id, platform_id, city, region, country, shows, clicks', 'safe', 'on'=>'search'),
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
			'date' => 'Date',
			'campaign_id' => 'Campaign',
			'creative_id' => 'Creative',
			'platform_id' => 'Platform',
			'city' => 'City',
			'region' => 'Region',
			'country' => 'Country',
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

		$criteria->compare('date',$this->date,true);
		$criteria->compare('campaign_id',$this->campaign_id,true);
		$criteria->compare('creative_id',$this->creative_id,true);
		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('region',$this->region,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function getForCampaign($campaign_id, $use_date, $date_from, $date_to)
    {
        $report = array(
            'rows'  => array(),
            'total' => array(
                'shows'  => '0',
                'clicks' => '0',
                'ctr'    => '0'
            ),
        );

        if($use_date){
            $date_condition =  ' AND r.date BETWEEN :date_from AND :date_to';
            $date_args = array( ':date_from' => $date_from, ':date_to' => $date_to );
        }else{
            $date_condition = '';
            $date_args = array();
        }

        $command = $this->getDbConnection()->createCommand()
            ->select(array(
                'c.id',
                'c.name',
                'c.size',
                'c.is_active',
                'c.status',
                'c.rtb_id',
                'c.is_winner',
                'c.cost',
                'IFNULL(SUM(r.shows), 0) AS shows',
                'IFNULL(SUM(r.clicks), 0) AS clicks',
            ))
            ->from(CampaignsCreatives::model()->tableName() . ' c')
            ->leftJoin($this->tableName() . ' r', 'c.id = r.creative_id'.$date_condition, $date_args)
            ->andWhere('c.campaign_id = :campaign_id', array(':campaign_id' => $campaign_id))
            ->group('c.id')
            ->order('c.id DESC');

        $dataReader = $command->query();

        if (!$dataReader->count()) {
            return $report;
        }

        foreach ($dataReader as $dbRow) {
            $report['total']['clicks'] += $dbRow['clicks'];
            $report['total']['shows'] += $dbRow['shows'];

            $dbRow['ctr'] = sprintf('%.2f',
                $dbRow['clicks'] > 0 ? round(($dbRow['clicks'] * 100) / $dbRow['shows'],2) : 0
            );

            $report['rows'][] = $dbRow;
        }

        $report['total']['ctr'] = sprintf('%.2f',
            $report['total']['clicks'] > 0 ?
                round(($report['total']['clicks'] * 100) / $report['total']['shows'],2)
                : 0
        );

        return $report;
    }

	public static function addShow( $campaign_id, $creative_id, $yandexBidRequestData )
	{
		$platform_id = $yandexBidRequestData->site->id;
		$city = $yandexBidRequestData->device->geo->city;
		$region = $yandexBidRequestData->device->geo->region;
		$country = $yandexBidRequestData->device->geo->country;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );
        $criteria->compare('creative_id', $creative_id );

		$reportRtbDaily = ReportRtbDaily::model()->find( $criteria );

		if ( count( $reportRtbDaily ) == 0) {
			$reportRtbDaily = new ReportRtbDaily;

			$reportRtbDaily->date = $currentDate;
			$reportRtbDaily->campaign_id = $campaign_id;
			$reportRtbDaily->creative_id = $creative_id;
			$reportRtbDaily->platform_id = $platform_id;
			$reportRtbDaily->city = $city;
			$reportRtbDaily->region = $region;
			$reportRtbDaily->country = $country;
			$reportRtbDaily->shows = 1;

			$reportRtbDaily->save();
		} else {
			$reportRtbDaily->shows = $reportRtbDaily->shows + 1;
			$reportRtbDaily->saveAttributes( array('shows') );
		}
	}

	public static function addClick( $campaign_id, $creative_id, $yandexBidRequestData )
	{

		$platform_id = $yandexBidRequestData->site->id;
		$city = $yandexBidRequestData->device->geo->city;
		$region = $yandexBidRequestData->device->geo->region;
		$country = $yandexBidRequestData->device->geo->country;

		$currentDate = date("Y-m-d");

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );
        $criteria->compare('creative_id', $creative_id );

		$reportRtbDaily = ReportRtbDaily::model()->find( $criteria );

		if ( count( $reportRtbDaily ) == 0 ) {
			$reportRtbDaily = new ReportRtbDaily;

			$reportRtbDaily->date = $currentDate;
			$reportRtbDaily->campaign_id = $campaign_id;
			$reportRtbDaily->creative_id = $creative_id;
			$reportRtbDaily->platform_id = $platform_id;
			$reportRtbDaily->city = $city;
			$reportRtbDaily->region = $region;
			$reportRtbDaily->country = $country;
			$reportRtbDaily->clicks = 1;

			$reportRtbDaily->save();
		} else {
			$reportRtbDaily->clicks = $reportRtbDaily->clicks + 1;
			$reportRtbDaily->saveAttributes( array('clicks') );
		}
	}


	//-------------------------------
	public static function addGoogleShow( $campaign_id, $creative_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 928374; //$bidRequestData->site->id;

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportRtbDaily = ReportRtbDaily::model()->find( $criteria );


		$city = "RU MSK"; //$bidRequestData->device->geo->city;
		$region = "RU-67"; //$bidRequestData->device->geo->region;
		$country = "RU"; //$bidRequestData->device->geo->country;

		if ( count( $reportRtbDaily ) == 0) {
			$reportRtbDaily = new ReportRtbDaily;

			$reportRtbDaily->date = $currentDate;
			$reportRtbDaily->campaign_id = $campaign_id;
			$reportRtbDaily->creative_id = $creative_id;
			$reportRtbDaily->platform_id = $platform_id;
			$reportRtbDaily->city = $city;
			$reportRtbDaily->region = $region;
			$reportRtbDaily->country = $country;
			$reportRtbDaily->shows = 1;

			$reportRtbDaily->save();
		} else {
			$reportRtbDaily->shows = $reportRtbDaily->shows + 1;
			$reportRtbDaily->saveAttributes( array('shows') );
		}
	}

	public static function addGoogleClick( $campaign_id, $creative_id )
	{
		$currentDate = date("Y-m-d");

		$platform_id = 928374; //$bidRequestData->site->id;

		$criteria=new CDbCriteria;
		$criteria->compare('date', $currentDate );
		$criteria->compare('platform_id', $platform_id );

		$reportRtbDaily = ReportRtbDaily::model()->find( $criteria );

		$platform_id = 928374; //$bidRequestData->site->id;
		$city = "RU MSK"; //$bidRequestData->device->geo->city;
		$region = "RU-67"; //$bidRequestData->device->geo->region;
		$country = "RU"; //$bidRequestData->device->geo->country;

		if ( count( $reportRtbDaily ) == 0 ) {
			$reportRtbDaily = new ReportRtbDaily;

			$reportRtbDaily->date = $currentDate;
			$reportRtbDaily->campaign_id = $campaign_id;
			$reportRtbDaily->creative_id = $creative_id;
			$reportRtbDaily->platform_id = $platform_id;
			$reportRtbDaily->city = $city;
			$reportRtbDaily->region = $region;
			$reportRtbDaily->country = $country;
			$reportRtbDaily->clicks = 1;

			$reportRtbDaily->save();
		} else {
			$reportRtbDaily->clicks = $reportRtbDaily->clicks + 1;
			$reportRtbDaily->saveAttributes( array('clicks') );
		}
	}
}