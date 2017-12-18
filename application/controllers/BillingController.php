<?php

class BillingController extends Controller
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
            array('allow',
                'actions'=>array('index'),
                'roles'=>array('admin', 'platform', 'webmaster'),
            ),
            array('allow',
                'actions'=>array('create','update','returnForm', 'delete', 'report'),
                'roles'=>array('admin'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionReport()
    {
        switch ($_POST['report']) {
            case 'notpaid':
                $report = new ExcelReportBillingNotPaid($_POST['date_from'], $_POST['date_to'], $_POST['is_active']);
                break;

            case 'paid':
                $report = new ExcelReportBillingWithdrawal($_POST['date_from'], $_POST['date_to'], $_POST['is_active']);
                break;
            default:

                throw new CHttpException(404, 'Page not found');
        }
        $report->build()->render();
        Yii::app()->end();
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $model=new BillingOutgoing;

        if(isset($_POST['BillingOutgoing']))
        {
            if($_POST['BillingOutgoing']['paid_date'] == '' || $_POST['BillingOutgoing']['is_paid'] == '0'){
                $_POST['BillingOutgoing']['paid_date'] = null;
            }
            if($_POST['BillingOutgoing']['is_paid'] == '1' && ($_POST['BillingOutgoing']['paid_date'] == '' || $_POST['BillingOutgoing']['paid_date'] == '0000-00-00')){
                $_POST['BillingOutgoing']['paid_date'] =date('Y-m-d');
            }
            $model->attributes=$_POST['BillingOutgoing'];
            
            if($model->save()){
                echo json_encode(array('success'=>true,'id'=>$model->primaryKey) );
                exit;
            }
            echo json_encode(array('success'=>false,'id'=>$model->primaryKey) );
            exit;
        }

        $this->render('create',array(
            'model'=>$model,
        ));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $model=$this->loadModel($id);

        if(isset($_POST['BillingOutgoing']))
        {
            if($_POST['BillingOutgoing']['paid_date'] == '' || $_POST['BillingOutgoing']['is_paid'] == '0'){
                $_POST['BillingOutgoing']['paid_date'] = null;
            }
            if($_POST['BillingOutgoing']['is_paid'] == '1' && ($_POST['BillingOutgoing']['paid_date'] == '' || $_POST['BillingOutgoing']['paid_date'] == '0000-00-00 00:00:00')){
                $_POST['BillingOutgoing']['paid_date'] =date('Y-m-d H:i:s');
            }
            $model->attributes=$_POST['BillingOutgoing'];
            if($model->save()){
                echo json_encode(array('success'=>true,'id'=>$model->primaryKey) );
                exit;
            }
            echo json_encode(array('success'=>false,'id'=>$model->primaryKey) );
            exit;
        }

        $this->render('update',array(
            'model'=>$model,
        ));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(array('billing/index'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

        $model=new BillingOutgoing('search');
        $model->unsetAttributes();  // clear any default values
        //$model->is_paid = 0;
        if(isset($_GET['BillingOutgoing']))
            $model->attributes=$_GET['BillingOutgoing'];
            
            
        $modelI=new BillingIncome('search');
        $modelI->unsetAttributes();  // clear any default values
        //$modelI->is_paid = 0;
        // search request
        if(isset($_GET['income_search'])){
            $modelI->id=$_GET['income_search'];
        }

        if(Yii::app()->user->role === Users::ROLE_PLATFORM){
            $platforms = new Platforms('search');
            $platforms->unsetAttributes();
            $platforms->user_id = Yii::app()->user->id;

            $modelI->platform_user_id = Yii::app()->user->id;
            $this->render('index_platform',array(
                'platforms'=> $platforms,
                'modelI'=>$modelI,
                'sum' => array('incoming' => array($modelI->getPaidByUser(Yii::app()->user->id, 0), $modelI->getPaidByUser(Yii::app()->user->id, 1))),
                'currency' => PlatformsCpc::getCurrency($platforms->findByAttributes(array('user_id' => Yii::app()->user->id))->currency),
            ));
        } elseif(Yii::app()->user->role === Users::ROLE_WEBMASTER) {
            $this->_indexWebmaster($modelI);
        } else {
            if(isset($_GET['IncomeFilter'])){
                $modelI->attributes = $_GET['IncomeFilter'];
            }
            list($period, $dateFrom, $dateTo) = Report::getPeriod('all');
            
            if($period !== 'all'){
                $modelI->dateFrom = $dateFrom;
                $modelI->dateTo = $dateTo;
            }

            $this->render('index',array(
                'model'=>$model,
                'modelI'=>$modelI,
                'sum' => array('incoming' => array(BillingIncome::getSumm(0), BillingIncome::getSumm(1)), 'outgoing' => array(BillingOutgoing::getSumm(0), BillingOutgoing::getSumm(1))),
                'period' => $period,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
            ));
        }
    }

    private function _indexWebmaster(BillingIncome $modelI)
    {
        $offersUsers = new OffersUsers('search');
        $offersUsers->unsetAttributes();
        $offersUsers->user_id = Yii::app()->user->id;

//        $modelI->source_type = BillingIncome::SOURCE_TYPE_WEBMASTER;
        $modelI->source_id = Yii::app()->user->id;

        $this->render('index_webmaster',array(
            'offersUsers' => $offersUsers,
            'modelI' => $modelI,
            'debit' => BillingIncome::model()->getDebitByUser(Yii::app()->user),
            'sum' => array(
                'incoming' => array(
                    $modelI->getPaidByUser(Yii::app()->user->id, 0),
                    $modelI->getPaidByUser(Yii::app()->user->id, 1)
                )
            ),
            'currency' => PlatformsCpc::getCurrency(PlatformsCpc::CURRENCY_RUB)
        ));
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return BillingOutgoing the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model=BillingOutgoing::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    public function actionReturnForm(){
         if(isset($_POST['update_id']))$model= $this->loadModel($_POST['update_id']);else $model=new BillingOutgoing;
        //  Comment out the following line if you want to perform ajax validation instead of client validation.
        //  You should also set  'enableAjaxValidation'=>true and
        //  comment  'enableClientValidation'=>true  in CActiveForm instantiation ( _ajax_form  file).

         //$this->performAjaxValidation($model);

        $this->disableClientScripts();
        $this->renderPartial('_form', array('model'=>$model), false, true);
    }
      
    public function init() {
        $this->registerAssets();
        parent::init();
    }

    private function registerAssets(){
        Yii::app()->clientScript->registerCoreScript('jquery');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
        //Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jqui1812/js/jquery-ui-1.8.12.custom.min.js', CClientScript::POS_HEAD);
        //Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/jquery-ui.min.js', CClientScript::POS_HEAD);
        //Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/jqui1812/css/dark-hive/jquery-ui-1.8.12.custom.css','screen');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/json2/json2.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ajaxform/client_val_form.css','screen');
    }
}
