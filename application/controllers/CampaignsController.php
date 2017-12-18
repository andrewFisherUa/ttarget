<?php

class CampaignsController extends Controller
{
	
	
	
	
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            //'postOnly + delete', // we only allow deletion via POST request
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
                'actions' => array('view', 'edit', 'addFakeClicks', 'create', 'update', 'returnForm', 'delete', 'transactions','offers', 'creative'),
                'roles' => array('admin'),
            ),
            array(
                'allow',
                'actions' => array('admin', 'view'),
                'roles' => array('user'),
            ),
            array(
                'allow',
                'actions' => array('report', 'statistics', 'creativeStatistics', 'googleAnalytics'),
                'roles' => array('admin', 'user'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Manages all models.
     */
    public function actionAdmin()
    {
        $model=new Campaigns('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['Campaigns']))
            $model->attributes=$_GET['Campaigns'];

        $modelN=new News('search');
        $modelN->unsetAttributes();  // clear any default values
        if(isset($_GET['News']))
            $modelN->attributes=$_GET['News'];

        $this->render('admin',array(
                'model'=>$model,
                'modelN'=>$modelN,
            ));
    }

    /**
     * Добавление поддельных кликов
     *
     * @param $id
     * @throws CHttpException
     */
    public function actionAddFakeClicks($id)
    {
        $campaign   = $this->loadModel($id, Campaigns::model()->notDeleted()->with('news'));
        $correction = new CampaignCorrection($campaign);
        $correction->set(
            $_POST['counter'],
            $_POST['date_from'],
            $_POST['date_to'],
            (int)$_REQUEST['count'],
            $_REQUEST['method'],
            isset($_REQUEST['hide_empty']),
            (int)$_REQUEST['teaser_id'],
            $_REQUEST['platform_id']
        );
        if(!isset($_REQUEST['correction'])) {
            $correction->getData();
            $this->renderPartial('partials/_correction/table', array('correction' => $correction));
        }else{
            $correction->adjust($_REQUEST['correction']);
            echo '<hr/><div class="well-small alert-success">Данные сохранены</div>';
        }
    }

    /**
     * Возвращает excel-отчеты по компании
     *
     * @param int $id
     *
     * @throws Exception
     */
    public function actionReport($id)
    {
        if (!isset($_POST['report'])) {
            throw new CHttpException(404, 'Page not found');
        }

        $campaign = $this->loadModel($id, Campaigns::model()->notDeleted()->with(array(
            'news:notDeleted' => array(
                'together' => false,
                'with' => array(
                    'teasers:notDeleted' => array(
                        'together' => false,
                        'with' => array(
                            'tags' => array('together' => true),
                        )
                    ),
                )
            ),
        )));

        $platform_id    = Yii::app()->request->getParam('platform_id', 0);
        $reportName     = (Yii::app()->user->role == 'user') ? 'ExcelReportByPeriodForClient' : $_POST['report'];

        if ($reportName == 'ExcelReportForPartner') {
            $dateFrom   = Yii::app()->request->getParam('date_from2', $campaign->date_start);
            $dateTo     = Yii::app()->request->getParam('date_to2', $campaign->date_end);
        } else {
            $dateFrom   = Yii::app()->request->getParam('date_from', $campaign->date_start);
            $dateTo     = Yii::app()->request->getParam('date_to', $campaign->date_end);
        }

        switch ($reportName) {
            case 'ExcelReportByPeriodForClient':
                ExcelReport::create($reportName, $campaign, $dateFrom, $dateTo)->build()->render();
                break;
            case 'ExcelReportByPeriod':
            case 'ExcelReportFull':
                ExcelReport::create($reportName, $campaign, $dateFrom, $dateTo)->build()->render();
                break;
            case 'ExcelReportForPartner':
                $platform = Platforms::model()->findByPk($platform_id);
                if (!$platform) {
                    throw new CHttpException(404, 'Page not found');
                }
                ExcelReport::create($reportName, $campaign, $platform, $dateFrom, $dateTo)->build()->render();
                break;
            default:
                throw new CHttpException(404, 'Page not found');
        }
        Yii::app()->end();
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
    	$campaign = $this->loadModel($id, Campaigns::model()->notDeleted()->with('news:notDeleted'));

    	if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
    		//CPA: redirecting to offers
    		//$this->redirect($this->createUrl('campaigns/offers/'.$id));
    	} elseif($campaign->cost_type == Campaigns::COST_TYPE_RTB) {
    		$this->redirect($this->createUrl('campaigns/creatives/'.$id));
    	}
    	
        list($period, $dateFrom, $dateTo) = Report::getPeriod();

        $report = ReportDailyByTeaserAndPlatform::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo, Yii::app()->request->getParam('filter', null));
        HashSort::sortBy(isset($_GET['sort']) ? $_GET['sort'] : 'create_date.desc', $report, 'teasers');

        $this->userData = Users::model()->findByPk($campaign->client_id);

        $view = (Yii::app()->user->role == Users::ROLE_ADMIN) ? 'partials/_news/admin' : 'partials/_news/user';
        $this->render('view', array(
            'view' => $view,
            'period'        => $period,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'report'        => $this->getReportDataProvider($report),
            'campaign'      => $campaign,
            'platforms'     => Platforms::model()->printable()->findAll(),
            'dataProvider'  => new GoogleDataProvider($campaign),
        ));
    }
    
    /**
     *	Предложения (offers) для кампании
     **/
    public function actionOffers($id)
    {
    	$campaign = $this->loadModel($id, Campaigns::model()->notDeleted()->with('offers'));
    
    	list($period, $dateFrom, $dateTo) = Report::getPeriod();
    
    	$report = ReportDailyByTeaserAndPlatform::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo, Yii::app()->request->getParam('filter', null));
    	HashSort::sortBy(isset($_GET['sort']) ? $_GET['sort'] : 'create_date.desc', $report, 'teasers');
    
    	$this->userData = Users::model()->findByPk($campaign->client_id);
    
    	//Transform 'created_date.desc' to 't.created_date DESC'
    	$_sortOrder = Yii::app()->request->getParam('Offers_sort', 'created_date.desc');
    	$_offersOrder = preg_replace_callback('/([a-z_]+)(\.)?(asc|desc|)/', function($matches){
    		return ('offers.'.$matches[1].' '.strtoupper(!empty($matches[3]) ? $matches[3] : 'ASC'));
    	},$_sortOrder);
    	
    	$this->render('offers', array(
    			'class'         => 'offers',
    			'view' 			=> 'partials/_offers/admin',
    			'period'        => $period,
    			'sort'			=> $_sortOrder,
    			'dateFrom'      => $dateFrom,
    			'dateTo'        => $dateTo,
    			'offers'        => Offers::model()->findByCampaignId($id,'all',null,null,$_offersOrder),
    			'hasActions'    => Offers::model()->hasAvailableCampaignActions($campaign->id),
    			'report'        => $this->getReportDataProvider($report),
    			'campaign'      => $campaign,
    			'platforms'     => Platforms::model()->printable()->findAll(),
    			'dataProvider'  => new GoogleDataProvider($campaign),
    	));
    }

    
    public function actionTransactions($id)
    {
        $campaign = $this->loadModel($id);
        list($period, $dateFrom, $dateTo) = Report::getPeriod();

        $actionsLog = ActionsLog::model()->getForCampaign(
            $campaign->id,
            $period != 'all',
            $dateFrom,
            $dateTo,
            Yii::app()->request->getParam('source_type'),
            Yii::app()->request->getParam('platform_id'),
            Yii::app()->request->getParam('user_id'),
            Yii::app()->request->getParam('status', null)
        );

        if( ! isset($_REQUEST['report']) ){
            $this->render('view', array(
                'campaign' => $campaign,
                'period' => $period,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'view' => 'partials/_transactions',
                'reportActionsLog' => new CReportDataProvider($actionsLog),
            ));
        }else {
            if($period == 'all'){
                $dateFrom = $campaign->date_start;
                $dateTo = $campaign->date_end;
            }
            ExcelReport::create('ExcelReportTransactions', $campaign, $dateFrom, $dateTo, $actionsLog)->build()->render();
            Yii::app()->end();
        }
    }

    /**
     * Статистика
     * @param $id
     */
    public function actionStatistics($id)
    {
        $campaign = $this->loadModel($id);
        list($period, $dateFrom, $dateTo) = Report::getPeriod();
        $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $reportActions = ReportDailyByCampaignAndPlatformAndAction::model()->getForCampaignByDate($campaign->id, $period != 'all', $dateFrom, $dateTo);
            $reportOffersActions = ReportDailyByOffer::model()->getForCampaignByOffer($campaign->id, $period != 'all', $dateFrom, $dateTo);
            $reportOffersDate = ReportDailyByOffer::model()->getForCampaignByDate($campaign->id, $period != 'all', $dateFrom, $dateTo);
            $reportOffersUsers = ReportDailyByOfferUser::model()->getForCampaignByUser($campaign->id, $period != 'all', $dateFrom, $dateTo);
        }
        $reportDate = ReportDailyByCampaignAndPlatform::model()->getForCampaignDate($campaign->id, $period != 'all', $dateFrom, $dateTo);
        $reportPlatformAll = ReportDailyByCampaignAndPlatform::model()->getForCampaignPlatform($campaign->id, $period != 'all', $dateFrom, $dateTo, $filter);
        $reportPlatformExternal = ReportDailyByCampaignAndPlatform::model()->getForCampaignPlatform($campaign->id, $period != 'all', $dateFrom, $dateTo, $filter, true);
        $reportPlatformInternal = ReportDailyByCampaignAndPlatform::model()->getForCampaignPlatform($campaign->id, $period != 'all', $dateFrom, $dateTo, $filter, false);
        $reportCity = ReportDailyByCampaignAndPlatformAndCity::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo);
        $reportCountry = ReportDailyByCampaignAndPlatformAndCountry::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo);

        $this->render('view', array(
            'campaign' => $campaign,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'view' => 'partials/_statistics',
            'reportActions' => (isset($reportActions) ? new CReportDataProvider($reportActions) : null),
            'reportDate' => new CReportDataProvider($reportDate),
            'reportPlatformAll' => new CReportDataProvider($reportPlatformAll),
            'reportPlatformExternal' => new CReportDataProvider($reportPlatformExternal),
            'reportPlatformInternal' => new CReportDataProvider($reportPlatformInternal),
            'reportCity' => new CReportDataProvider($reportCity),
            'reportCountry' => new CReportDataProvider($reportCountry),
            //--
            'reportOffersActions' => (isset($reportOffersActions) ? new CReportDataProvider($reportOffersActions) : null),
            'reportOffersDate' => (isset($reportOffersDate) ? new CReportDataProvider($reportOffersDate) : null),
            'reportOffersUsers' => (isset($reportOffersUsers) ? new CReportDataProvider($reportOffersUsers) : null),
        ));
    }

    /**
     * Статистика - Креатив
     * @param $id
     */
    public function actionCreativeStatistics($id)
    {
        $campaign = $this->loadModel($id);
        list($period, $dateFrom, $dateTo) = Report::getPeriod();
        $filter = isset($_REQUEST['filter']) ? $_REQUEST['filter'] : null;

        $reportDate = ReportRtbDailyByCampaignAndPlatform::model()->getForCampaignDate($campaign->id, $period != 'all', $dateFrom, $dateTo);
        $reportPlatformAll = ReportRtbDailyByCampaignAndPlatform::model()->getForCampaignPlatform($campaign->id, $period != 'all', $dateFrom, $dateTo, $filter);
        $reportCity = ReportRtbDailyByCampaignAndPlatformAndCity::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo);
        $reportCountry = ReportRtbDailyByCampaignAndPlatformAndCountry::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo);

        $this->render('view', array(
            'campaign' => $campaign,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'view' => 'partials/_creativeStatistics',
            'reportDate' => new CReportDataProvider($reportDate),
            'reportPlatformAll' => new CReportDataProvider($reportPlatformAll),
            'reportCity' => new CReportDataProvider($reportCity),
            'reportCountry' => new CReportDataProvider($reportCountry),
        ));
    }


    public function actionGoogleAnalytics()
    {
        $id = isset($_REQUEST['state']) ? $_REQUEST['state'] : (isset($_REQUEST['id']) ? $_REQUEST['id'] : NULL);
        $campaign = $this->loadModel($id);
        list($period, $dateFrom, $dateTo) = Report::getPeriod();

        $GA = new CampaignGoogleAnalytics($campaign);
        if($period != 'all'){
            $GA->setDateRange($dateFrom, $dateTo);
        }

        if(isset($_REQUEST['cancel'])){
            $GA->reset();
        }elseif(isset($_REQUEST['profile'])){
            $GA->updateProfile($_REQUEST['profile']);
        }elseif(isset($_REQUEST['code'])){
            $GA->authenticate($_REQUEST['code']);
            $this->redirect($this->createUrl('', array('id' => $campaign->id)));
        }

        $reports = array();
        $profiles = null;
        $error = null;
        $authUrl = null;

        try {
            $authUrl = $GA->authorize();

            if (!$authUrl) {
                if ($campaign->ga_profile_id) {
                    // все необходимые данные есть, запрашиваем отчеты
                    if (Yii::app()->user->role === Users::ROLE_ADMIN) {
                    	$reports['campaign'] = $GA->getByCampaigns();
                    }
                    $reports['keyword'] = $GA->getByKeyword();
                    $reports['country'] = $GA->getByCountry();
                    $reports['city'] = $GA->getByCity();
                } else {
                    // список доступных профилей
                    $profiles = $GA->getAvailableProfiles();
                }
            }
        }catch (Google_Exception $e){
            Yii::log($e->__toString(), CLogger::LEVEL_ERROR);
            $error = $e->getMessage();
        }

        $this->render('view', array(
            'campaign' => $campaign,
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'view' => 'partials/_googleAnalytics',
            'authUrl' => $authUrl,
            'profiles' => $profiles,
            'reports' => $reports,
            'error' => $error,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $this->save(new Campaigns('create'));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        $model->setScenario('edit');

        $this->save($model);
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        $model->is_deleted = 1;
        $model->save(false, array('is_deleted'));

        $this->redirect(array('users/view', 'id' => $model->client_id));
    }

    public function actionReturnForm()
    {
        if (isset($_REQUEST['update_id'])) {
            $model = Campaigns::model()->with('cities', 'countries');
            $model = $this->loadModel($_REQUEST['update_id'], $model);
            if(isset($_REQUEST['clone'])){
                $model->clone_id = $model->id;
                $model->id = null;
                $model->isNewRecord = true;
                $model->is_active = true;
                $model->date_start = date('Y-m-d');
                $model->date_end = null;
            }
        } else {
            $model = new Campaigns('create');
        }

        if (isset($_GET['u'])) {
            $model->client_id = $_GET['u'];
        }

        $this->disableClientScripts();
        $this->renderPartial('_form',
            array(
                'model' => $model,
                'countries' => Countries::model()->with('cities')->findAll()
            ),
            false, true
        );
    }
    
    /**
     *	New method for return edit form
     **/
    public function actionEdit( $id = null )
    {
    	if( !is_null($id) ){
    		//Update campaign
    		$model = Campaigns::model()->with('cities', 'countries');
    		$model = $this->loadModel($id, $model);
    		if(isset($_REQUEST['clone'])){
    			$model->clone_id = $model->id;
    			$model->id = null;
    			$model->isNewRecord = true;
    			$model->is_active = true;
    			$model->date_start = date('Y-m-d');
    			$model->date_end = null;
    		}
    			
    		if($model){
    			$_step = 'fields';
    
    			if($model->cost_type == Campaigns::COST_TYPE_ACTION){
    				$_children = CampaignsActions::model()->findByCampaignId($model->id);
    			} else if($model->cost_type == Campaigns::COST_TYPE_RTB) {
    				$_children = CampaignsActions::model()->findByCampaignId($model->id);
    			} else {
    				$_children = null;
    			}
    
    		} else {
    			throw new CHttpException(404);
    		}
    			
    	} else {
    		//Create campaign
    			
    		$model = new Campaigns('create');
    		$_step = 'type';
    			
    		if(!empty($_REQUEST['u'])){
    			$user = Users::model()->findByPk($_REQUEST['u']);
    			if(!$user){
    				throw new CHttpException(404, 'User ['.$_REQUEST['u'].' not found');
    			} else if($user->role != Users::ROLE_USER) {
    				throw new CHttpException(404, 'Invalid user['.$_REQUEST['u'].' role');
    			} else {
    				$model->client_id = $user->id;
    			}
    		}
    			
    		$_children = null;
    			
    	}
    	$this->renderPartial('form/_form',array(
    			'model' => $model,
    			'step' => $_step,
    			'types' => Campaigns::model()->getAvailableCostTypes(),
    			'countries' => Countries::model()->with('cities')->findAll()
    	));
    
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @param CActiveRecord $model null
     * @return Campaigns the loaded model
     * @throws CHttpException
     */
    public function loadModel($id, CActiveRecord $model = null)
    {
        if ($model) {
            $campaign = $model->findByPk($id);
        } else {
            $campaign = Campaigns::model()->notDeleted()->findByPk($id);
        }

        if ($campaign === null || !$this->canView($campaign)) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $campaign;
    }

    /**
     * Проверяет, может ли текущий пользователь просматривать кампанию
     *
     * @param Campaigns $campaign
     *
     * @return bool
     */
    private function canView(Campaigns $campaign)
    {
        return Yii::app()->user->role == Users::ROLE_ADMIN || $campaign->isOwner(Yii::app()->user->id);
    }

    /**
     * Создает/обновляет кампанию
     *
     * @param Campaigns $model
     */
    private function save(Campaigns $model)
    {
        if (isset($_POST[get_class($model)])) {
        	
        	$model->attributes = $_POST[get_class($model)];
            if(!isset($_POST[get_class($model)]['bounce_check'])){
                $model->bounce_check = null;
            }

            $transaction = $model->getDbConnection()->beginTransaction();

            try {
                if($model->bounce_check !== null && $model->dirty->isAttributeChanged('bounce_check')){
                    $model->bounces = $model->clicks;
                    ReportDailyByCampaign::model()->initBounces($model->id);
                }
                if ($model->save()) {
                    $transaction->commit();
                    echo json_encode(array('success' => true, 'id' => $model->id));
                    Yii::app()->end();
                } else {
                    echo CHtml::errorSummary($model);
                    $transaction->rollback();
                }

            } catch (Exception $e) {
                $transaction->rollback();
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }

        echo json_encode(array('success' => false, 'id' => (int) $model->id));
        Yii::app()->end();
    }

    /**
     * @param  array $report
     * @return CArrayDataProvider
     */
    private function getReportDataProvider(array $report)
    {
        return new CNotSortedArrayDataProvider(
            $this->convertReportDataForProvider($report),
            array(
                'keyField' => false,
                'sort' => array(
                    'attributes' => array('create_date', 'name', 'is_active', 'shows', 'clicks', 'ctr', 'id'),
                    'defaultOrder' => false
                ),
                'pagination' => false
            )
        );
    }

    /**
     * Преобразует данные отчета для дата провайдера
     *
     * @param $report
     *
     * @return array
     */
    private function convertReportDataForProvider($report)
    {
        $result = array();

        foreach ($report as $news) {

            $teasers = $news['teasers'];
            unset($news['teasers']);

            $result[] = array_merge($news, array(
                'news_name' => $news['name'],
                'news_id'   => $news['id'],
                'class'         => 'news',
                'teasers_count' => count($teasers),
            ));

            array_walk($teasers, function(&$item) use ($news) {
                $item['class'] = 'teaser';
                $item['news_name']  = $news['name'];
            });
            $result = array_merge($result, $teasers);
        }
        return $result;
    }
}
