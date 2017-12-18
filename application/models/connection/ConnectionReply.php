<?php
class ConnectionReply{
    /**
     * Результат выполнения запроса (curl_errno)
     *
     * @var int
     */
    public $result;

    /**
     * Дополнительная информация о результате выполнения запроса
     *
     * @var array
     */
    public $info;

    /**
     * Тело ответа
     *
     * @var string
     */
    public $content;
}