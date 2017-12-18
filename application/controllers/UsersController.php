<?php

class UsersController extends Controller
{
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    public $menu1 = '';

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
                'actions' => array('index', 'view', 'create', 'clients'),
                'roles' => array('admin'),
            ),
            array('allow',
                'actions' => array('update', 'returnForm'),
                'roles' => array('admin', 'platform', 'webmaster'),
            ),
            array('allow',
                'actions' => array('delete'),
                'roles' => array('admin'),
            ),
        	array('allow',
        		'actions' => array('signinPlatform', 'signinSuccess'),
                'users' => array('*'),
        	),
            array('allow',
                'actions' => array('account'),
                'roles' => array('webmaster'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $model = new Users('search');
        $model->unsetAttributes(); // clear any default values
        $_GET['Users']['role'] = isset($_REQUEST['Users']['role']) ? $_REQUEST['Users']['role'] : Users::ROLE_USER;
        if (isset($_GET['Users']))
            $model->attributes = $_GET['Users'];

        $role = isset($_REQUEST['Users']['role']) ? $_REQUEST['Users']['role'] : Users::ROLE_USER;

        $this->render('index', array(
            'model' => $model,
            'role' => $_GET['Users']['role'],
        ));
    }
    
    /**
    *
    **/
    public function actionClients()
    {
    	$model = new Users('search');
    	$model->unsetAttributes(); // clear any default values
    	if (isset($_GET['Users']))
    		$model->attributes = $_GET['Users'];
    
    	$this->render('clients', array(
    			'model' => $model,
    	));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id)
    {
        $modelC = new Campaigns('search');
        $modelC->unsetAttributes(); // clear any default values

        if (isset($_GET[get_class($modelC)])) {
            $modelC->attributes = $_GET[get_class($modelC)];
        }

        $this->userData = Users::model()->findByPk($id);
		$list = null;
		switch($this->userData->role){
			case Users::ROLE_USER:
					$only_active = false;
					if(isset($_GET[get_class($modelC)])){
						$model->attributes = $_GET[get_class($modelC)];
						if(isset($_GET[get_class($modelC)]['is_active']) && $_GET[get_class($modelC)]['is_active'] == 1){
							$only_active = true;
						}
					}
					$list = $modelC->searchForUser($this->userData->id, $only_active);
				break;
			case Users::ROLE_PLATFORM:
					$model = new Platforms('search');
					$model->user_id = $this->userData->id;
					$list = $model->search();
				break;
			case Users::ROLE_WEBMASTER:
					Offers::disableDefaultScope();
					$status = Yii::app()->request->getParam('status', -1);
					$list = OffersUsers::model()->findByUserId($this->userData->id, true, $status === '' ? -1 : $status);
				break;
		}
        $this->render('view', array(
            'model' => $this->loadModel($id),
            'modelC' => $modelC,
        	'list' => $list
        ));
    }
    
    /**
     *	TODO: User account page
     **/
    public function actionAccount()
    {
    	$User = $this->loadModel(Yii::app()->user->id);
    	
    	$width = Yii::app()->params->userImageWidth;
    	$height = Yii::app()->params->userImageHeight;
    	
    	$this->render('account',array('model' => $User, 'width' => $width, 'height' => $height));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $this->save(new Users('create'));
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

        $this->redirect(array('users/index'));
    }

    public function actionReturnForm()
    {
        if (isset($_POST['update_id'])) {
            $model = $this->loadModel($_POST['update_id']);
        }  else {
            $model = new Users('create');
        }

        $width = Yii::app()->params->userImageWidth;
        $height = Yii::app()->params->userImageHeight;

        $this->disableClientScripts();
        $this->renderPartial('_form', array('model' => $model, 'width' => $width, 'height' => $height), false, true);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Users the loaded model
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        if(Yii::app()->user->role == 'platform'){
            $model = Users::model()->notDeleted()->findByPk(Yii::app()->user->id);
        }else{
            $model = Users::model()->notDeleted()->findByPk($id);
        }
        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }

    /**
     * Создает/обновляет пользователя
     *
     * @param Users $model
     */
    private function save(Users $model)
    {
        if (isset($_POST[get_class($model)])) {
            if(Yii::app()->user->role !== Users::ROLE_ADMIN){
                unset($_POST[get_class($model)]['role']);
            }
            $model->attributes  = $_POST[get_class($model)];

            if (($file = CUploadedFile::getInstance($model, 'logo')))
            {
                $model->logo = $file;
            }

            $transaction = $model->getDbConnection()->beginTransaction();

            try {
                if ($model->save() && $model->saveLogo()) {
                    $transaction->commit();
                    $this->renderJsonAndExit(array('success' => true));
                }else{
                    $transaction->rollback();
                }
            } catch (Exception $e) {
                $transaction->rollback();
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }
        $this->renderJsonAndExit(array('success' => false,
            'html' => CHtml::errorSummary($model).(isset($platform) ? CHtml::errorSummary($platform) : '')
        ));
    }

    public function actionSigninPlatform()
    {
        if(!Yii::app()->user->isGuest){
            $this->redirect('/');
        }

        $user = new Users('signin');
        if(isset($_POST['Users'])){
            $user->attributes = $_POST['Users'];
        }
        $user->role = Users::ROLE_PLATFORM;
        $user->status = Users::STATUS_MODERATION;
        $platform = new Platforms('signin');
        if(isset($_POST['Platforms'])){
            $platform->attributes = $_POST['Platforms'];
        }
        $platform->is_active = 0;

        if(isset($_POST['Users']) && isset($_POST['Platforms'])){
            $user->validate();
            $platform->validate();
            if(!$user->hasErrors() && !$platform->hasErrors()) {
                $transaction = $user->getDbConnection()->beginTransaction();
                if ($user->save(false)) {
                    $platform->user_id = $user->id;
                    if ($platform->save(false)) {
                        SMail::sendMail(
                            Yii::app()->params['registrationEmail'],
                            'Регистрация пользователя '.$user->email,
                            'SignInRequest',
                            array('user' => $user)
                        );
                        $transaction->commit();
                        $this->redirect(array('users/signinSuccess'));
                    }
                }
                $transaction->rollback();
            }
        }

        $this->render('signin_platform',array(
            'user' => $user,
            'platform' => $platform
        ));
    }

    public function actionSigninSuccess()
    {
        $this->render('signin_success');
    }
}
