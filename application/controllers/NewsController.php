<?php

class NewsController extends Controller
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
                'actions' => array('create', 'update', 'returnForm', 'addShows', 'delete', 'changeActivity'),
                'roles' => array('admin'),
            ),
            array('allow',
                'actions' => array('teasers'),
                'roles' => array('admin', 'user'),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

    public function actionCreate()
    {
        $this->save(new News('create'));
    }

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

        $this->redirect(array('campaigns/view', 'id' => $model->campaign_id));
    }

    public function actionTeasers()
    {
        $news   = $this->loadModel($_POST['id']);
        echo CJSON::encode(array('teasers' => $news->teasers));
        Yii::app()->end();
    }

    public function actionReturnForm()
    {
        if (isset($_POST['update_id'])) {
            $model = $this->loadModel($_POST['update_id']);
            $model->setScenario('edit');
        } else {
            $model = new News('create');
        }

        if (isset($_GET['c'])) {
            $model->campaign_id = $_GET['c'];
        }

        $this->disableClientScripts();
        $this->renderPartial('_form', array(
            'model'     => $model,
        ), false, true);
    }

    public function actionChangeActivity()
    {
        if (isset($_POST['update_id']) && isset($_POST['val'])) {
            $model = $this->loadModel($_POST['update_id']);
            $model->is_active = $_POST['val'] ? 1 : 0;
            if ($model->save(false, array('is_active'))) {
                echo json_encode(array('success' => true));
                Yii::app()->end();
            }
        }
        echo json_encode(array('success' => false));
        Yii::app()->end();
    }

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @param CActiveRecord $ar
     * @return News the loaded model
     * @throws CHttpException
     */
    public function loadModel($id, CActiveRecord $ar = null)
    {
        $ar  = $ar ?: News::model();
        $model = $ar->notDeleted()->findByPk($id);

        if ($model === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        return $model;
    }

    /**
     * Создает/обновляет новость
     *
     * @param News $model
     */
    private function save(News $model)
    {
        if (isset($_POST[get_class($model)])) {

            $model->attributes = $_POST[get_class($model)];

            $transaction = $model->getDbConnection()->beginTransaction();

            try {
                if ($model->save()) {
                    $transaction->commit();
                    echo json_encode(array('success' => true, 'id' => $model->id));
                    Yii::app()->end();
                } else {
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
