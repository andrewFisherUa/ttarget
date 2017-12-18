<?php
class ConnectionRequest{
    /**
     * URL адрес запроса
     *
     * @var string
     */
	public $url;

	/**
	 * Параметры запроса
	 *
	 * @var array
	 */
	public $params = array();

	/**
	 * Признак POST запроса
	 *
	 * @var boolean
	 */
    public $isPost;

    /**
     * Дополнительные параметры CURL
     *
     * @var array
     */
    public $options = array();

    /**
     * Передаваемый заголовок referer
     *
     * @var string
     */
    public $referer;

    /**
     * Обратный вызов
     *
	 * @param mixed $callback
	 * @see http://ua.php.net/manual/en/language.pseudo-types.php#language.types.callback
	 */
    public $callback;

    /**
     * Ответ
     *
     * @var ConnectionReply
     */
    public $reply;

    /**
     * идентификатор
     * @var mixed
     */
    public $id;

	public function __construct($url){
		$this->url = $url;
	}
}
