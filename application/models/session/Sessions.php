<?php

/**
 * This is the model class for table "sessions".
 *
 * The followings are the available columns in table 'user_session':
 * @property string $id
 * @property string $uid
 * @property string $created_date
 * @property string $last_date
 *
 */
class Sessions extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Sessions the static model class
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
		return 'sessions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('uid, created_date, last_date', 'required'),
			array('uid', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, uid, created_date, last_date', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'uid' => 'Uid',
			'created_date' => 'Created Date',
			'last_date' => 'Last Date',
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
		$criteria->compare('uid',$this->uuid,true);
		$criteria->compare('created_date',$this->created_date,true);
		$criteria->compare('last_date',$this->last_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function addGeo($uid, $countryCode, $cityId)
    {
        $this->getDbConnection()->createCommand(
            'INSERT INTO `sessions_geo` VALUES(:sessionId, :countryCode, :cityId, NOW())'
            .' ON DUPLICATE KEY UPDATE last_date = NOW()'
        )->execute(array(
            ':sessionId' => $this->getIdByUid($uid),
            ':countryCode' => $countryCode,
            ':cityId' => $cityId
        ));

    }

    public function addTagsByTeaserId($uid, $teaserId, $count)
    {
        $this->getDbConnection()->createCommand(
            'INSERT INTO `sessions_tags` SELECT :sessionId, tag_id, :count, NOW() FROM `teasers_tags` WHERE `teaser_id`=:teaserId'
            .' ON DUPLICATE KEY UPDATE `count` = `count` + :count, `last_date` = NOW();'
        )->execute(array(
            ':sessionId' => $this->getIdByUid($uid),
            ':teaserId' => $teaserId,
            ':count' => $count
        ));
    }

    public function getIdByUid($uid)
    {
        $this->getDbConnection()->createCommand(
            'INSERT INTO `'.$this->tableName().'` (`uid`, `created_date`, `last_date`) VALUES(:uid, NOW(), NOW())'
            .' ON DUPLICATE KEY UPDATE `last_date`=NOW(), `id`=LAST_INSERT_ID(id)'
        )->execute(array(':uid' => $uid));

        return $this->getDbConnection()->getLastInsertID();
    }

    public function getTopTags()
    {
        $result = $this->getDbConnection()->createCommand()
            ->select(array(
                't.name',
                'SUM(st.count) AS scount'
            ))
            ->from('sessions_tags st')
            ->leftJoin('tags t', 'st.tag_id = t.id')
            ->group('st.tag_id')
            ->order('scount DESC')
            ->limit(10)
            ->queryAll();
        return $result;
    }

    public function getLast100()
    {
        $cmd = $this->getDbConnection()->createCommand()
            ->select('*')
            ->from($this->tableName())
            ->where('(select 1 from sessions_tags where session_id = sessions.id limit 1)')
            ->order('last_date DESC')
            ->limit(100);
        $sessions = array();
        $ids = array();
        foreach($cmd->query() as $row){
            $ids[] = $row['id'];
            $sessions[$row['id']] = $row;
        }

        $cmd = $this->getDbConnection()->createCommand()
            ->select(array('*', 't.name AS tag_name'))
            ->from('sessions_tags')
            ->leftJoin('tags t', 't.id = sessions_tags.tag_id')
            ->where(
                $this->getDbConnection()->commandBuilder
                    ->createInCondition('sessions_tags', 'session_id', $ids)
            );

        foreach($cmd->query() as $row){
            $sessions[$row['session_id']]['tags'][] = $row;
        }

        $cmd = $this->getDbConnection()->createCommand()
            ->select(array('*', 'c.name AS country_name', 's.name AS city_name'))
            ->from('sessions_geo')
            ->leftJoin(Countries::model()->tableName() . ' c', 'sessions_geo.country_code = c.code')
            ->leftJoin(Cities::model()->tableName() . ' s', 'sessions_geo.city_id = s.id')
            ->where(
                $this->getDbConnection()->commandBuilder
                    ->createInCondition('sessions_geo', 'session_id', $ids)
            );

        foreach($cmd->query() as $row){
            $sessions[$row['session_id']]['geo'][] = $row;
        }

        return $sessions;
    }

    public function addPage($uid, $pageId, $count)
    {
        $this->getDbConnection()->createCommand(
            'INSERT INTO `sessions_segments`'
            .' SELECT :uid, segment_id, :count, NOW() FROM `pages_segments` WHERE `page_id`=:pageId'
            .' ON DUPLICATE KEY UPDATE `count` = `count` + :count, `last_date` = NOW();'
        )->execute(array(
            ':uid' => $uid,
            ':pageId' => $pageId,
            ':count' => $count
        ));
    }

}