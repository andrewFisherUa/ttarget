<?php

/**
 * This is the model class for table "campaigns_reports".
 *
 * The followings are the available columns in table 'campaigns_reports':
 * @property string $id
 * @property string $campaign_id
 * @property string $type
 * @property string $report_date
 *
 * The followings are the available model relations:
 * @property Campaigns $campaign
 */
class CampaignsReports extends CActiveRecord
{
    const TYPE_PERIOD = 'period';
    const TYPE_FULL = 'full';
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return CampaignsReports the static model class
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
		return 'campaigns_reports';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('campaign_id, report_date', 'required'),
			array('campaign_id', 'length', 'max'=>10),
			array('type', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, campaign_id, type, report_date', 'safe', 'on'=>'search'),
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
			'campaign' => array(self::BELONGS_TO, 'Campaigns', 'campaign_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'campaign_id' => 'Campaign',
			'type' => 'Type',
			'report_date' => 'Report Date',
		);
	}

    /**
     * @param mixed $date
     * @return CampaignsReports[]
     */
    public function getAllByDate($date = null)
    {
        $this->with('campaign');
        if($date === null){
            return $this->findAll('report_date = CURDATE()');
        }else{
            return $this->findAll('report_date = :date', array(':date' => $date));
        }
    }

    public function notify($file, $name)
    {
        //костыль загрузки лоадера registerScripts YiiMail, для Swift_Attachment
        $t = new YiiMailMessage();
        SMail::sendMail(
            Yii::app()->params->notifyEmail,
            ($this->type == self::TYPE_PERIOD ? 'Промежуточный' : 'Полный') . ' отчет по кампании "'.$this->campaign->name.'"',
            'CampaignReport',
            array('report' => $this),
            array(Swift_Attachment::fromPath($file)->setFilename($name.'.xlsx'))
        );
    }

}