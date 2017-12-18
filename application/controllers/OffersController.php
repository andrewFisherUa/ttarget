<?php

class OffersController extends Controller
{
	/**
	*	Возвращает JSON-cписок вебмастеров для фильтра в форме редактирования заявки
	**/
	public function actionWMSearch()
	{
		$_data = array();
		
		$_searchQuery = !empty($_POST['data']['q']) ? $_POST['data']['q'] : null;
		$_users = Users::model()->searchByRoleAndEmail(Users::ROLE_WEBMASTER, $_searchQuery);
		foreach($_users as $user){
			$_data[] = array(
				'id' => $user->id,
				'text' => $user->email
			);
		}
		
		echo json_encode(array('q' => $_searchQuery, 'results' => $_data));
		Yii::app()->end();
	}
	
	/**
	 *	Список заявок на офферы в кабинете администратора
	 **/
	public function actionRequests()
	{
		if(Yii::app()->user->role === Users::ROLE_ADMIN) {
				
			list($_filterPeriod, $_filterDateFrom, $_filterDateTo) = Report::getPeriod( Yii::app() -> request -> getParam('period', 'all') );
			
			$_filterStatus = Yii::app() -> request -> getParam('status', null);
			$_filterSearch = Yii::app() -> request -> getParam('filter', null);
				
			$_requests = OffersUsers::model()->search($_filterPeriod, $_filterDateFrom, $_filterDateTo, $_filterStatus, $_filterSearch, true);

			$this->render('requests', array(
					'_filterPeriod'   => $_filterPeriod,
					'_filterDateFrom' => $_filterDateFrom,
					'_filterDateTo'   => $_filterDateTo,
					'_filterSearch'   => $_filterSearch,
					'_filterStatus'   => $_filterStatus,
					'requests'        => $_requests,
			));
				
		} elseif(Yii::app()->user->isGuest){
			$this->redirect('/login');
		} else {
			throw new CHttpException(400, 'You do not have permissions to access this page.');
		}
	}
	
	public function actionEditRequestForm( $id )
	{
		if($id){
			$model = OffersUsers::model()->findByPk($id);
			if($model){
				$data = array('model' => $model);
				$this->renderPartial('_editRequestForm', $data);
			} else {
				throw new CHttpException(404, 'Mdel with specified id not found');
			}
		} else {
			throw new CHttpException(404, 'Undefined model id');
		}
		
		Yii::app()->end();
	}
	
	/**
	*
	**/
	public function actionEditRequest($id)
	{
		if($id){
			$model = OffersUsers::model()->findByPk($id);
			if($model){
				if (isset($_POST[get_class($model)])) {
					$model->attributes = $_POST[get_class($model)];
					$transaction = $model->getDbConnection()->beginTransaction();
					try {
						if ($model->save()) {
							$transaction->commit();
							echo json_encode(array( 'success' => true, 'id' => $model->id));
							Yii::app()->end();
						} else {
							echo CHtml::errorSummary($model);
							$transaction->rollback();
						}
					} catch (Exception $e) {
						$transaction->rollback();
						Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
					}
				}
			} else {
				
			}
		} else {
			
		}
		
		echo json_encode(array('success' => false));
		Yii::app()->end();
	}
	
	/**
	*	TODO: Страница уведомлений для пользователя
	**/
	public function actionNotifications()
	{
		
	}
	
	/**
	*
	**/
	public function actionReadNotification( $id )
	{
		$notification = OffersUsersNotifications::model()->findByPk($id);
		if($notification && $notification->user_id == Yii::app()->user->id){
			$notification->status = OffersUsersNotifications::STATUS_OLD;
			$notification->save(false);
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
		Yii::app()->end();
	}
	
	/**
	*	Выводит форму создания/редактирования оффера
	**/
	public function actionEditForm( $id = null )
	{
		$_formData = array();
		
		$_campaign_id = Yii::app()->request->getParam('campaign_id');
		if($_campaign_id){
				
			$_formData['campaign_id'] = $_campaign_id;
			$_formData['countries'] = Countries::model()->with('cities')->findAll();
			
			if($id){
				$_formData['actions'] = Offers::model()->getAvailableCampaignActions(Yii::app()->request->getParam('campaign_id'),$id);
			} else {
				$_formData['actions'] = Offers::model()->getAvailableCampaignActions(Yii::app()->request->getParam('campaign_id'));
			}
			
				
			if($id){
				$model = $this->loadModel($id);
				$_offerUsersFilter = OffersUsersFilter::model()->findByOfferId($id, OffersUsersFilter::FILTER_TYPE_ALLOWED);
			} else {
				$model = new Offers('create');
				$_offerUsersFilter = array();
			}
				
			$_formData['model'] = $model;
			
			//Вебмастера, которым разрешен показ оффера
			$_formData['offerWMFilterUsers'] = $_offerUsersFilter;
			
				
				
			$this->disableClientScripts();
			$this->renderPartial('_form', $_formData, false, true);
				
		} else {
			throw new CHttpException(400, 'Campaign ID is missing');
			Yii::app()->end();
		}
	}
	
	/**
	*	Изменение флага активности оффера
	**/
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
     *	Изменение статуса отклика на оффер, поступившего от вебмастера
     **/
    public function actionChangeStatus( $id )
    {
    	$OfferUser = OffersUsers::model()->findByPk($id);
    	if($OfferUser){
    			
    		$_status = Yii::app()->request->getParam('status', null);
    		if(null !== $_status){
    			try{
    				$transaction = $OfferUser->getDbConnection()->beginTransaction();
    				
    				if( $OfferUser -> setStatus($_status, true, true) ){
    					$transaction->commit();
    					echo json_encode(array( 'success' => true, 'id' => $OfferUser->id, 'count_new' => OffersUsers::model()->getCountNew()));
    					Yii::app()->end();
    				} else {
    					echo CHtml::errorSummary($OfferUser);
    					$transaction->rollback();
    				}
    			} catch( Exception $e ){
    				Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
    				$transaction->rollback();
    			}
    		} else {
    
    		}
    	} else {
    		throw new CHttpException(404, 'User request not found');
    	}
    	echo json_encode(array('success' => false));
    	Yii::app()->end();
    }
    
	/**
	*	Обработка загруженного изображения
	**/
	public function actionImageUpload()
	{
		//Recieving the file or url
		if($file = CUploadedFile::getInstanceByName('file')) {
            $_file = $this->_getTmpUploadedImage($file);
        } elseif(isset($_REQUEST['url'])){
            $_file = $this->_getTmpImageByUrl($_REQUEST['url']);
        }
        
        //File recieved
        if(!empty($_file) && empty($_file['error'])){
            $_file = $this->_storeTmpImage($_file);

            echo json_encode(array(
                'file' => array_diff_key($_file, array('tmpName' => ''))
            ));
            
        }
        Yii::app()->end();
	}
	
	/**
	 *	Сохранение данных оффера из формы создания/редактирования
	 **/
	public function actionSave( $id = null )
	{
		if($id){
			$model = $this->loadModel($id);
			$model->setScenario('edit');
		} else {
			$model = new Offers('create');
		}
	
		if (isset($_POST[get_class($model)])) {
			
			$model->attributes = $_POST[get_class($model)];
	
			//Processing images
			if(!empty($_POST[get_class($model)]['imageIds'])){
				if(!empty($_POST[get_class($model)]['imageIds']['upload'])){
					$model->setImageIdsNew($_POST[get_class($model)]['imageIds']['upload']);
				}
				if(!empty($_POST[get_class($model)]['imageIds']['delete'])){
					$model->setImageIdsDelete($_POST[get_class($model)]['imageIds']['delete']);
				}
			}
			
			//Processing GEO
			if(!empty($_POST[get_class($model)]['countriesIds'])){
				$model->setCountriesIds(explode(',',$_POST[get_class($model)]['countriesIds']));
			} else {
				$model->setCountriesIds(array());
			}
			if(!empty($_POST[get_class($model)]['citiesIds'])){
				$model->setCitiesIds(explode(',',$_POST[get_class($model)]['citiesIds']));
			} else {
				$model->setCitiesIds(array());
			}
			
			//Processing wm_filter
			if(!empty($_POST['Offers_wm_filter'])){
				$model->setWMFilterRules($_POST['Offers_wm_filter']);
			} else {
				$model->setWMFilterRules();
			}
			
			
			
			//begin
			$transaction = $model->getDbConnection()->beginTransaction();
			try {
				if ($model->save()) {
					$transaction->commit();
					echo json_encode(array( 'success' => true, 'id' => $model->id,
											'hasAvailableActions' => Offers::model()->hasAvailableCampaignActions($model->campaign_id)));
					Yii::app()->end();
				} else {
					echo json_encode(array( 'success' => false, 'id' => $model->id,
											'hasAvailableActions' => Offers::model()->hasAvailableCampaignActions($model->campaign_id)));
					
					$transaction->rollback();
					Yii::app()->end();
				}
			} catch (Exception $e) {
				$transaction->rollback();
				Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
			}
		}
		echo json_encode(array('success' => false));
		Yii::app()->end();
	}

	
	/**
	*	Удаление оффера
	**/
	public function actionDelete( $id )
	{
		$model = $this->loadModel($id);
		$model->is_deleted = 1;
		$transaction = $model->getDbConnection()->beginTransaction();
		try{
			$model->save(false);
			OffersUsers::model()->deleteByOfferId($model->id);
			$transaction->commit();
			echo json_encode(array('success' => true));
			Yii::app()->end();
		} catch(Exception $e) {
			echo json_encode(array('success' => false, 'error' => $e->getMessage()));
			$transaction->rollback();
		}
		
		Yii::app()->end();
	}

	
	/**
	 *	Главная страница кабинета вебмастера
	 **/
	public function actionList()
	{
		if(Yii::app()->user->role === Users::ROLE_WEBMASTER){
			
			$Offers = Offers::model()->getAvailableForUserId(Yii::app()->user->id);
			$reportSummary = null;
			$userNotifications = null;
			
			$this->render('list', array(
				'offers' => $Offers,
			));
		} else {
			throw new CHttpException(403, 'You don\'t have permissions to access this page');
		}
	}
	
	/**
	*	Страница оффера
	**/
	public function actionView( $id )
	{
		if(Yii::app()->user->role === Users::ROLE_WEBMASTER || Yii::app()->user->role === Users::ROLE_ADMIN){
			$Offer = $this->loadModel($id);
			if($Offer){
				if($Offer->isAllowedForUser(Yii::app()->user->id)){
					$offerUser = OffersUsers::model()->findOfferRequestByUserId($Offer->id, Yii::app()->user->id);
					$this->render('view', array(
							'offer' => $Offer, 'offerUser' => $offerUser
					));
				} else {
					throw new CHttpException(403, 'You don\'t have permissions to access this page');
				}
			} else {
				throw new CHttpException(404, 'Requested page not found');
			}
		} else {
			throw new CHttpException(403, 'You don\'t have permissions to access this page');
		}
	}
	
	/**
	*	Отклик вебмастера на оффер
	**/
	public function actionJoin( $id )
	{
		try{
			if(Yii::app()->user->role === Users::ROLE_WEBMASTER){
				$Offer = $this->loadModel($id);
				if($Offer){
					if(!$Offer->isUserJoined(Yii::app()->user->id)){
						$_description = Yii::app()->request->getParam('description', '');
						$OfferUser = $Offer->joinUser(Yii::app()->user->id, $_description);
						if($OfferUser->hasErrors()){
							echo json_encode(array('success' => false, 'errors' => CHtml::errorSummary($OfferUser)));
						} else {
							echo json_encode(array('success' => true));
						}
						Yii::app()->end();
					} else {
						throw new CHttpException(500, 'Already joined');
					}
				} else {
					throw new CHttpException(404, 'Requested page not found');
				}
			} else {
				throw new CHttpException(403, 'You don\'t have permissions to access this page');
			}
		} catch(Exception $e) {
			echo json_encode(array('success' => false));
			Yii::app()->end();
		}
	}

	/**
	*	Возвращает загруженный файл
	**/
	private function _getTmpUploadedImage(CUploadedFile $file)
	{
		$result = array(
				'name' => $file->getName(),
				'size' => $file->getSize(),
				'tmpName' => $file->getTempName(),
				//'data' => file_get_contents($file->getTempName())
		);
		if($file->hasError){
			$result['error'] = $file->getError();
		}
		return $result;
	}
	
	/**
	*	Загружает файл по URL и возвращает его
	**/
	private function _getTmpImageByUrl($url)
	{
		$file = array(
				'name' => $url,
		);
		$urlValid = new CUrlValidator();
		if ($urlValid->validateValue($_REQUEST['url'])) {
			$inputFileName = tempnam(sys_get_temp_dir(), 'URL');
			@file_put_contents($inputFileName, fopen($_REQUEST['url'], 'r'));
			$file['size'] = @filesize($inputFileName);
			if($file['size'] < 1){
				$file['error'] = 'Не удалось скачать URL';
			}else{
				$file['tmpName'] = $inputFileName;
				//$file['data'] = file_get_contents($inputFileName);
			}
		}else{
			$file['error'] = 'Неправильный URL';
		}
		return $file;
	}
	
	/**
	*	Storing uploaded file to temporary storage
	**/
	private function _storeTmpImage( $file )
	{
		try{
			/** @var Image $img */
			$img = Yii::app()->image->load($file['tmpName']);
			$outputFileName = CFile::createUniqueFileName(Yii::app()->params->docTmpPath, '.' . $img->image['ext']);
			
			$file['id']  = $outputFileName;
			$file['width'] = $img->image['width'];
			$file['height'] = $img->image['height'];
			$file['type'] = $img->image['type'];
			$file['ext'] = $img->image['ext'];
			$file['mime'] = $img->image['mime'];
			
			$img->save(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $outputFileName);
			$file['url'] = Yii::app()->params->docTmpUrl . '/' . $outputFileName;
			
		}catch (CException $e){
			$file['error'] = $e->getMessage();
		}
		@unlink($file['tmpName']);
		return $file;
	}

    public function actionShortLink($id)
    {
        $model = OffersUsers::model()->findByPk($id);
        if($model === null){
            throw new CHttpException(404, 'The requested page does not exist.');
        }
        $link = ShortLink::createLink(
            ShortLink::TARGET_TYPE_OFFER_USER,
            $model->id,
            $model->getUrl(),
            $model->offer->date_end
        );

        $this->renderJsonAndExit(array('success' => true, 'url' => $link->getUrl()));
    }

	//==========================================================================
	
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

    public function actionStats()
    {
        list($period, $dateFrom, $dateTo) = Report::getPeriod();

        $actionsLog = ActionsLog::model()->getForWebmaster(
            Yii::app()->user->id,
            $period != 'all',
            $dateFrom,
            $dateTo,
            Yii::app()->request->getParam('status', null)
        );

        $this->render('transactions', array(
            'period' => $period,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'reportActionsLog' => new CReportDataProvider($actionsLog),
        ));
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
						'actions' => array('save', 'delete', 'wmsearch', 'changeStatus', 'editForm', 'editRequestForm', 'editRequest', 'view', 'join', 'imageupload', 'changeactivity', 'requests', 'changeActionLogStatus'),
						'roles' => array(Users::ROLE_ADMIN),
				),
				array(
						'allow',
						'actions' => array('list', 'join', 'view', 'notifications','readNotification', 'stats', 'shortLink'),
						'roles' => array(Users::ROLE_WEBMASTER),
				),
				array('deny',
						'users' => array('*'),
				),
		);
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @param CActiveRecord $ar
	 * @return OffersUsers the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id, CActiveRecord $ar = null)
	{
		$ar  = $ar ?: Offers::model();
		$model = $ar->findByPk($id);
	
		if ($model === null) {
			throw new CHttpException(404, 'The requested page does not exist.');
		}
	
		return $model;
	}
}
