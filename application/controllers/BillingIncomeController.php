<?php

class BillingIncomeController extends Controller
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
				'actions'=>array('returnFormPlatform', 'createPlatform'),
				'roles'=>array('platform'),
			),
			array('allow', 
				'actions'=>array('update','index','view'),
				'roles'=>array('admin'),
			),
            array('allow',
                'actions'=>array('create', 'returnForm'),
                'roles'=>array(Users::ROLE_ADMIN, Users::ROLE_WEBMASTER),
            ),
			array('allow', 
				'actions'=>array('admin','delete'),
				'roles'=>array('admin'),
			),
			array('deny', 
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
        if(Yii::app()->user->role === Users::ROLE_ADMIN) {
            $model = new BillingIncome;

            // Uncomment the following line if AJAX validation is needed
            // $this->performAjaxValidation($model);
            if (isset($_POST['BillingIncome'])) {
                if ($_POST['BillingIncome']['paid_date'] == '' || $_POST['BillingIncome']['is_paid'] == '0') {
                    $_POST['BillingIncome']['paid_date'] = null;
                }
                if ($_POST['BillingIncome']['is_paid'] == '1' && ($_POST['BillingIncome']['paid_date'] == '' || $_POST['BillingIncome']['paid_date'] == '0000-00-00')) {
                    $_POST['BillingIncome']['paid_date'] = date('Y-m-d');
                }
                $model->attributes = $_POST['BillingIncome'];

                if ($model->save()) {
                    echo json_encode(array('success' => true, 'id' => $model->primaryKey));
                    exit;
                }
                echo json_encode(array('success' => false, 'id' => $model->primaryKey));
                exit;
            }

            $this->render('create', array(
                'model' => $model,
            ));
        }elseif(Yii::app()->user->role === Users::ROLE_WEBMASTER){
            $this->_createWebmaster();
        }
	}

    private function _createWebmaster()
    {
        $models = array();
        $valid = BillingIncome::model()->createWithdrawalRequest(
            array(
                'source_id' => Yii::app()->user->id,
                'source_type' => BillingIncome::SOURCE_TYPE_WEBMASTER,
                'sum' => floatval($_POST['BillingIncome']['sum'])
            ),
            $_POST['BillingIncome']['comment'],
            $models
        );
        if($valid){
            $this->renderJsonAndExit(array('success'=>true));
        }
        $this->renderJsonAndExit(array('success'=>false, 'html' => CHtml::errorSummary($models)));
    }

    public function actionCreatePlatform()
    {
        if(isset($_POST['BillingIncome']))
        {
            $sources = array();
            foreach($_POST['BillingIncome']['source_id'] as $pos => $platform_id){
                if(isset($sources[$platform_id])){
                    $sources[$platform_id]['sum'] += floatval($_POST['BillingIncome']['sum'][$pos]);
                }else{
                    $sources[$platform_id] = array(
                        'source_type' => BillingIncome::SOURCE_TYPE_PLATFORM,
                        'source_id' => $platform_id,
                        'sum' => floatval($_POST['BillingIncome']['sum'][$pos])
                    );
                }
            }
            $models = array();
            $valid = BillingIncome::model()->createWithdrawalRequest($sources, $_POST['BillingIncome']['comment'], $models);
            if($valid){
                $this->renderJsonAndExit(array('success'=>true));
            }
            $this->renderJsonAndExit(array('success'=>false, 'html' => CHtml::errorSummary($models)));
        }
    }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		if(isset($_POST['BillingIncome']))
		{
			if($_POST['BillingIncome']['paid_date'] == '' || $_POST['BillingIncome']['is_paid'] == '0'){
				$_POST['BillingIncome']['paid_date'] = null;
			}
			if($_POST['BillingIncome']['is_paid'] == '1' && ($_POST['BillingIncome']['paid_date'] == '' || $_POST['BillingIncome']['paid_date'] == '0000-00-00 00:00:00')){
				$_POST['BillingIncome']['paid_date'] =date('Y-m-d H:i:s');
			}
			
			$model->attributes=$_POST['BillingIncome'];
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
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return BillingIncome the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=BillingIncome::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param BillingIncome $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='billing-income-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public function actionReturnForm(){
        if(Yii::app()->user->role == Users::ROLE_ADMIN) {
            if (isset($_POST['update_id'])) $model = $this->loadModel($_POST['update_id']); else $model = new BillingIncome;
            //  Comment out the following line if you want to perform ajax validation instead of client validation.
            //  You should also set  'enableAjaxValidation'=>true and
            //  comment  'enableClientValidation'=>true  in CActiveForm instantiation ( _ajax_form  file).

            //$this->performAjaxValidation($model);

            $this->disableClientScripts();
            $this->renderPartial('_form', array('model' => $model), false, true);
        }else{
            $this->_returnFormWebmaster();
        }
    }

    private function _returnFormWebmaster()
    {
        if(isset($_POST['update_id']))$model= $this->loadModel($_POST['update_id']);else $model=new BillingIncome;
        $this->disableClientScripts();
        $this->renderPartial('_form_webmaster', array('model'=>$model), false, true);
    }

    public function actionReturnFormPlatform(){
        if(isset($_POST['update_id']))$model= $this->loadModel($_POST['update_id']);else $model=new BillingIncome;
        $this->disableClientScripts();
        $this->renderPartial('_form_platform', array('model'=>$model), false, true);
    }
      
	public function   init() {
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
