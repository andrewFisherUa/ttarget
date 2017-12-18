<?php

/**
 * This is the model class for table "billing_outgoing".
 *
 * The followings are the available columns in table 'billing_outgoing':
 * @property string $id
 * @property string $issuing_date
 * @property double $sum
 * @property integer $is_paid
 * @property string $paid_date
 * @property string $comment
 * @property string $client_id
 *
 * The followings are the available model relations:
 * @property Users $client
 */
class BillingOutgoing extends CActiveRecord
{
    public $login;
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return BillingOutgoing the static model class
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
        return 'billing_outgoing';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('issuing_date, client_id, number', 'required'),
            array('number', 'unique'),
            array('is_paid', 'numerical', 'integerOnly'=>true),
            array('sum', 'numerical'),
            array('client_id', 'length', 'max'=>10),
            array('paid_date, comment', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, issuing_date, sum, is_paid, paid_date, comment, client_id', 'safe', 'on'=>'search'),
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
            'client' => array(self::BELONGS_TO, 'Users', 'client_id'),
        );
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
            'client_id' => 'Клиент',
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

        $criteria->select = 't.*, u.login';
        $criteria->compare('is_paid',$this->is_paid);
        $criteria->compare('paid_date',$this->paid_date,true);
        $criteria->join = 'INNER JOIN users u ON u.id=t.client_id';

        if(isset($this->id)) {
            $searchCriteria = new CDbCriteria();
            $searchCriteria->compare('t.id',$this->id,true,'OR');
            $searchCriteria->compare('t.number',$this->id,true,'OR');
            $searchCriteria->compare('sum',$this->id, true,'OR');
            $searchCriteria->compare('comment',$this->id,true,'OR');
            $searchCriteria->compare('u.login',$this->id,true,'OR');
            $searchCriteria->compare('CONVERT(issuing_date USING utf8)',$this->id,true,'OR');
            $criteria->mergeWith($searchCriteria);
        }

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    public static function getSumm($filter = null){
        $sql = "SELECT SUM(sum) FROM billing_outgoing" . ((!is_null($filter))  ? ' WHERE is_paid = ' . (int)$filter : '');
        return  Yii::app()->db->createCommand($sql)->queryScalar();
    }
}