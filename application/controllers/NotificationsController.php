<?php

class NotificationsController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
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
				'actions'=>array('index','changeNew', 'changeNewAll'),
				'roles'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
        $notifications=new Notifications('search');
        $notifications->unsetAttributes();  // clear any default values
        if(isset($_GET['News']))
            $notifications->attributes=$_GET['News'];

        
        $_filterIsNew = isset($_GET['is_new']) ? (int)$_GET['is_new'] : 1;
        $pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 10;
        
        $notifications = $notifications->search($_filterIsNew, $pageSize);
        
        $this->render('admin',array(
            'notifications' => $notifications,
        	'_filterIsNew' => $_filterIsNew,
        	'pageSize'     => $pageSize
        ));
	}

    public function actionChangeNew($id, $type)
    {
        header('Content-type: application/json');
        if($type == 'notification'){
            $model = Notifications::model()->findByPk($id);
        }else{
            $model = News::model()->findByPk($id);
        }
        if($model){
            $model->is_new = 0;
            if($model->save()){
                echo CJavaScript::jsonEncode(array('success' => true));
                Yii::app()->end();
            }
        }
        echo CJavaScript::jsonEncode(array('success' => false));
        Yii::app()->end();
    }
    
    public function actionChangeNewAll()
    {
    	header('Content-type: application/json');
    	$_ids = array();
    	$_cntNew = Notifications::model()->getNewCount();
    	if(!empty($_POST['ids'])){
    		foreach($_POST['ids'] as $id){
    			$_ids[] = (int)$id;
    		}
    	}
    	
    	if(!empty($_ids)){
    		Notifications::model()->changeNewAll($_ids);
    		$_cntNew = Notifications::model()->getNewCount();
    	}
    	echo CJavaScript::jsonEncode(array('success' => true, 'count_new' => $_cntNew));
    	Yii::app()->end();
    }

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Notifications the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Notifications::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
