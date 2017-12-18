<?php

class OptionsController extends Controller
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
                'actions' => array('index', 'create', 'update', 'returnForm', 'admin', 'delete'),
                'roles' => array(Users::ROLE_ADMIN),
            ),
            array(
                'allow',
                'actions' => array('constructor', 'deleteBlock', 'clientCode'),
                'roles' => array(Users::ROLE_ADMIN, Users::ROLE_PLATFORM)
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionConstructor()
    {
        if(isset($_REQUEST['id'])){
            $model = Blocks::model()->findByPk($_REQUEST['id']);
            if($model === null){
                throw new CHttpException(404, 'Page not found');
            }
            $clientCode = ClientCode::model()->findByAttributes(array('platform_id' => $model->platform_id));
        }
        if(!isset($model)){
            $model = new Blocks();
        }
        if(!isset($clientCode)){
            $clientCode = new ClientCode();
        }
        if(isset($_POST['Blocks'])){
            $model->attributes = $_POST['Blocks'];

            $isNew = $model->getIsNewRecord();
            if($model->save()){
                if($isNew) {
                    $this->redirect(array('constructor', 'id' => $model->id, '#' => 'htmlContainer'));
                }else{
                    $this->redirect(array('constructor'));
                }
            }
        }
        if(Yii::app()->user->role == Users::ROLE_ADMIN){
            $platforms = Platforms::model()->printable()->findAll();
        }else{
            $platforms = Platforms::model()->printable()->findAllByAttributes(array('user_id' => Yii::app()->user->id));
        }
        $this->render('blockConstructor', array(
            'model' => $model,
            'platforms' => $platforms,
            'clientCode' => $clientCode
        ));
    }

    public function actionDeleteBlock($id)
    {
        if(Yii::app()->user->role == Users::ROLE_ADMIN){
            $model = Blocks::model()->findByPk($id);
        }else{
            $model = Blocks::model()->with('platform')->findByPk($id, 'platform.user_id = :userId', array(':userId' => Yii::app()->user->id));
        }
        if($model === null){
            throw new CHttpException(404, 'Page not found');
        }
        $model->delete();

        $this->redirect(array('constructor'));
    }

    public function actionClientCode()
    {
        if(isset($_REQUEST['ClientCode']['platform_id'])){
            if(Yii::app()->user->role !== Users::ROLE_ADMIN){
                $platform = Platforms::model()->findByPk($_REQUEST['ClientCode']['platform_id']);
                if(null === $platform || $platform->user_id !== Yii::app()->user->id){
                    throw new CHttpException(403);
                }
            }
            $model = ClientCode::model()->findByPk($_REQUEST['ClientCode']['platform_id']);
        }
        if(!isset($model)){
            $model = new ClientCode();
        }
        if(isset($_REQUEST['ClientCode']['path'])){
            $model->attributes = $_REQUEST['ClientCode'];
            if(isset($_REQUEST['control'])){
                $this->_downloadControlScript($model);
            }
            if($model->validate() && $model->validateDeployment() && $model->save(false)){
            }
        }

        $this->renderPartial('partials/clientCode', array('clientCode' => $model, 'form' => new CActiveForm()));
    }

    private function _downloadControlScript(ClientCode $clientCode){
        header('Content-Type: text/x-php');
        header('Content-Disposition: attachment;filename="script.php"');
        header('Cache-Control: max-age=0');

        print str_replace(
            array('%PATH%', '%API_KEY%'),
            array($clientCode->path, $clientCode->getApiKey()),
            file_get_contents(Yii::getPathOfAlias('application.data.clientCode') . '/control.php')
        );

        Yii::app()->end();
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        if (isset($_REQUEST['version'])){
            RedisPlatform::instance()->setVersion((int) $_REQUEST['version']);
        }

        list($period, $dateFrom, $dateTo) = Report::getPeriod();

        $reportTags = ReportDailyByPlatform::model()->getTotalsForTags(
            $period == 'all' ? null : $dateFrom,
            $period == 'all' ? null : $dateTo,
            isset($_GET['Tags']['name']) ? $_GET['Tags']['name'] : null
        );

        $this->render('index', array(
//            'model' => $model,
            'version' => RedisPlatform::instance()->getVersion(),
            'reportTags' => $this->getDataProvider($reportTags, 'name'),
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ));
    }

    /**
     * @param $data
     * @param bool|string $defaultOrder
     * @return CArrayDataProvider
     * @todo выделить создание провайдеров в хелпер
     */
    public function getDataProvider($data, $defaultOrder = false)
    {
        return new CArrayDataProvider($data, array(
            'sort' => array(
                'attributes' => isset($data[0]) ? array_keys($data[0]) : array(),
                'defaultOrder' => $defaultOrder,
            ),
            'keyField' => false,
        ));
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate()
    {
        $this->save(new Tags('create'));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id)
    {
        $this->save($this->loadModel($id));
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        if ($model->canDelete($id)) {
            $this->loadModel($id)->delete();
            $this->redirect(array('/options'));
        } else {
            $this->redirect(array('/options?error'));
        }
    }

    public function actionReturnForm()
    {
        if (isset($_POST['update_id'])) $model = $this->loadModel($_POST['update_id']); else $model = new Tags;

        $this->disableClientScripts();
        $this->renderPartial('_form', array('model' => $model), false, true);
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Tags the loaded model
     * @throws CHttpException
     */
    private function loadModel($id)
    {
        $model = Tags::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    /**
     * Создает/обновляет сегмент
     *
     * @param Tags $model
     */
    private function save(Tags $model)
    {
        if (isset($_POST[get_class($model)])) {

            $model->attributes = $_POST[get_class($model)];

            $transaction = $model->getDbConnection()->beginTransaction();

            try {
                if ($model->save()) {
                    $transaction->commit();
                    echo json_encode(array('success' => true, 'id' => $model->id));
                    Yii::app()->end();
                } else { var_dump($model->getErrors()); exit;
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
}
