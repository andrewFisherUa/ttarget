<?php

class SegmentsController extends Controller
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
				'actions'=>array('admin','returnForm','delete','save', 'returnSubSegments'),
				'roles'=>array('admin'),
			),
			array('deny',
				'users'=>array('*'),
			),
		);
	}

	public function actionDelete($id)
	{
        $this->loadModel($id)->delete();
        $this->redirect(array('admin'));
	}

	public function actionAdmin()
	{
		$model=new Segments('search');
		if(isset($_GET['Segments']))
			$model->attributes=$_GET['Segments'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

    public function actionReturnForm()
    {
        if (isset($_POST['id'])) {
            $model      = $this->loadModel($_POST['id']);
        } else {
            $model = new Segments('create');
        }

        $this->disableClientScripts();
        $this->renderPartial('_form', array(
            'model' => $model,
        ), false, true);
    }

    public function actionReturnSubSegments($id)
    {
        $segment = $this->loadModel($id);
        echo implode("\n", array_values(
            CHtml::listData(Segments::model()->getOrderedSegments($segment->path), 'id', 'path')
        ));
        Yii::app()->end();
    }

    public function actionSave()
    {
        if(isset($_REQUEST['id'])){
            $model = $this->loadModel($_REQUEST['id']);
        }else {
            $model = new Segments('create');
        }
        $this->save($model);
    }

    private function save(Segments $model)
    {
        $model->attributes = $_POST[get_class($model)];
        $success = $model->save();
        echo json_encode(array('success' => $success, 'html' => CHtml::errorSummary($model)));
        Yii::app()->end();
    }

    /**
     * @param $id
     * @return Segments
     * @throws CHttpException
     */
	public function loadModel($id)
	{
        $model = Segments::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
	}
}
