<?php

/**
 * This is the model class for table "billing_income".
 *
 * The followings are the available columns in table 'billing_income':
 * @property string $id
 * @property string $issuing_date
 * @property double $sum
 * @property integer $is_paid
 * @property string $paid_date
 * @property string $comment
 * @property string $number
 * @property string $source_type
 * @property string $source_id
 *
 * The followings are the available model relations:
 * @property Platforms $platform
 */
class BillingIncome extends CActiveRecord
{
    const SOURCE_TYPE_PLATFORM = 'platform';
    const SOURCE_TYPE_WEBMASTER = 'webmaster';

    public $server;
    public $source_name;
    public $platform_user_id;
    public $dateFrom;
    public $dateTo;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return BillingIncome the static model class
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
        return 'billing_income';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('issuing_date, source_type, source_id, number, sum', 'required'),
//          array('number', 'unique'),
            array('is_paid', 'numerical', 'integerOnly'=>true),
            array('sum', 'numerical', 'min' => '0.01'),
            array('sum', 'greaterThanDebit', 'on' => 'withdrawal'),
            array('source_type', 'in', 'range' => array_keys($this->getAvailableSourceTypes())),
            array('source_id', 'length', 'max'=>10),
            array('paid_date, comment', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, issuing_date, sum, is_paid, paid_date, comment, source_id, platform_user_id', 'safe', 'on'=>'search'),
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
            'platform' => array(self::BELONGS_TO, 'Platforms', 'source_id'),
            'offer' => array(self::BELONGS_TO, 'OffersUsers', 'source_id'),
        );
    }

    public static function getSumm($filter = null){
        $sql = "SELECT SUM(sum) FROM billing_income" . ((!is_null($filter))  ? ' WHERE is_paid = ' . (int)$filter : '');
        return  Yii::app()->db->createCommand($sql)->queryScalar();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => '№ Счета',
            'number' => '№ Счета',
            'issuing_date' => 'Дата выставления',
            'sum' => 'Сумма',
            'is_paid' => 'Оплачен',
            'paid_date' => 'Дата оплаты',
            'comment' => 'Коментарий',
            'source_id' => 'Источник',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->select = 't.*, COALESCE(p.server, CONCAT(u.login, " (",u.email,")")) AS source_name';
        $criteria->compare('t.source_type', $this->source_type);
        $criteria->compare('t.source_id', $this->source_id);
        $criteria->compare('p.user_id',$this->platform_user_id);
        $criteria->compare('t.is_paid',$this->is_paid);
        
        $criteria->join = "LEFT JOIN `platforms` p ON t.source_type='".self::SOURCE_TYPE_PLATFORM."' AND p.id=t.source_id "
            ."LEFT JOIN `users` u ON t.source_type='".self::SOURCE_TYPE_WEBMASTER."' AND u.id=t.source_id ";

        if(isset($this->dateFrom) && isset($this->dateTo)){
            $criteria->addBetweenCondition('t.issuing_date', $this->dateFrom, $this->dateTo);
        }

        if(isset($this->id)) {
            $searchCriteria = new CDbCriteria();
            $searchCriteria->compare('t.id', $this->id, true, 'OR');
            $searchCriteria->compare('t.number', $this->id, true, 'OR');
//            $searchCriteria->compare('t.issuing_date', $this->id, true, 'OR');
            $searchCriteria->compare('t.sum', $this->id, true, 'OR');
            $searchCriteria->compare('paid_date', $this->paid_date, true, 'OR');
            $searchCriteria->compare('t.comment', $this->id, true, 'OR');
            $searchCriteria->compare('u.login', $this->id, true, 'OR');
            $searchCriteria->compare('u.email', $this->id, true, 'OR');
            $searchCriteria->compare('p.server', $this->id, true, 'OR');
            
            $criteria->mergeWith($searchCriteria);
        }

        
        
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    public function searchGrouped()
    {
        $criteria=new CDbCriteria;

        $criteria->select = 'number, issuing_date, sum(sum) as sum,is_paid,paid_date,comment, group_concat(p.server separator \', \') as server';
        $criteria->compare('p.user_id',$this->platform_user_id);
        $criteria->compare('number',$this->number);
        $criteria->compare('is_paid',$this->is_paid);
        $criteria->join = "LEFT JOIN `platforms` p ON t.source_type='".self::SOURCE_TYPE_PLATFORM."' AND p.id=t.source_id";
        $criteria->group='t.number';
        $criteria->having='server LIKE :search OR comment LIKE :search OR sum LIKE :search OR number LIKE :search OR issuing_date LIKE :search OR paid_date LIKE :search';
        $criteria->params[':search'] = '%'.$this->id.'%';

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    /**
     * @param array $sources
     * @param string $comment
     * @param BillingIncome[] $models
     * @return bool
     */
    public function createWithdrawalRequest($sources, $comment, &$models = null)
    {
        if($models === null){
            $models = array();
        }
        if(isset($sources['source_type'])){
            $sources = array($sources);
        }
        $valid = true;
        $total = 0;
        foreach($sources as $source){
            $model = new BillingIncome();
            $model->source_type = $source['source_type'];
            $model->source_id = $source['source_id'];
            $model->sum = $source['sum'];
            $model->comment = $comment;
            $model->issuing_date = date('Y-m-d');
            $model->number = 'temp';
            $model->setScenario('withdrawal');
            $valid = $valid && $model->validate();
            $models[] = $model;
            $total += $model->sum;
        }

        if($total < Yii::app()->params->PlatformBillingMinimalWithdrawal){
            $models[0]->addError('sum', Yii::t('validation', "Мминимальная выводимая сумма: {min}", array('{min}' => Yii::app()->params->PlatformBillingMinimalWithdrawal)));
            $valid = false;
        }

        if($valid){
            $valid = $this->batchSave($models);
            if($valid){
                SMail::sendMail(
                    Yii::app()->params->billingEmail,
                    'Запрос на вывод средств',
                    'WithdrawalRequest',
                    array('models' => $models)
                );
            }
        }

        return $valid;
    }

    /**
     * @param BillingIncome[] $models
     * @return boolean
     */
    public function batchSave($models)
    {
        Yii::app()->db->createCommand('LOCK TABLE `'.$this->tableName().'` WRITE;')->execute();
        $number = Yii::app()->db->createCommand('SELECT MAX(number) FROM `'.$this->tableName().'`;')->queryScalar();
        $transaction=Yii::app()->db->beginTransaction();
        $number++;
        $saved = true;
        foreach($models as $model){
            $model->number = $number;
            $saved = $saved && $model->save();
        }
        if($saved){
            $transaction->commit();
        }else{
            $transaction->rollback();
        }
        Yii::app()->db->createCommand('UNLOCK TABLES;')->execute();
        return $saved;
    }

    public function greaterThanDebit()
    {
        if($this->source_type == self::SOURCE_TYPE_WEBMASTER){
            $debit = $this->getDebitByUser(Users::model()->findByPk($this->source_id));
            if($this->sum > $debit){
                $this->addError('sum', Yii::t('validation', "Максимальная выводимая сумма: {debit}", array('{debit}' => $debit)));
            }
        }else {
            if ($this->sum > $this->platform->billing_debit) {
                $this->addError('sum', Yii::t('validation', "Максимальная выводимая сумма для платформы {platform}: {debit}", array('{platform}' => $this->platform->server, '{debit}' => $this->platform->billing_debit)));
            }
        }
    }

    public function getPaidByUser($user_id, $is_paid = null)
    {
        $cmd = Yii::app()->db
            ->createCommand()
            ->select('SUM(`sum`) AS `sum`')
            ->from($this->tableName() . ' t')
            ->leftJoin(Platforms::model()->tableName().' p',
                "`source_type`='".BillingIncome::SOURCE_TYPE_PLATFORM."' AND t.source_id = p.id")
            ->where(
                "p.user_id = :user_id OR (t.source_type= :type_webmaster AND t.source_id = :user_id)",
                array(':type_webmaster' => self::SOURCE_TYPE_WEBMASTER, ':user_id' => $user_id)
            );
        if(!is_null($is_paid)){
            $cmd->andWhere('is_paid = :is_paid', array(':is_paid' => $is_paid));
        }
        return (float) $cmd->queryScalar();
    }

    public function getProfitByUser($user)
    {
        if($user->role == Users::ROLE_PLATFORM) {
            return ReportDailyByPlatform::model()->getPriceSumByAttributes(array('user_id' => $user->id));
        }elseif($user->role == Users::ROLE_WEBMASTER){
            return ReportTotalByOfferUser::model()->getRewardSum($user->id);
        }else{
            throw new CException('Unknown user billing type');
        }
    }

    /**
     * @param Users $user
     * @return float
     */
    public function getDebitByUser($user)
    {
        return $this->getProfitByUser($user) - $this->getPaidByUser($user->id);
    }

    public function getPaidByPlatform($platform_id, $is_paid = null)
    {
        $cmd = Yii::app()->db
            ->createCommand()
            ->select('SUM(`sum`) AS `sum`')
            ->from($this->tableName())
            ->where('source_type = :source_type AND source_id = :source_id', array(
                ':source_type' => self::SOURCE_TYPE_PLATFORM,
                ':source_id' => $platform_id
            ));
        if($is_paid !== null){
            $cmd->andWhere('is_paid = :is_paid', array(':is_paid' => $is_paid));
        }
        return (float) $cmd->queryScalar();
    }

    public function getProfitByPlatform($platform_id)
    {
        return ReportDailyByPlatform::model()->getPriceSumByAttributes(array('platform_id' => $platform_id));
    }

    public static function getAvailableSourceTypes()
    {
        return array(
            self::SOURCE_TYPE_PLATFORM => 'Площадка',
            self::SOURCE_TYPE_WEBMASTER => 'Вебмастер',
        );
    }
}