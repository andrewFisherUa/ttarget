<?php

class CampaignsReportsController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('returnForm', 'update'),
                'roles' => array('admin'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

	public function actionReturnForm($campaign_id)
	{
        $periods = CampaignsReports::model()->findAllByAttributes(array(
            'campaign_id' => $campaign_id,
            'type' => CampaignsReports::TYPE_PERIOD
        ));
        $full = CampaignsReports::model()->findByAttributes(array(
            'campaign_id' => $campaign_id,
            'type' => CampaignsReports::TYPE_FULL
        ));
        if(empty($periods)){
            $periods = new CampaignsReports();
            $periods->campaign_id = $campaign_id;
            $periods->type = CampaignsReports::TYPE_PERIOD;
            $periods->report_date = date('Y-m-d', strtotime('+1 week'));
            $periods = array($periods);
        }
        $store = true;
        if($full === null){
            $campaign = Campaigns::model()->findByPk($campaign_id);
            $full = new CampaignsReports();
            $full->campaign_id = $campaign_id;
            $full->type = CampaignsReports::TYPE_FULL;
            $full->report_date = date('Y-m-d', strtotime($campaign->date_end) + 86400);
            $store = false;
        }

        $this->disableClientScripts();

		$this->renderPartial('_form', array(
            'periods' => $periods,
            'full' => $full,
            'store' => $store
        ), false, true);
	}

    public function actionUpdate()
    {
        if(isset($_POST['CampaignsReports'])){
            if(isset($_POST['store'])){
                $valid = true;
                $reports = array();
                foreach($_POST['CampaignsReports'] as $reportParams){
                    if(!empty($reportParams['id'])){
                        $report = CampaignsReports::model()->findByPk($reportParams['id']);
                    }else{
                        $report = new CampaignsReports();
                    }
                    unset($reportParams['id']);
                    $report->attributes = $reportParams;
                    $report->report_date = date('Y-m-d', strtotime($report->report_date));
                    $valid = $valid && $report->validate();
                    $reports[] = $report;
                }

                if($valid){
                    foreach($reports as $report){
                        $report->save();
                    }
                    echo json_encode(array('success' => true));
                    Yii::app()->end();
                }
            }else{
                if(!empty($_POST['CampaignsReports']['full']['campaign_id'])){
                    CampaignsReports::model()->deleteAllByAttributes(array(
                        'campaign_id' => $_POST['CampaignsReports']['full']['campaign_id']
                    ));
                }
                echo json_encode(array('success' => true));
                Yii::app()->end();
            }
        }

        echo json_encode(array('success' => false));
        Yii::app()->end();
    }
}