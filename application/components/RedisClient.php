<?
/**
 * Client for redis
 *
 * @link https://github.com/nicolasff/phpredis
 */

class RedisClient extends CApplicationComponent
{
    public $host = 'localhost';
    public $port = 6379;
    public $timeout = 3.5; // seconds in float
    public $dbIndex = 0;

    private $client = null;
    private $isConnected = false;

    /**
     * @brief   Initialize component.
     */
    public function init()
    {
        parent::init();

        $this->client       = new Redis();
        $this->isConnected  = $this->client->open($this->host, $this->port, $this->timeout);

        $this->client->select($this->dbIndex);
    }

    public function isConnected()
    {
        return $this->isConnected;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->client, $method), $arguments);
    }

}