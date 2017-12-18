<?php

/**
 * This is the model class for table "report_rtb_daily_by_campaign".
 *
 * The followings are the available columns in table 'report_rtb_daily_by_campaign':
 * @property string $campaign_id
 * @property string $date
 * @property string $shows
 * @property string $clicks
 */
class ReportRtbDailyByCampaign extends Report
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ReportRtbDailyByCampaign the static model class
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
		return 'report_rtb_daily_by_campaign';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, date', 'required'),
			array('campaign_id', 'length', 'max'=>10),
			array('shows, clicks', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('campaign_id, date, shows, clicks', 'safe', 'on'=>'search'),
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
		$criteria->compare('date',$this->date,true);
		$criteria->compare('shows',$this->shows,true);
		$criteria->compare('clicks',$this->clicks,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
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
			'c.cost_type',
			'c.clicks AS total_clicks',
			'c.actions AS total_actions',
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
				'IFNULL(SUM(r.clicks + r.offers_clicks), 0) AS clicks'
			);
		}else{
			array_push($select, 'c.shows', 'c.clicks');
		}
		$cmd->select($select);

		$dataReader = $cmd->query();

		$report = array();

		foreach($dataReader as $dbRow){

			$report[] = array_merge($dbRow, array(
				'value_left' => $this->_getValueLeft($dbRow),
				'days_left' => $dbRow['days_left'],
				'actions' => $dbRow['cost_type'] == Campaigns::COST_TYPE_ACTION ? $dbRow['actions'] + $dbRow['offers_actions'] : '-',
			));
		}

		return $report;
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
}