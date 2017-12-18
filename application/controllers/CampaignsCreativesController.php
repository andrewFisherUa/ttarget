<?php
/*******************************************************************
*	file: CampaignsCreativesController.php
*	freated: 20 июля 2015 г. - 8:44:00
*
*	@author Konstantin Budylov <k.budylov@gmail.com>
*
*
*******************************************************************/


class CampaignsCreativesController extends Controller
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
						'actions' => array('index', 'returnForm', 'returnFormRejection', 'create', 'update', 'delete', 'fileupload'),
						'roles' => array(Users::ROLE_ADMIN),
				),
				array('deny',
						'users' => array('*'),
				),
		);
	}
	
	public function actionIndex($id)
	{
		$campaign = $this->loadCampaign($id, Campaigns::model()->notDeleted()->with('creatives'));

		list($period, $dateFrom, $dateTo) = Report::getPeriod();

		$this->userData = Users::model()->findByPk($campaign->client_id);

        $report = ReportRtbDaily::model()->getForCampaign($campaign->id, $period != 'all', $dateFrom, $dateTo);

        $this->render('/campaigns/creatives',array(
                'campaign' => $campaign,
                'report' => new CReportDataProvider($report),
                'period'   => $period,
                'dateFrom' => $dateFrom,
                'dateTo'   => $dateTo,
        ));
	}
	
	/**
	*    Form
	**/
	public function actionReturnForm( $id = null )
	{
	    if($id){
			$model = CampaignsCreatives::model()->findByPk($id);
	        if($model){
	        	$campaign = $this->loadCampaign($model->campaign_id);
	        	$model -> campaign_id = $campaign->id;
	        } else {
	        	throw new CHttpException(404);
	        }
		} else {
			if(!empty($_REQUEST['campaign_id'])){
			    $campaign = $this->loadCampaign($_REQUEST['campaign_id']);
			    $model = new CampaignsCreatives('create');
			    $model -> campaign_id = $campaign->id;
			} else {
			    throw new CHttpException(403);
			}
		}
		
		$this->disableClientScripts();
		$this->renderPartial('_form', array('model' => $model, 'campaign' => $campaign), false, true);
	}

	public function actionReturnFormRejection()
	{
		if (isset($_GET['n'])) {
			$model = CampaignsCreatives::model()->findByPk( $_GET['n'] );
		}

		$this->disableClientScripts();
		$this->renderPartial('_formRejection', array(
			'model' => $model
		), false, true);
	}

	public function actionDelete($id)
	{
		$criteria=new CDbCriteria;
		$criteria->compare('id', $id );

		$creative = CampaignsCreatives::model()->find($criteria);

		$creative->delete();

		echo '[]';
		Yii::app()->end();
	}


	/**
	*    Create
	**/
	public function actionCreate()
	{
	    if(!empty($_POST['CampaignsCreatives'])){
	       $model = new CampaignsCreatives('create');
	       $model -> attributes = $_POST['CampaignsCreatives'];
	       $model->typesIds = $_POST['CampaignsCreatives']['typesIds'];
	       $model->categoryIds = $_POST['CampaignsCreatives']['categoryIds'];
	       
	       if($model -> save()){
	           echo json_encode(array('success' => true));
	           Yii::app()->end();
	       } else {
	           
	           print_r($model->getErrors());
	           
	           echo json_encode(array('error'=>'Данные не были сохранены') );
	       }
	    }
	}
	
	/**
	*    Update
	**/
	public function actionUpdate( $id )
	{
	    if($id){
	        $model = CampaignsCreatives::model()->findByPk($id);
	        if($model){
	            $campaign = $this->loadCampaign($model->campaign_id);
	            
	            if(!empty($_POST['CampaignsCreatives'])){
	                
	                if(!empty($_POST['CampaignsCreatives']['filename'])){
	                    if($model->filename == $_POST['CampaignsCreatives']['filename']){
	                        unset($_POST['CampaignsCreatives']['filename']);
	                        unset($_POST['CampaignsCreatives']['filesize']);
	                    }
	                } else {
	                    unset($_POST['CampaignsCreatives']['filename']);
	                    unset($_POST['CampaignsCreatives']['filesize']);
	                }
	                
	                $model -> attributes = $_POST['CampaignsCreatives'];
	                $model->typesIds = $_POST['CampaignsCreatives']['typesIds'];
	                $model->categoryIds = $_POST['CampaignsCreatives']['categoryIds'];

					$model->moderationRequired();

					if($model -> save()){
	                    echo json_encode(array('success' => true));
	                    Yii::app()->end();
	                } else {
	                     
	                    print_r($model->getErrors());
	                     
	                    echo json_encode(array('error'=>'Данные не были сохранены') );
	                }
	            }
	            
	        } else {
	            throw new CHttpException(404);
	        }
	    }
	    
	    
	}
	
	/**
	*    Upload file
	**/
	public function actionFileupload()
	{
		if($_file = CUploadedFile::getInstanceByName('file')) {
		    $_file = $this->_uploadFile($_file);
		}
		
		if(!empty($_file) && empty($_file['error'])){
		    
		    $_type = !empty($_REQUEST['CampaignsCreatives']['type']) ? $_REQUEST['CampaignsCreatives']['type'] :
		             !empty($_REQUEST['type']) ? $_REQUEST['type'] : null;

			$_resizeTo = isset($_REQUEST['CampaignsCreatives']['size']) ? $_REQUEST['CampaignsCreatives']['size'] : null;

			switch($_type){
				case CampaignsCreatives::TYPE_IMAGE:
					   $_fileTypesAllowed = Yii::app()->params['rtbCreativeTypeImageTypesAllowed'];
					   $_size = Yii::app()->params['rtbCreativeTypeImageMaxFilesize'];
					break;
				case CampaignsCreatives::TYPE_AUDIO:
    					$_fileTypesAllowed = Yii::app()->params['rtbCreativeTypeAudioTypesAllowed'];
    					$_size = Yii::app()->params['rtbCreativeTypeAudioMaxFilesize'];
				    break;
    		    case CampaignsCreatives::TYPE_VIDEO:
        		    	$_fileTypesAllowed = Yii::app()->params['rtbCreativeTypeVideoTypesAllowed'];
        		    	$_size = Yii::app()->params['rtbCreativeTypeVideoMaxFilesize'];
    		        break;
    		    default:
    		    	     echo json_encode(array('error'=>'Unknown creative type specified') );
    		    	break;
			}
			
			try{
				$this->_validateUploadedFile($_file, $_fileTypesAllowed, $_size, $_type);
				$_file['outputFilename'] = $this->_processUploadedFile($_file, $_type, $_resizeTo, $_size);
				$_file['url'] =Yii::app()->params->docTmpUrl . '/' . $_file['outputFilename'];
			} catch(Exception $e) {
				$_file['error'] = $e->getMessage();
			}
		}
		echo json_encode(array(
		    'file' => array_diff_key($_file, array('tmpName' => ''))
		));
		Yii::app()->end();
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @param CActiveRecord $model null
	 * @return Campaigns the loaded model
	 * @throws CHttpException
	 */
	public function loadCampaign($id, CActiveRecord $model = null)
	{
		if ($model) {
			$campaign = $model->findByPk($id);
		} else {
			$campaign = Campaigns::model()->notDeleted()->findByPk($id);
		}
	
		if ($campaign === null || !$this->canView($campaign)) {
			throw new CHttpException(404, 'The requested page does not exist.');
		}
	
		return $campaign;
	}
	
	/**
	 * Проверяет, может ли текущий пользователь просматривать кампанию
	 *
	 * @param Campaigns $campaign
	 *
	 * @return bool
	 */
	private function canView(Campaigns $campaign)
	{
		return Yii::app()->user->role == Users::ROLE_ADMIN || $campaign->isOwner(Yii::app()->user->id);
	}
	
	private function _uploadFile(CUploadedFile $file)
	{
		$result = array(
	        'name' =>    $file->getName(),
	        'size' =>    $file->getSize(),
	        'tmpName' => $file->getTempName(),
	    	'type'    => $file->getType()
	    );
	    if($file->hasError){
	        $result['error'] = $file->getError();
	    }
	    return $result;
	}
	
	private function _validateUploadedFile( $file, $typesAllowed, $maxFilesize, $type )
	{
	    if(in_array($file['type'], $typesAllowed)){
	        if($type == CampaignsCreatives::TYPE_IMAGE || $file['size'] <= $maxFilesize){
	            return true;
	        } else {
	            throw new Exception("Filesize ({$file['size']}) is greather than allowed [$maxFilesize] bytes");
	        }
	    } else {
	        throw new Exception("Allowed file types is: [".join(', ', $typesAllowed)."]");
	    }
	}

	private function _processUploadedFile( &$file, $type, $resizeTo, $maxFileSize )
	{
        $_pathinfo = pathinfo($file['name']);
	    
	    //сохраняем файл во временное хранилище
	    $_outputFilename = CFile::createUniqueFileName(Yii::app()->params->docTmpPath, '.'.$_pathinfo['extension'], 't_');
	    
	    if( is_dir(Yii::app()->params->docTmpPath) && is_writable(Yii::app()->params->docTmpPath)){
			if($type == CampaignsCreatives::TYPE_IMAGE){
				$this->_resizeUploadedImage($file, $_outputFilename, $resizeTo, $maxFileSize);
			}else {
				move_uploaded_file($file['tmpName'],
					Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $_outputFilename
				);
			}
			return basename($_outputFilename);
	    } else {
	        throw new Exception('Temp directory for uploaded files are not exists or not writable.');
	    }
	}

	private function _resizeUploadedImage(&$file, $outputFilename, $resizeTo, $maxFileSize)
	{
		if (in_array($resizeTo, CampaignsCreatives::model()->getAvailableSizes(), true)) {
			$resizeTo = explode('x', $resizeTo);
		} else {
			throw new CException('Image size ' . $resizeTo . ' is not available');
		}
		$quality = 95;
		do {
			/** @var Image $img */
			$img = Yii::app()->image->load($file['tmpName']);
			$img
				->resize($resizeTo[0], $resizeTo[1], Image::NONE)
				->quality($quality)
				->save(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $outputFilename);
			$file['size'] = @filesize(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $outputFilename);
			$quality--;
		} while ($file['size'] > $maxFileSize);
	}
}



/*******************************************************************
*	encoding: UTF-8
*	tab size: 4
*	end oof file: CampaignsCreativesController.php
**/