<?php

/**
 * This is the model class for table "short_link".
 *
 * The followings are the available columns in table 'short_link':
 * @property string $id
 * @property string $eid
 * @property string $expire_date
 * @property string $url
 * @property string $target_type
 * @property integer $target_id
 */
class ShortLink extends CActiveRecord
{
    const TARGET_TYPE_OFFER_USER = 'offer_user';
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ShortLink the static model class
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
		return 'short_link';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('expire_date, url, target_id', 'required'),
			array('target_id', 'numerical', 'integerOnly'=>true),
			array('eid', 'length', 'max'=>255),
			array('url', 'length', 'max'=>2048),
			array('target_type', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, eid, expire_date, url, target_type, target_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'eid' => 'Eid',
			'expire_date' => 'Expire Date',
			'url' => 'Url',
			'target_type' => 'Target Type',
			'target_id' => 'Target',
		);
	}

    protected function beforeSave()
    {
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $this->eid = self::createEid($this->id);
        $this->updateByPk($this->id, array('eid' => $this->eid));
    }

    public function getUrl()
    {
        return Yii::app()->params->shortLinkBaseUrl . $this->eid;
    }

    /* полный набор символов разрешенный в url (79 знаков)*/
    // private static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._!$&\'()*+,;=:?/';

    /* набор учитывающий текущий парсер vk, fb, gmail */
    private static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-._$()+;=:?/';

    public static function createEid($id)
    {
        $cLength = strlen(self::$chars);
        $eid = '';
        while($id > $cLength - 1){
            $eid .= self::$chars[bcmod($id, $cLength)];
            $id = (int) ($id / $cLength);
        }
        $eid .= self::$chars[(int) $id];
        return $eid;
    }

    public static function createLink($target_type, $target_id, $url, $expire_date)
    {
        $link = ShortLink::model()->findByAttributes(array(
            'target_type' => $target_type,
            'target_id' => $target_id,
        ));
        if(! $link instanceof ShortLink){
            $link = new ShortLink();
            $link->target_type = $target_type;
            $link->target_id = $target_id;
            $link->url = self::prepareUrl($url);
            $link->expire_date = $expire_date;
            if(!$link->save()){
                throw new CException('Cant save ShortLink: '. var_export($link->getErrors(), true));
            }
            RedisShortLink::instance()->set($link);
        }

        return $link;
    }

    public static function prepareUrl($url)
    {
        $url = parse_url($url);
        if($url === false){
            throw new CException('Cant parse url: ' . $url);
        }
        return $url['path']
            .(isset($url['query']) ? '?'.$url['query'] : '')
            .(isset($url['fragment']) ? '#'.$url['fragment'] : '');
    }
}