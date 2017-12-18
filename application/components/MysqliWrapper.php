<?php

class MysqliWrapper extends CApplicationComponent
{
    public $host        = 'localhost';
    public $port        = 3306;
    public $username    = '';
    public $password    = '';
    public $database    = '';

    /**
     * @var mysqli
     */
    private $mysqli;

    public function init()
    {
        $this->mysqli = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database,
            $this->port
        );

        if ($this->mysqli->connect_errno) {
            throw new Exception("Can't connect to db");
        }
    }

    /**
     * @return mysqli
     */
    public function client()
    {
        return $this->mysqli;
    }

    public function multiQuery($sql)
    {
        if (!$this->mysqli->multi_query($sql)) {
            throw new Exception($this->mysqli->error."\n".$sql, $this->mysqli->errno);
        }
        while ($this->mysqli->more_results()) {
            $this->mysqli->next_result();
            $this->mysqli->store_result();
            if ($this->mysqli->errno) {
                throw new Exception($this->mysqli->error."\n".$sql, $this->mysqli->errno);
            }
        }
    }
}