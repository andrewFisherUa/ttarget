<?php

class PagesController extends Controller
{
    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('admin','returnForm','delete','save'),
                'roles'=>array('admin'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionAdmin()
    {
        $model=new Pages('search');
        if(isset($_GET['Segments']))
            $model->attributes=$_GET['Segments'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    public function actionDelete($id)
    {
        $this->loadModel($id)->delete();

        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(array('pages/admin'));
    }

	public function actionReturnForm()
	{
        if (isset($_POST['id'])) {
            $model      = $this->loadModel($_POST['id']);
        } else {
            $model = new Pages('create');
        }

        $this->disableClientScripts();
        $this->renderPartial('_form', array(
            'model' => $model,
        ), false, true);
	}

    public function actionSave()
    {
        if(isset($_REQUEST['id'])){
            $model = $this->loadModel($_REQUEST['id']);
        }else {
            $model = new Pages('create');
        }
        $this->save($model);
    }

    private function save(Pages $model)
    {
        $model->attributes = $_POST[get_class($model)];
        $success = $model->save();
        echo json_encode(array('success' => $success, 'html' => CHtml::errorSummary($model)));
        Yii::app()->end();
    }

    /**
     * @param $id
     * @return Pages
     * @throws CHttpException
     */
    public function loadModel($id)
    {
        $model = Pages::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }
}