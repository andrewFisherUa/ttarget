<?php

/**
 * Комманды для выполнения по расписанию
 */
class CronCommand extends CConsoleCommand
{
    public function actionIndex()
    {
        echo $this->getHelp();
    }

    /**
     * Выполняется раз в день
     */
    public function actionDaily()
    {
        $this->campaignsNotify();
        $this->campaignsReports();
        $this->campaignsNewsUrls();
        $this->platformsRequests();

        if(date('d') == '01'){
            $this->actionMonthly();
        }
    }

    /**
     * Выполняется первого числа каждого месяца
     */
    public function actionMonthly()
    {
        $this->autoWithdrawal();
    }

    /**
     * Выполняется каждый час
     */
    public function actionHourly()
    {
    }


    /**
     * Выполняется каждую минуту
     */
    public function actionMinutely()
    {
        $this->createCreatives();
        $this->updateCreatives();
        $this->getCreativesStatus();
    }


    public function actionYaTest()
    {
        $this->ya_test();
    }

    public function actionGoTest()
    {
        $this->go_test();
    }



    public function autoWithdrawal()
    {
        $users = Users::model()->findAllByAttributes(
            array('is_auto_withdrawal' => 1, 'role' => array(Users::ROLE_PLATFORM, Users::ROLE_WEBMASTER))
        );
        foreach($users as $user){
            /** @var Users $user */
            $withdrawal = array();
            if($user->role == Users::ROLE_PLATFORM) {
                foreach ($user->platforms as $platform) {
                    $debit = $platform->billing_debit;
                    if ($debit > 0) {
                        $withdrawal[] = array(
                            'source_type' => BillingIncome::SOURCE_TYPE_PLATFORM,
                            'source_id' => $platform->id,
                            'sum' => $debit,
                        );
                    }
                }
            }elseif($user->role == Users::ROLE_WEBMASTER){
                $debit = BillingIncome::model()->getDebitByUser($user);
                if($debit > 0) {
                    $withdrawal = array(
                        'source_type' => BillingIncome::SOURCE_TYPE_WEBMASTER,
                        'source_id' => $user->id,
                        'sum' => $debit,
                    );
                }
            }
            $models = array();

            if(!empty($withdrawal)) {
                BillingIncome::model()->createWithdrawalRequest($withdrawal, 'Автозапрос на вывод средств', $models);
                $errors = CHtml::errorSummary($models);
                if (!empty($errors)) {
                    Yii::log('Auto withdrawal for user_id ' . $user->id . ' failed: ' . $errors, CLogger::LEVEL_WARNING);
                }
            }
        }
    }

    public function campaignsNotify()
    {
        $date_end = date('Y-m-d', time() + (Yii::app()->params->CampaignNotifyDaysLeft * 86400));
        $campaigns = Campaigns::model()->notDeleted()->findAll(
            'is_notified = 0 AND date_end <= :date_end',
            array(':date_end' => $date_end)
        );
        foreach($campaigns as $c){
            $c->notify('CampaignNotifyDaysLeft');
        }
    }

    public function campaignsReports()
    {
        $reports = CampaignsReports::model()->getAllByDate();
        foreach($reports as $rep){
            if($rep->type == CampaignsReports::TYPE_FULL){
                $excel = new ExcelReportFull($rep->campaign, $rep->campaign->date_start, $rep->campaign->date_end);
            }else{
                $excel = new ExcelReportByPeriod($rep->campaign, $rep->campaign->date_start, date('Y-m-d', strtotime("-1 day")));
            }

            $file = tempnam(sys_get_temp_dir(), 'XLS');
            $excel->build()->save($file);
            $rep->notify($file, $excel->getFileName());
            unlink($file);
        }
    }

    /** Задания на проверку доступности URL новостей */
    public function campaignsNewsUrls()
    {
        foreach(Campaigns::model()->notDeleted()->activeWithoutLimits()->findAll() as $c){
            Yii::app()->resque->createJob('stat', 'CampaignCheckNewsUrlJob', array(
                'campaign_id' => $c->id,
            ));
        }
    }

    private function platformsRequests()
    {
        foreach(Platforms::model()->findAllWithRequestAlert() as $platform){
            Platforms::model()->updateByPk($platform->id, array('lr_notify_date' => $platform->last_request_date));
            SMail::sendMail(
                Yii::app()->params->notifyEmail,
                'Площадка "' . $platform->server . '" не запрашивает тизерные блоки',
                'PlatformRequestAlert',
                array('platform' => $platform)
            );
        }
    }

    public function createCreatives()
    {
        //echo "createCreatives\n";
        $criteria = new CDbCriteria();
        $criteria->condition = "is_created=0 AND is_active=1 AND status=0";

        foreach ( CampaignsCreatives::model()->findAll($criteria) as $creative )
        {
            //echo "\tcreateCreatives - ", $creative->attributes['id'], "\n";
            CampaignsCreatives::model()->addCreativeToRTSById( $creative->attributes['id'] );
        }
    }

    public function updateCreatives()
    {
        //echo "updateCreatives\n";
        $criteria = new CDbCriteria();
        $criteria->condition = "is_created=1 AND is_active=1 AND to_update=1";

        foreach (CampaignsCreatives::model()->findAll($criteria) as $creative ) {
            //echo "\tupdateCreatives - ", $creative->attributes['id'], "\n";
            CampaignsCreatives::model()->updateCreativeToRTSById( $creative->attributes['id'], $creative->attributes['rtb_id'] );
        }
    }

    public function getCreativesStatus()
    {
        //echo "getCreativesStatus\n";
        $criteria = new CDbCriteria();
        $criteria->condition = "status!=0 AND is_active=1";

        foreach (CampaignsCreatives::model()->findAll( $criteria ) as $creative ) {
            //echo "\tgetCreativesStatus - ", $creative->attributes['id'], "\n";
            if($creative->checkIsActive()){
                CampaignsCreatives::model()->checkCreativeStatusToRTSById( $creative->attributes['id'], $creative->attributes['rtb_id'] );
            }else{
                $creative->updateByPk($creative->id, array('is_active' => 0));
            }

        }
    }

    public function ya_test()
    {
        CampaignsCreatives::model()->ya_test();
    }

    public function go_test()
    {
        CampaignsCreatives::model()->go_test();
    }
}