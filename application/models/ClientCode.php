<?php

/**
 * This is the model class for table "client_code".
 *
 * The followings are the available columns in table 'client_code':
 * @property string $platform_id
 * @property string $file_name
 * @property string $url
 * @property string $path
 * @property string $control_url
 * @property string $update_date
 * @property string $error
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 *
 * @method ClientCode[] findAll()
 */
class ClientCode extends CActiveRecord
{
    protected function beforeValidate()
    {
        $this->url = IDN::encodeURL($this->url);
        $this->control_url = IDN::encodeURL($this->control_url);

        return parent::beforeValidate();
    }

    public function isValid()
    {
        return !$this->getIsNewRecord() && !$this->hasErrors() && $this->error == "";
    }

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ClientCode the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'client_code';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('platform_id, file_name, url, path, control_url', 'required'),
			array('platform_id', 'length', 'max'=>10),
			array('file_name', 'length', 'max'=>45),
			array('url, control_url', 'length', 'max'=>2048),
			array('path', 'length', 'max'=>512),
            array('url, control_url', 'url', 'defaultScheme' => 'http://', 'validateIDN' => false),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('platform_id, file_name, url, path, control_url', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'platform' => array(self::BELONGS_TO, 'Platforms', 'platform_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'platform_id' => 'Platform',
			'file_name' => 'File Name',
			'url' => 'Url',
			'path' => 'Path',
			'control_url' => 'Control Url',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('platform_id',$this->platform_id,true);
		$criteria->compare('file_name',$this->file_name,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('path',$this->path,true);
		$criteria->compare('control_url',$this->control_url,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function init()
    {
        $this->file_name = uniqid() . '.js';
    }

    private $controlCodes = array(
        '1' => 'Несоответствие секретного ключа',
        '2' => 'Ошибка открытия файла',
        '3' => 'Ошибка записи файла',
        '4' => 'Другая ошибка'
    );

    private function controlReplyString($reply){
        $code = substr($reply, 0, 1);
        $reply = substr($reply, 1);
        return $this->controlCodes[$code] . (empty($reply) ? '.' : ':' . $reply);
    }

    public function validateDeployment()
    {
        $c = new Connection(array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_MAXREDIRS => 0,
        ));
        $controlRequest = new ConnectionRequest($this->control_url);
        $controlRequest->isPost = true;
        $controlRequest->params = array(
            'apiKey' => $this->getApiKey(),
            'js' => $this->_getJS(),
        );
        $c->addRequest($controlRequest);
        $requests = $c->run();
        $reply = $requests[0]->reply;
        if($reply->info['http_code'] != 200){
            $this->addError('control_url', 'Ошибка доступа к скрипту управления: '.$reply->info['http_code']);
        }elseif($reply->content != "0"){
            $this->addError('control_url', 'Ошибка выполнения скрипта управления: '
                . $this->controlReplyString($reply->content)
            );
        }
        $c->reset()->addRequest(new ConnectionRequest($this->url));
        $requests = $c->run();
        $reply = $requests[0]->reply;
        if($reply->info['http_code'] != 200 || $reply->content !== $this->_getJS()){
            $this->addError('url', 'Javascript не обновлен');
        }
        if($this->hasErrors()){
            return false;
        }
        return true;
    }

    public function getApiKey()
    {
        return Crypt::encryptUrlComponent($this->platform_id, Yii::app()->params['clientCodeSecret']);
    }

    private function _getJS()
    {
        return self::getSimpleBlock(self::_getBlockVar($this->platform_id), false);
    }

    private static function _getBlockVar($platformId)
    {
        return str_replace('-', '', 'B' . Crypt::encryptUrlComponent($platformId));
    }

    public static function getSimpleBlock($blockVar = null, $jsTag = true)
    {
        if(null === $blockVar){
            $blockVar = '{id: %BLOCK_ID%, count: %COUNT%%USE_TITLE%}';
        }
        return
            ($jsTag ? '<script type="text/javascript">' : '')
            . str_replace(
                '%BLOCK_VAR%',
                $blockVar,
                file_get_contents(Yii::getPathOfAlias('application.data.clientCode') . '/clientCode.js')
            )
            . ($jsTag ? '</script>' : '');
    }

    public function getAdvancedBlock()
    {
        return
            str_replace(
                array('%BLOCK_VAR%', '%CLIENT_CODE_URL%'),
                array(
                    self::_getBlockVar($this->platform_id),
                    $this->url,
                ),
                file_get_contents(Yii::getPathOfAlias('application.data.clientCode') . '/clientBlock.js')
            );
    }

}