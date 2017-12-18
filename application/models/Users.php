<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $email
 * @property string $login
 * @property string $password
 * @property string $logo
 * @property string $role
 * @property integer $is_deleted
 * @property string $billing_details_type
 * @property string $billing_details_text
 * @property integer $is_auto_withdrawal
 * @property string $created_date
 * @property string $lastlogin_date
 * @property string $login_token
 * @property string $passwd_token
 * @property string $contact_details
 * @property integer $status
 * @property string $phone
 * @property string $skype
 *
 * The followings are the available model relations:
 * @property Campaigns[] $campaigns
 * @property Platforms[] $platforms
 *
 * Behaviors
 * @property DirtyObjectBehavior $dirty
 *
 * @method Users findByPk()
 * @method Users find()
 */
class Users extends CActiveRecord
{
    const ROLE_ADMIN     = 'admin';
    const ROLE_USER      = 'user';
    const ROLE_GUEST     = 'guest';
    const ROLE_PLATFORM  = 'platform';
    const ROLE_WEBMASTER = 'webmaster';

    const STATUS_MODERATION = 0;
    const STATUS_ACCEPTED = 1;

    const DEFAULT_LOGO = 'default.jpg';

    public $repeat_password;
    public $initialPassword;
    public $campaigns;

    public $acceptRules;

    private $campaigns_cont;
    private $platforms_count;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Users the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'users';
    }

    /**
     * @return array Доступные для выобра поли пользователей
     */
    public static function getAvailableRoles()
    {
        $roles = array(
            self::ROLE_USER      => 'Клиент',
            self::ROLE_ADMIN     => 'Администратор',
            self::ROLE_PLATFORM  => 'Площадка',
            self::ROLE_WEBMASTER => 'Вебмастер',
        );
        if(Yii::app()->user->isGuest) {
            //signin
            return array(self::ROLE_PLATFORM => $roles[self::ROLE_PLATFORM]);
        }elseif(Yii::app()->user->role !== self::ROLE_ADMIN){
            return array(Yii::app()->user->role => $roles[Yii::app()->user->role]);
        }else{
            return $roles;
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $width = Yii::app()->params->userImageWidth;
        $height = Yii::app()->params->userImageHeight;

        $logoErrorMsg = 'Размер изображния больше допустимого (' . $width . ' x ' . $height . ')';

        return array(
            array('email, login', 'required'),
            array('email', 'length', 'max' => 128),
            array('email', 'email'),
            array('email', 'unique'),
            array('password, repeat_password', 'required', 'on' => 'insert, signin'),
            array('repeat_password, password', 'length', 'min' => 6, 'max' => 40),
            array('repeat_password', 'compare', 'compareAttribute' => 'password'),
            array('login, billing_details_type', 'length', 'max' => 45),
            array(
                'logo',
                'ext.yiiext.validators.EImageValidator',
                'allowEmpty' => true,
                'mime' => array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'),
                'maxWidth' => $width,
                'maxHeight' => $height,
                'tooLargeWidth' => $logoErrorMsg,
                'tooLargeHeight' => $logoErrorMsg,
                'on' => array('create', 'edit')
            ),
            array('logo', 'default', 'value' => self::DEFAULT_LOGO),
            array('role', 'in', 'range' => array_keys(self::getAvailableRoles())),
            array('status', 'in', 'range' => array_keys(self::getAvailableStatuses())),
            array('billing_details_text', 'safe'),
            array('is_auto_withdrawal', 'numerical', 'integerOnly' => true),
            array('contact_details', 'length', 'max' => 1024),
            array(
                'created_date','default',
                'value'=>new CDbExpression('NOW()'),
                'setOnEmpty'=>true,'on'=>'create'
            ),
            array('phone', 'match', 'pattern' => '/7 \(\d{3}\) \d{3}-\d{4}/', 'allowEmpty' => true, 'message' => 'Неверный формат'),
            array('skype', 'length', 'max' => 128),
            array('id, email, login, password, logo, role, campaigns, status', 'safe', 'on' => 'search'),
            // singin
            array('acceptRules', 'compare', 'compareValue' => '1', 'on' => 'signin', 'message' => 'Вы должны быть согласны с правилами.'),
            array('role, status', 'unsafe', 'on' => 'signin')
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'campaigns' => array(self::HAS_MANY, 'Campaigns', 'client_id'),
            'platforms' => array(self::HAS_MANY, 'Platforms', 'user_id'),
        	'offers' => array(self::HAS_MANY, 'OffersUsers', 'user_id'),
        	'offersNotifications' => array(self::HAS_MANY, 'OffersUsersNotifications', 'user_id'),
        );
    }

    public function behaviors()
    {
        return array(
            'dirty' => array(
                'class' => 'application.components.behaviors.DirtyObjectBehavior'
            )
        );
    }

    public function afterFind()
    {
        //reset the password to null because we don't want the hash to be shown.
        $this->initialPassword = $this->password;
        $this->password = null;

        parent::afterFind();
    }

    protected function beforeSave()
    {
        if (empty($this->password) && empty($this->repeat_password) && !empty($this->initialPassword)) {
            $this->password = $this->repeat_password = $this->initialPassword;
        } else {
            $this->password = md5($this->password);
            $this->refreshPasswordToken();
        }

        $this->role = ($this->role) ?: self::ROLE_GUEST;

        return parent::beforeSave();
    }

    protected function afterSave()
    {
        if($this->dirty->getCleanAttribute('status') == self::STATUS_MODERATION && $this->status == self::STATUS_ACCEPTED){
            SMail::sendMail($this->email, 'Подтверждение аккаунта ttarget.ru', 'SignInAccepted', array('user' => $this));
        }

        if ($this->is_deleted) {
            // Cоздаем задание на удаление из БД
            Yii::app()->resque->createJob('app', 'UserDelFromDbJob', array('user_id' => $this->id));
        }

        $this->deleteOldLogo();
        parent::afterSave();
    }

    /**
     * При удалении пользователя, удаляем его лого
     */
    protected function afterDelete()
    {
        if ($this->logo != self::DEFAULT_LOGO) {
            $filePath = Yii::app()->params['logoBasePath'] . DIRECTORY_SEPARATOR . $this->logo;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        parent::afterDelete();
    }

    /**
     * Удаляет старое лого
     */
    private function deleteOldLogo()
    {
        if (!$this->getIsNewRecord() &&
            $this->dirty->isAttributeChanged('logo') &&
            $this->dirty->getCleanAttribute('logo') != self::DEFAULT_LOGO
        ) {
            $oldFilePath = Yii::app()->params['logoBasePath'] . DIRECTORY_SEPARATOR . $this->dirty->getCleanAttribute('logo');
            if (is_file($oldFilePath)) {
                unlink($oldFilePath);
            }
        }
    }

    public function validatePassword($password)
    {
        return md5($password) === $this->initialPassword;
    }

    private function refreshPasswordToken()
    {
        $this->passwd_token = sha1(uniqid(mt_rand(), true));
        $this->updateByPk($this->id, array('passwd_token' => $this->passwd_token));
        if(Yii::app()->user->id == $this->id) {
            Yii::app()->user->setState(UserIdentity::PASSWD_TOKEN, $this->passwd_token);
            Yii::app()->user->cookieUpdateStates();
        }
    }
    
    /**
     * @return bool Сохраняет лого
     */
    public function saveLogo()
    {
        if (!($this->logo instanceof CUploadedFile))
        {
            return true;
        }

        $newFileName = 'c_' . $this->id . '_' . $this->logo->name;

        $filePath   = Yii::app()->params['logoBasePath'] . DIRECTORY_SEPARATOR . $newFileName;
        $uploaded   = $this->logo->saveAs($filePath);
        $this->logo = $newFileName;

        if ($uploaded)
        {
            $command    = $this->getDbConnection()->createCommand();
            $updated    = $command->update(
                $this->tableName(),
                array('logo' => $this->logo),
                'id = :id',
                array('id' => $this->id)
            );
        }

        return $uploaded && $updated;
    }

    /**
     * @return Users Именованная группа для выборки не удаленных тизеров
     */
    public function notDeleted()
    {
        $alias = $this->getTableAlias(false, false);
        $this->getDbCriteria()->mergeWith(array(
            'condition' => 'is_deleted = 0',
        ));

        return $this;
    }

    /**
     * @return Users Именованная группа для выборки позьзователей для вывода списком
     */
    public function printable()
    {
        $alias = $this->getTableAlias(false,false);
        $this->notDeleted()->getDbCriteria()->mergeWith(array(
            'order' => 'login,email ASC'
        ));

        return $this;
    }

    /**
     * @return Users Именованная группа для выборки вебмастеров
     */
    public function webmaster()
    {
        $alias = $this->getTableAlias(false,false);
        $this->notDeleted()->getDbCriteria()->mergeWith(array(
            'condition' => $alias . '.role = :role',
            'params' => array(':role' => self::ROLE_WEBMASTER),
        ));

        return $this;
    }

    public function getLoginEmail()
    {
        return $this->login . (empty($this->email) ? '' : ' ('.$this->email.')');
    }

    /**
     * Удаляет кампании пользователя
     */
    public function deleteCampaigns()
    {
        foreach ($this->getRelated('campaigns') as $campaign) {
            $campaign->is_deleted = 1;
            $campaign->save(false, array('is_deleted'));
        }
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'email' => 'Email',
            'login' => 'ФИО',
            'password' => 'Пароль',
            'repeat_password' => 'Повторите пароль',
            'logo' => 'Логотип',
            'role' => 'Роль',
            'billing_details_type' => 'Реквизиты',
            'is_auto_withdrawal' => 'Автоматический запрос на выплаты',
            'contact_details' => 'Контактная информация',
            'status' => 'Статус',
            'phone' => 'Телефон'
        );
    }

    public function searchWithActive($user = false, $campaignsCostType = '')
    {
//        var_dump($campaignsCostType); exit();
        $criteria = new CDbCriteria;
        $criteria->select = 't.*';
        $criteria->compare('id', $this->id, true);

        $criteria->with = array('campaigns' => array(
            'together' => true,
            'select' => false,
            'with' => array(
                'news' => array('together' => true, 'select' => false),
                'news.teasers' => array('together' => true, 'select' => false)
            )
        ));
        $criteria->group = 't.id';

        $criteria->addCondition('t.is_deleted = 0');
        $criteria->addCondition('t.role = "user"');
        $criteria->addCondition('campaigns.is_active = 1');
        $criteria->addCondition('campaigns.date_end >= CURDATE()');
        if ($campaignsCostType != '' && array_key_exists($campaignsCostType, Campaigns::model()->getAvailableCostTypes())){
            $criteria->compare('campaigns.cost_type', $campaignsCostType);
        }
        if (!empty($this->login)) {
            $searchCriteria = $this->getSearchCriteria();
            $searchCriteria->compare('campaigns.name', $this->login, true, 'OR');
            $criteria->mergeWith($searchCriteria);
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }


    public function searchByRoleAndEmail( $role, $emailMatch = null )
    {
    	$_criteria = new CDbCriteria;
    	$_criteria->compare('role', $role);
    	$_criteria->compare('is_deleted', 0);
    	$_criteria -> order = 'email ASC';
    	if($emailMatch){
    		$_criteria->compare('email', $emailMatch, true);
    	}
    	return $this->findAll($_criteria);
    }
    
    public function searchAll($campaignFilters = array(), $role = 'user')
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->select = 't.*';
        $criteria->with = array('campaigns' => array(
            'together' => true,
            'select' => false,
            'with' => array(
                'news' => array('together' => true, 'select' => false),
                'news.teasers' => array('together' => true, 'select' => false),
            	
            )
        ));
        $criteria->group = 't.id';

        $criteria->compare('t.id', $this->id, true);
        if(!empty($role)){
        	$criteria->addCondition('t.role = "'.$role.'"');
        }

        $criteria->compare('t.status', $this->status);

        $criteria->addCondition('t.is_deleted = 0');
        if (isset($campaignFilters['cost_type']) &&
            array_key_exists($campaignFilters['cost_type'], Campaigns::model()->getAvailableCostTypes())
        ){
            $criteria->compare('campaigns.cost_type', $campaignFilters['cost_type']);
        }
        if (isset($campaignFilters['is_active']) && $campaignFilters['is_active'] == 1){
            $criteria->addCondition('campaigns.is_active = 1');
            $criteria->addCondition('campaigns.date_end >= CURDATE()');
        }
        if (!empty($this->login)) {
            $criteria->mergeWith($this->getSearchCriteria());
        }

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * @return int Возвращает количество кампаний пользователя
     */
    public function campaignsCount()
    {
        if (!isset($this->campaigns_cont)) {
            $this->campaigns_cont = (int) Campaigns::model()->count('client_id = :id', array(':id' => $this->id));
        }

        return $this->campaigns_cont;
    }

    /**
     * @return int Возвращает количество площадок пользователя
     */
    public function platformsCount()
    {
        if (!isset($this->platforms_count)) {
            $this->platforms_count = (int) Platforms::model()->count('user_id = :id', array(':id' => $this->id));
        }

        return $this->platforms_count;
    }

    private function getSearchCriteria()
    {
        $searchCriteria = new CDbCriteria();
        $searchCriteria->compare('t.email', $this->login, true, 'OR');
        $searchCriteria->compare('t.login', $this->login, true, 'OR');
        $searchCriteria->compare('campaigns.id', $this->login, false, 'OR');
        $searchCriteria->compare('campaigns.name', $this->login, true, 'OR');
        $searchCriteria->compare('news.id', $this->login, false, 'OR');
        $searchCriteria->compare('teasers.id', $this->login, false, 'OR');
        return $searchCriteria;
    }

    public static function getAvailablePaymentTypes()
    {
        return array(
            'WEB-money' => 'WEB-money',
            'Яндекс.Деньги' => 'Яндекс.Деньги',
            'Банковские реквизиты' => 'Банковские реквизиты',
            'Другие' => 'Другие',
        );
    }

	public function getRoleName()
	{
		$roles = array(
				self::ROLE_USER      => 'Клиент',
				self::ROLE_ADMIN     => 'Администратор',
				self::ROLE_PLATFORM  => 'Площадка',
				self::ROLE_WEBMASTER => 'Вебмастер',
				self::ROLE_GUEST     => 'Гость'
		);
		return isset($roles[$this->role]) ? $roles[$this->role] : $roles[self::ROLE_GUEST];
	}

    public function getAvailableStatuses()
    {
        return array(
            self::STATUS_ACCEPTED => 'Подтвержденные',
            self::STATUS_MODERATION => 'Новые',
        );
    }
}