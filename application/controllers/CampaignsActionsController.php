<?php

class CampaignsActionsController extends Controller
{
    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
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
                'actions' => array('returnForm', 'update', 'create', 'delete', 'changeActionLogStatus'),
                'roles' => array(Users::ROLE_ADMIN),
            ),
            array('deny',
                'users' => array('*'),
            ),
        );
    }

	public function actionReturnForm()
	{
        if(isset($_REQUEST['id'])){
            $model = $this->loadModel($_REQUEST['id']);
        }elseif(isset($_REQUEST['campaign_id'])){
            $model = new CampaignsActions('create');
            $model->campaign_id = (int) $_REQUEST['campaign_id'];
        }else{
            throw new CHttpException(404, 'The requested page does not exist.');
        }

        $this->disableClientScripts();

        $this->renderPartial('_form',
            array(
                'model' => $model,
            ),
            false, true
        );
	}

    public function actionCreate()
    {
        $model = new CampaignsActions('create');
        $this->save($model);
    }


    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        $this->save($model);
    }

    public function actionDelete($id){
        $model = $this->loadModel($id);
        $model->is_deleted = 1;
        $success = $model->save(false);
        
        echo json_encode(array('success' => $success));
        Yii::app()->end();
    }

    public function actionChangeActionLogStatus()
    {
        $log = ActionsLog::model()->findByPk((int) $_REQUEST['id']);
        if($log === null){
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        $success = $log->changeStatus((int) $_REQUEST['status']);

        $this->renderJsonAndExit(array('success' => $success, 'status' => $log->status));
    }

    private function save(CampaignsActions $model)
    {
        $model->attributes = $_POST[get_class($model)];
        $model->setScenario($model->target_type);
        
        $_newRecord = $model->isNewRecord;
        
        $success = $model->save();
        
        if($success && $_newRecord){
        	$_htmlRow = '<tr id="campaigns-action-'.$model->id.'" ><td><a href="#" onclick="return false;" class="break-word edit-action" data-id="'.$model->id.'">'.CHtml::encode($model->name).'</a></td>';
        	$_htmlRow.= '<td>'.CHtml::encode(Arr::ad($model->getAvailableTargetTypes(),$model->target_type)).'</td>';
        	$_htmlRow.= '<td>'.CHtml::encode($model->target).'</td>';
        	$_htmlRow.= '<td><a href="#" onclick="return false;" data-id="'.$model->id.'" class="btn btn-danger delete-action"><i class="icon-14 icon-trash"></i></a></td></tr>';
        } else {
        	$_htmlRow = '<td><a href="#" onclick="return false;" class="break-word edit-action" data-id="'.$model->id.'">'.CHtml::encode($model->name).'</a></td>';
        	$_htmlRow.= '<td>'.CHtml::encode(Arr::ad($model->getAvailableTargetTypes(),$model->target_type)).'</td>';
        	$_htmlRow.= '<td>'.CHtml::encode($model->target).'</td>';
        	$_htmlRow.= '<td><a href="#" onclick="return false;" data-id="'.$model->id.'" class="btn btn-danger delete-action"><i class="icon-14 icon-trash"></i></a></td>';
        }
        
        echo json_encode(array('success' => $success, 'html' => CHtml::errorSummary($model), 'htmlRow' => $_htmlRow));
        Yii::app()->end();
    }

    /**
     * @param $id
     * @return CampaignsActions
     * @throws CHttpException
     */
    private function loadModel($id)
    {
        $campaignAction = CampaignsActions::model()->findByPk($id);
        if ($campaignAction === null) {
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        return $campaignAction;
    }
}