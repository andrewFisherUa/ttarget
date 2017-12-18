<?php

class TeasersController extends Controller
{
    private $_model;

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array(
                'allow',
                'actions' => array('create', 'update', 'returnForm', 'changeActivity', 'image'),
                'roles' => array('admin'),
            ),
            array(
                'allow',
                'actions' => array('delete'),
                'roles' => array('admin'),
            ),
            array(
                'deny',
                'users' => array('*'),
            ),
        );
    }

    /**
     * Обработка изображения в форме тизера
     */
    public function actionImage()
    {
        if($file = CUploadedFile::getInstanceByName('file')) {
            $file = $this->_uploadImage($file);
        }elseif(isset($_REQUEST['url'])){
            $file = $this->_downloadImageByUrl($_REQUEST['url']);
        }
        if(isset($file)){
            if(!isset($file['error'])) {
                $file = $this->_prepareImage($file);
            }

            echo json_encode(array(
                'file' => array_diff_key($file, array('tmpName' => ''))
            ));
            Yii::app()->end();
        }elseif(isset($_REQUEST['crop']) && isset($_REQUEST['fileName'])){
            try {
                /** @var Image $img */
                $img = Yii::app()->image->load(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . basename($_REQUEST['fileName']));
                $outputFileName = CFile::createUniqueFileName(Yii::app()->params->imageBasePath, '.'.$img->image['ext'], 't_');
                $img
                    ->resize((int)$_REQUEST['crop']['w'], (int)$_REQUEST['crop']['h'], Image::NONE)
                    ->crop(
                        Yii::app()->params->teaserImageWidth,
                        Yii::app()->params->teaserImageHeight,
                        (int)$_REQUEST['crop']['y'],
                        (int)$_REQUEST['crop']['x']
                    )
                    ->save(Yii::app()->params->imageBasePath . DIRECTORY_SEPARATOR . $outputFileName);
            }catch (CException $e){
                echo json_encode(array('error'=>$e->getMessage()) );
                Yii::app()->end();
            }
            echo json_encode(array('fileName'=> $outputFileName) );
            Yii::app()->end();
        }
    }

    public function actionCreate()
    {
        $this->save(new Teasers('create'));
    }

    public function actionUpdate($id)
    {
        $model = $this->loadModel($id);
        $model->setScenario('edit');

        $this->save($model);
    }

    public function actionReturnForm()
    {
        if (isset($_POST['update_id'])) {
            $model = $this->loadModel($_POST['update_id']);
            $model->setScenario('edit');
        } else {
            $model = new Teasers('create');
        }

        if (isset($_GET['n'])) {
            $model->news_id = $_GET['n'];
        }

        $this->disableClientScripts();
        $this->renderPartial('_form', array(
            'model' => $model,
            'platforms' => Platforms::model()->printable()->notDeleted()->active()->with('tags')->findAll('is_external = 0')
        ), false, true);
    }

    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        $model->is_deleted = 1;
        $model->save(false, array('is_deleted'));

        echo '[]';
        Yii::app()->end();
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
     * @param null $id
     * @return Teasers|null
     * @throws CHttpException
     */
    private function loadModel($id = null)
    {
        if ($this->_model === null) {
            $id = isset($_GET['id']) ? $_GET['id'] : $id;
            if ($id) {
                $this->_model = Teasers::model()->notDeleted()->with('tags', 'platforms')->findbyPk($id);
            }

            if ($this->_model === null) {
                throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
            }
        }
        return $this->_model;
    }

    /**
     * Создает/обновляет тизер
     *
     * @param Teasers $model
     */
    private function save(Teasers $model)
    {
        if (isset($_POST[get_class($model)])) {

            $model->attributes  = $_POST[get_class($model)];
            if(!isset($_POST[get_class($model)]['platformIds']) || !is_array($model->platformIds)){
                $model->platformIds = array();
            }
            if(!is_array($model->tagIds)){
                $model->tagIds = array();
            }


            if ($model->save()) {
                echo json_encode(array('success' => true));
                Yii::app()->end();
            }
        }

        echo json_encode(array('success' => false));
        Yii::app()->end();
    }

    private function _downloadImageByUrl($url)
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
            }
        }else{
            $file['error'] = 'Неправильный URL';
        }
        return $file;
    }

    private function _prepareImage($file){
        try{
            /** @var Image $img */
            $img = Yii::app()->image->load($file['tmpName']);
            $wp = $img->image['width'] / Yii::app()->params->teaserImageWidth;
            $hp = $img->image['height'] / Yii::app()->params->teaserImageHeight;
            if ($wp < 1 || $hp < 1) {
                $img->resize(
                    Yii::app()->params->teaserImageWidth,
                    Yii::app()->params->teaserImageHeight,
                    ($wp < $hp ? Image::WIDTH : Image::HEIGHT)
                );
            }
            $outputFileName = CFile::createUniqueFileName(Yii::app()->params->docTmpPath, '.' . $img->image['ext']);
            $img->save(Yii::app()->params->docTmpPath . DIRECTORY_SEPARATOR . $outputFileName);
            $file['url'] = Yii::app()->params->docTmpUrl . '/' . $outputFileName;
        }catch (CException $e){
            $file['error'] = $e->getMessage();
        }
        @unlink($file['tmpName']);
        return $file;
    }

    private function _uploadImage(CUploadedFile $file)
    {
        $result = array(
            'name' => $file->getName(),
            'size' => $file->getSize(),
            'tmpName' => $file->getTempName(),
        );
        if($file->hasError){
            $result['error'] = $file->getError();
        }
        return $result;
    }
}
