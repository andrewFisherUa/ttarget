<?php

/**
 * This is the model class for table "offers_users_notifications".
 *
 * The followings are the available columns in table 'offers_users_notifications':
 * @property string $id
 * @property string $user_id
 * @property string $created_date
 * @property string $text
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Users $user
 */
class OffersUsersNotifications extends CActiveRecord
{
	const STATUS_NEW = 0;
	const STATUS_OLD = 1;
	
	/**
	*	Возвращает список уведомлений для пользователя
	**/
	public function findByUserId( $user_id, $status = self::STATUS_NEW )
	{
		$_criteria = new CDbCriteria();
		$_criteria -> addCondition('user_id = :user_id');
		$_criteria -> addCondition('status = :status');
		$_criteria -> order = 'created_date ASC';
		$_criteria -> params = array(':user_id' => $user_id, ':status' => $status);
		return $this->findAll($_criteria);
	}
	
	/**
	*	Отправка уведомления пользователю
	**/
	public function send($user_id, $text)
	{
		$notification = new OffersUsersNotifications('create');
		$notification -> user_id = $user_id;
		$notification -> text = $text;
		$notification -> save(false);
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OffersUsersNotifications the static model class
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
		return 'offers_users_notifications';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, created_date, text', 'required'),
			array('status', 'numerical', 'integerOnly'=>true),
			array('user_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, created_date, text, status', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => 'User',
			'created_date' => 'Created Date',
			'text' => 'Text',
			'status' => 'Status',
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('created_date',$this->created_date,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	public function behaviors()
	{
		return array(
				'timestamps' => array(
						'class'                 => 'zii.behaviors.CTimestampBehavior',
						'createAttribute'       => 'created_date',
						'updateAttribute'       => null,
						'timestampExpression'   => new CDbExpression('now()'),
				),
		);
	}
}