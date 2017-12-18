<?php

/**
 * Класс для работы с данными трэка в редис
 */
class RedisTrack extends RedisAbstract
{
    /**
     * счетчик для генерации трэков
     */
    const KEY_SEQUENCE = 'ttarget:tracks:sequence';

    /**
     * hash трэка
     */
    const KEY_TRACK = 'ttarget:tracks:{track_id}';

    /**
     * @param string $class
     * @return RedisTrack
     */
    public static function instance($class = __CLASS__)
    {
        return parent::instance($class);
    }

    /**
     * Возващает ключ данных цели
     *
     * @param int $trackId
     * @return string
     */
    public function getTrackKey($trackId)
    {
        return str_replace(
            '{track_id}',
            $trackId,
            self::KEY_TRACK
        );
    }

    /**
     * Добавляет трэк в redis
     *
     * @param Tracks $track
     * @return int
     */
    public function addTrack(Tracks $track)
    {
        if($track->getIsNewRecord()){
            $track->id = $this->redis()->incr(self::KEY_SEQUENCE);
        }
        $attrs = $track->getAttributes();
        foreach($attrs as $k => $v){
            if($v === null){
                unset($attrs[$k]);
            }
        }
        $this->redis()->hMset(
            $this->getTrackKey($track->id),
            $attrs
        );
        return $track->id;
    }

    /**
     * Получает трэк из redis
     *
     * @param $trackId
     * @return array|null
     */
    public function getTrack($trackId)
    {
        $track = $this->redis()->hGetAll($this->getTrackKey($trackId));
        if(!empty($track)) {
            $track['id'] = $trackId;
            return $track;
        }
        return null;
    }

    /**
     * Устанавливает счетчик трэков
     *
     * @param $number
     * @return bool
     */
    public function setSequence($number)
    {
        $seq = $this->redis()->get(self::KEY_SEQUENCE);
        if($seq < $number){
            $seq = $number;
            $this->redis()->set(self::KEY_SEQUENCE, $seq);
        }
        return $seq;
    }
}