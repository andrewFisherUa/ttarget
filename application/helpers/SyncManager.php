<?php


class SyncManager {
    public static $relations = array(
        'campaigns' => array(
            'relations' => array('offers'),
            'jobName' => 'CampaignAddToRedisJob',
            'jobIdParameter' => 'campaign_id'
        ),
        'offers' => array(
            'jobName' => 'OfferSyncToRedisJob',
            'jobIdParameter' => 'offer_id',
            'relations' => array(
                'users' => array('className' => 'offersUsers')
            ),
            'relationOptions' => array('scopes' => array('active', 'running'))
        ),
        'offersUsers' => array(
            'jobName' => 'OfferUserSyncToRedisJob',
            'jobIdParameter' => 'offer_user_id',
            'relationOptions' => array('scopes' => array('accepted'))
        )
    );

    public static $defaultOptions = array(
        'relationOptions' => array(),
    );

    public static function syncRelated($obj)
    {
        $className = lcfirst(get_class($obj));
        if(isset(self::$relations[$className]) && isset(self::$relations[$className]['relations'])){
            foreach(self::$relations[$className]['relations'] as $relatedName => $relatedOptions){
                if(!is_array($relatedOptions)){
                    $relatedName = $relatedOptions;
                    $relatedOptions = array();
                }

                $relatedClassName = isset($relatedOptions['className']) ? $relatedOptions['className'] : $relatedName;
                $relationOptions = self::_getOption('relationOptions', $relatedClassName, $relatedOptions);
                foreach($obj->$relatedName($relationOptions) as $relatedObj) {
                    Yii::app()->resque->createJob(
                        'app',
                        self::_getOption('jobName', $relatedClassName, $relatedOptions),
                        array(self::_getOption('jobIdParameter', $relatedClassName, $relatedOptions) => $relatedObj->id)
                    );
                }
            }
        }
    }

    private static function _getOption($option, $relatedName, $relatedOptions = array()){
        if(isset($relatedOptions[$option])){
            return $relatedOptions[$option];
        }elseif(isset(self::$relations[$relatedName]) && isset(self::$relations[$relatedName][$option])){
            return self::$relations[$relatedName][$option];
        }elseif(isset(self::$defaultOptions[$option])){
            return self::$defaultOptions[$option];
        }

        throw new CException('Cant find related option. Name: '.$relatedName.', Option: '.$option);
    }

    public static function syncNowAndTomorrow($obj)
    {
        self::_sync($obj, true, true);
    }

    public static function syncTomorrow($obj)
    {
        self::_sync($obj, false, true);
    }

    public static function sync($obj){
        self::_sync($obj, true, false);
    }

    private static function _sync($obj, $now = false, $tomorrow = false)
    {
        $className = lcfirst(get_class($obj));
        $job = self::_getOption('jobName', $className);
        $params = array(self::_getOption('jobIdParameter', $className) => $obj->id);
        if($now) {
            Yii::app()->resque->createJob(
                'app',
                $job,
                $params
            );
        }
        if($tomorrow) {
            Yii::app()->resque->enqueueJobAt(
                strtotime('tomorrow'),
                'app',
                $job,
                $params
            );
        }
    }
}