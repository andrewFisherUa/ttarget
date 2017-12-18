<?php

class SiteController extends Controller
{
    /**
     * Declares class-based actions.
     */
    public function actions()
    {
        return array(
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('index', 'page', 'login', 'error', 'logout', 'ext'),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array('indexGA','loginUser', 'weblog', 'weblogCSV', 'tests'),
                'roles' => array(Users::ROLE_ADMIN),
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionExt($id)
    {
        $c = new Connection(array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_MAXREDIRS => 0,
        ));

        $controlRequest = new ConnectionRequest(
            'http://api.phoenix-widget.com/api/v1/creatives'
            . '?id=142'
            . '&key=64Og5rTxv2Ro3U2q2g3N6jhjbL6o5QPh'
            . '&count='.$id
            . '&ip='.(empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'])
            . '&ua='.(empty($_SERVER['HTTP_USER_AGENT']) ? '' : urlencode($_SERVER['HTTP_USER_AGENT']))
        );
        $c->addRequest($controlRequest);
        $requests = $c->run();
        if($requests[0]->reply->result == 0){
            $externalData = CJSON::decode($requests[0]->reply->content);
            if(!empty($externalData)){
                $blockId = Yii::app()->request->getParam('b', 'ttarget_div');
                $ePlatformId = Yii::app()->request->getParam('p', '');
                echo "e=document.getElementById('".$blockId."'); if(e)e.insertAdjacentHTML('beforeend', '";
                foreach($externalData as $row){
                    echo '<div>'
                        . '<a href="'
                            . Yii::app()->params['teaserLinkBaseUrl']
                            . Crypt::encryptUrlComponent(1)
                            . '.n.' . $ePlatformId
                            . '.' . urlencode($row['url'])
                        . '" '
                        . 'onclick="window[\\\'TT\\\'].externalStats(\\\'http://api.phoenix-widget.com/'.$row['stats_url'].'\\\');" '
                        . 'data-id="1" target="_blank">'
                        . '<img src="http://images.phoenix-widget.com/crop/200/200/'.$row['image'].'"/>'
                        . '<b>'.$row['title'].'</b>'
                        . '</a>'
                        . '</div>';
                }
                echo "');\n";
            }
        }
    }


/**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        if(Yii::app()->user->role === Users::ROLE_PLATFORM){

            return $this->render('index_platform');

        }elseif(Yii::app()->user->role === Users::ROLE_USER){
            $modelC = new Campaigns('search');
            $modelC->unsetAttributes(); // clear any default values

            if (isset($_GET['Campaigns'])) {
                $modelC->attributes = $_GET['Campaigns'];
            }

            $this->render('index_user', array('modelC' => $modelC));

        } elseif(Yii::app()->user->role === Users::ROLE_ADMIN) {
        	list($period, $dateFrom, $dateTo) = Report::getPeriod();
        	$costType = Yii::app() -> request -> getParam('cost_type', null);
        	$isActive = Yii::app() -> request -> getParam('is_active', null);
        	
        	$report = ReportDailyByCampaign::model()->getForActiveCampaigns($period != 'all',$dateFrom,$dateTo,$costType,$isActive);

            $this->render('index', array(
                'period' => $period,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'report' => new CReportDataProvider($report),
                //'reportRtb' => new CReportDataProvider($reportRtb),
            ));

        }elseif(Yii::app()->user->role === Users::ROLE_WEBMASTER){
        	Offers::disableDefaultScope();
        	$status = Yii::app()->request->getParam('status', -1);
           	$offers = OffersUsers::model()->findByUserId(Yii::app()->user->id, true, $status === '' ? -1 : $status);
           	$offersReportTotal = ReportDailyByOfferUser::model()->getTotalReportByUserId(Yii::app()->user->id);
           	
           	//TODO offers notifications model
           	$offersNotifications = OffersUsersNotifications::model()->findByUserId(Yii::app()->user->id, OffersUsersNotifications::STATUS_NEW);
           	
           	$monthsName = DateHelper::getRusMonth((int)date('m', time()), false);
           	
            $this->render('index_webmaster', array(
            	'offers' => $offers,
            	'reportTotal' => $offersReportTotal,
            	'monthsName' => $monthsName,
            	'notifications' => $offersNotifications
            ));
            	 
        }elseif(Yii::app()->user->isGuest){
            $this->redirect('/login');
        }
    }

    public function actionWebLog()
    {
        if(Yii::app()->user->role === Users::ROLE_ADMIN){
        
            $topTags = Sessions::model()->getTopTags();
            
            return $this->render('weblog', array('topTags' => new CReportDataProvider($topTags)));
        }
    }

    public function actionWeblogCSV()
    {
        $data = Sessions::model()->getLast100();

        header('Content-type: text/csv');
        header('Content-disposition: attachment;filename=last100.csv');

        echo "Cookie uid;Created date;Last date;Tags;Geo\n";

        foreach($data as $row){

            if(!empty($row['tags'])) {
                $tags = array();
                foreach ($row['tags'] as $tag) {
                    $tags[] = $tag['tag_name'] . ': ' . $tag['count'];
                }
            }else{
                $tags = array('Нет переходов');
            }

            $geos = array();
            foreach($row['geo'] as $geo){
                $geos[] = GEO::getStringByName($geo['country_name'], $geo['city_name']) . '@' . $geo['last_date'];
            }
            echo iconv('UTF-8', 'WINDOWS-1251', implode(';', array(
                $row['uid'],
                $row['created_date'],
                $row['last_date'],
                implode(' | ', $tags),
                implode(', ', $geos)
            )))."\n";
        }
        Yii::app()->end();
    }
    
    public function actionTests()
    {
        $_output = array();
        $_returnVar = 0;
        
        $_testsDir = realpath(__DIR__.'/../tests');
        //$_bootstrap = $_testsDir.'/phpunit.xml';
        
        exec('phpunit --verbose --debug -c '.$_testsDir, $_output, $_returnVar);
        
        $this->renderPartial('tests', array('cmd' => 'phpunit --verbose -c '.$_testsDir,'output' => $_output));
    }
    
    public function actionIndexGA()
    {
        list($period, $dateFrom, $dateTo) = Report::getPeriod();
        $costType = Yii::app() -> request -> getParam('cost_type', null);
        $isActive = Yii::app() -> request -> getParam('is_active', null);

        $report = ReportDailyByCampaign::model()->getGAForActiveCampaigns($period != 'all',$dateFrom,$dateTo,$costType,$isActive);

        $this->renderJsonAndExit($report);
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo $error['message'];
            } else {
                $this->render('error', $error);
            }
        }
    }

    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        if (!Yii::app()->user->isGuest) {
            $this->redirect('/');
        }

        $model = new LoginForm;

        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {
                $this->redirect(Yii::app()->user->returnUrl);
            }
        }

        $this->render('login', array('model' => $model));
    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect('/');
    }

	public function actionLoginUser( $id )
	{
		if(Yii::app()->user->role == Users::ROLE_ADMIN){
			$user = Users::model()->findByPk($id);
			if($user){
				$model = new LoginForm;
				$model->attributes = array(
						'email' => $user->email,
						'rememberMe' => false
				);
				// validate user input and redirect to the previous page if valid
				if ($model->login(true)) {
					$this->redirect('/');
				} else {
					throw new CHttpException(500, 'Не удалось пройти авторизацию');
				}
			} else {
				throw new CHttpException(404, 'Пользователь не найден');
			}
		} else {
			$this->redirect('/');
		}
	}
}