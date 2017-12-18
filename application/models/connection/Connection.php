<?php
/**
 * Connection class
 *
 * @author Vitaliy Dunin (fingerv@gmail.com)
 */
class Connection
{
	/**
	 * Опции curl по умолчанию
	 *
	 * @var array
	 */
	protected $_defaultOptions = array(
		CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)',
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_MAXREDIRS      => 1,
		CURLOPT_FRESH_CONNECT  => 1, // это необходимо?
		CURLOPT_COOKIESESSION  => 1,
		CURLOPT_CONNECTTIMEOUT => 5,
	    CURLOPT_TIMEOUT        => 60,
        CURLOPT_COOKIEFILE     => "",
	    CURLOPT_ENCODING       => 'gzip',
	);

	/**
	 * Инициализированные соединения
	 *
	 * @var resource[]
	 */
	protected $_connections = array();

	/**
	 * Запросы
	 *
	 * @var ConnectionRequest[]
	 */
	protected $_requests = array();

    public function __construct($options = array()){
        $this->_defaultOptions = $this->_defaultOptions + $options;
    }

	/**
	 * Строит строку параметров для запроса
	 *
	 * @param array $params
	 * @return string
	 */
	protected  function _buildParams(array $params){
	    $paramsline = http_build_query($params);
		// Удаляем цифровые индексы добавленные http_build_query во вложенных массивах
		// Это нужно для того чтобы была возможность передать несколько переменных с одним именем
		$paramsline = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $paramsline);
		return $paramsline;
	}

	/**
	 * Строит строку печенек для запроса
	 *
	 * @param array $cookies
	 * @return string
	 */
	protected function _buildCookies(array $cookies){
	    $cookieStr = '';
		foreach ($cookies as $key => $value) {
			$cookieStr .= $key . '=' . $value . ';';
		}
		return $cookieStr;
	}

	/**
	 * Добавляет запрос
	 *
	 * @param ConnectionRequest $request
	 * @throws ConnectionException
	 */
	public function addRequest(ConnectionRequest $request){
		$ch = curl_init();
		if (!$ch) {
			throw new ConnectionException('Cannot initialize CURL');
		}
		$params = $this->_buildParams($request->params);
        $options = $this->_defaultOptions;
        $options[CURLOPT_URL] = $request->url;

        if ($request->isPost){
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $this->_buildParams($request->params);
        }elseif(!empty($request->params)){
            $options[CURLOPT_URL] .= '?' . $this->_buildParams($request->params);
        }

        if (!empty($request->cookies)){
			$options[CURLOPT_COOKIE] = $this->_buildCookies($request->cookies);
        }

        if (!empty($request->referer)){
            $options[CURLOPT_REFERER] = $request->referer;
        }

		if (!curl_setopt_array($ch, ($options + $request->options))){
			throw new ConnectionException('Cannot set CURL options');
		}
		$this->_connections[] = $ch;
		$this->_requests[] = $request;
	}

    public function hasRequests(){
        if(!$this->_connections){
            return false;
        }
        return true;
    }

    public function reset()
    {
        $this->_connections = $this->_requests = array();
        return $this;
    }

	public function run() {
	    if (!$this->_connections) {
			throw new ConnectionException('No handles to process');
		}

		$multiHandle = curl_multi_init();
		if (!$multiHandle) {
			throw new ConnectionException('Cannot init multi handle');
		}

		foreach ($this->_connections as $handle) {
			$res = curl_multi_add_handle($multiHandle, $handle);
			if ($res) {
				throw new ConnectionException('Cannot add handle to multi handle');
			}
		}

		$activeCount = null;
        $this->_multiExec($multiHandle, $activeCount);
		do {
			$this->_multiSelect($multiHandle);
            $this->_multiExec($multiHandle, $activeCount);
            while($info = curl_multi_info_read($multiHandle)){
                $handle_info = curl_getinfo($info['handle']);
                $this->_onHandleReady($info, $handle_info);
                curl_multi_remove_handle($multiHandle, $info['handle']);
            }
		} while ($activeCount > 0);

		return $this->_requests;
	}

    private function _multiExec($mh, &$activeCount){
        do {
            $execRes = curl_multi_exec($mh, $activeCount);
        } while ($execRes == CURLM_CALL_MULTI_PERFORM);
        if ($execRes != CURLE_OK) {
            throw new ConnectionException(sprintf('Multi exec returned %d, expected %d', $execRes, CURLE_OK));
        }
        return $execRes;
    }

    private function _multiSelect($mh){
        if (curl_multi_select($mh) == -1) {
            usleep(1);
        }
    }

	/**
	 * @param array $info
     * @param array $handleInfo
     * @throws ConnectionException
	 */
	protected function _onHandleReady($info, $handleInfo){
	    $handleIndex = array_search($info['handle'], $this->_connections, true);
		if ($handleIndex === false) {
		    throw new ConnectionException(sprintf('Handle not found among %d now processing handles', count($this->_connections)));
		}

	    $reply = new ConnectionReply();
        $reply->result = $info['result'];
	    $reply->info = $handleInfo;
	    $reply->content = curl_multi_getcontent($info['handle']);
		unset($this->_connections[$handleIndex]);
        $this->_requests[$handleIndex]->reply = $reply;
	}

}