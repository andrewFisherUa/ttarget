<?php

class RedisCache extends CCache
{
    public $host = 'localhost';
    public $port = 6379;
    public $timeout = 3.5; // seconds in float
    public $dbIndex = 0;

    /**
     * @var Redis
     */
    private $redis;

    public function init()
    {
        parent::init();

        $this->redis       = new Redis();

        $this->redis->open($this->host, $this->port, $this->timeout);
        $this->redis->select($this->dbIndex);
    }

    protected function generateUniqueKey($key)
    {
        if (YII_DEBUG) {
            return $key;
        } else {
            return parent::generateUniqueKey($key);
        }
    }

    /**
     * @return Redis
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $ttl время жизни ключа в секундах. Если 0, то храним бесконечно
     *
     * @return bool
     */
    protected function setValue($key, $value, $ttl)
    {
        if (0 == $ttl) {
            return $this->redis->set($key, $value);
        } else {
            return $this->redis->setex($key, $ttl, $value);
        }
    }

    protected function addValue($key, $value, $ttl)
    {
        $result = $this->redis->setnx($key, $value);

        if ($result) {
            $this->redis->expire($key, $ttl);
        }

        return $result;
    }

    protected function getValue($key)
    {
        return $this->redis->get($key);
    }

    protected function getValues($keys)
    {
        $ids = array_values($keys);
        $values = $this->redis->mGet($ids);
        $result = array();

        foreach ($ids as $k => $id) {
            $result[$id] = $values[$k];
        }

        return $result;
    }

    protected function deleteValue($key)
    {
        return $this->redis->delete($key);
    }

    protected function flushValues()
    {
        $return = true;
        foreach ($this->redis->_hosts() as $host) {
            $return = $this->redis->_instance($host)->flushDB() && $return;
        }
        return $return;
    }
}