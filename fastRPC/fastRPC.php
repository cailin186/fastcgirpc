<?php
/**
 * fast RPC sdk
 *
 * remote process use fast-cgi protocal
 * @author <lowellzhong@gmail.com>
 *
 * code example:
 *
 * $rpc = new fastRPC;
 * $rpc->setTimeout(0.1, 0.01);			//set call timeout and socket read/write timeout
 * $rpc->setServer('10.134.11.13', '8080');	//set RPC server address
 * $photos = $rpc->call("photo.list", array('userid' => 123456, 'page' => 4, 'pagesize' => 30));
 * ...
 *
 */

define('FCGI_VERSION_1', 1);
define('FCGI_BEGIN_REQUEST', 1);
define('FCGI_RESPONSE', 1);
define('FCGI_ABORT_REQUEST', 2);
define('FCGI_END_REQUEST', 3);
define('FCGI_PARAMS', 4);
define('FCGI_STDIN', 5);
define('FCGI_STDOUT', 6);
define('FCGI_STDERR', 7);
define('FCGI_DATA', 8);

define('FRPC_HOST', '127.0.0.1');
define('FRPC_PORT', '9000');
define('FRPC_ROOT', '/opt/wwwroot/rpcserver.php');
define('FRPC_CALL_TIMEOUT', 0.3);			//second
define('FRPC_SOCKET_RW_TIMEOUT', 0.1);		//second

class fastRPC
{
	private $host = FRPC_HOST;
	private $port = FRPC_PORT;
	private $requestID = 1;
	private $rpc_root = FRPC_ROOT;
	private $sock = null;
	private $call_timeout;	//second
	private $socket_rw_timeout;	//usecond

	function __construct($server = null, $root = null, $timeout = null) {

		$this->socket_rw_timeout = FRPC_SOCKET_RW_TIMEOUT * 1000000;
		$this->call_timeout = FRPC_CALL_TIMEOUT;

		if(!is_null($server)) {
			list($host, $port) = explode(':', $server);
			if(!isset($port))
				$port = FRPC_PORT;

			$this->setServer($host, $port);
		}

		if(!is_null($root))	{
			$this->setRPCRoot($root);
		}

		if(!is_null($timeout) && is_array($timeout)) {
			$this->setTimeout($timeout[0], $timeout[1]);
		}
	}

	function setRPCRoot($root) {
		$this->rpc_root = $root;	
	}

	function setServer($host, $port) {
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * set rpc call timeout
	 *
	 * @param int $t1 call execute timeout, second
	 * @param int $t2 socket r/w timeout, second
	 */
	function setTimeout($t1, $t2) {

		if($t2 > $t1) {
			trigger_error('socket read/write timeout must less than call-timeout', E_USER_WARNING);
			return false;
		}

		$this->call_timeout = $t1;
		$this->socket_rw_timeout = $t2 * 1000000;

		return true;
	}

	/**
	 * get float time
	 */
	function time() {
		list($usec, $sec) = explode(" ", microtime());
		return (float)$usec + (float)$sec;
	}

	function call($method, $args = array()) {
	
		$time_start = $this->time();
		$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_connect($this->sock, $this->host, $this->port);
	
		$body = $this->makeBeginRequestBody(FCGI_RESPONSE, false);
		$header = $this->makeHeader(FCGI_BEGIN_REQUEST, $this->requestID, strlen($body), 0);
		$record = $header . $body;
		$ret = socket_write($this->sock, $record);

		$evn = array();
		$env['SCRIPT_FILENAME'] = $this->rpc_root;
		$env['FRPC_METHOD'] = $method;

		foreach($env as $key => $value)
		{
			$body = $this->makeNameValueHeader($key, $value);
			$header = $this->makeHeader(FCGI_PARAMS, $this->requestID, strlen($body), 0);
			$record = $header . $body;
			$ret = socket_write($this->sock, $record);
		}

		foreach($args as $key => $value)
		{
			$body = $this->makeNameValueHeader("FRPC_ARGS_" . $key, $value);
			$header = $this->makeHeader(FCGI_PARAMS, $this->requestID, strlen($body), 0);
			$record = $header . $body;
			socket_write($this->sock, $record);
		}

		$body = "";
		$header = $this->makeHeader(FCGI_STDIN, $this->requestID, strlen($body), 0);
		$record = $header . $body;
		$len = socket_write($this->sock, $record);

		//timeout set
		socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 0, 'usec' => $this->socket_rw_timeout));

		$content = "";
		$len = 0;
		while($tmp = socket_read($this->sock, 8)) {
			$time = $this->time();
			if(($time - $time_start) > $this->call_timeout) {
				socket_close($this->sock);
				trigger_error("call timeout", E_USER_WARNING);
				return false;
			}
			
			$header = unpack("Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved", $tmp);
			$tmp = socket_read($this->sock, $header['contentLength']);
			if(false === $tmp)
				return false;

			if($header['paddingLength'] > 0) {
				if(false === socket_read($this->sock, $header['paddingLength'])) {
					return false;
				}
			}
	
			if($header['type'] == FCGI_STDOUT || $header['type'] == FCGI_STDERR) {
				$content .= $tmp;
				$len += $header['contentLength'];
			}
		}

		if(false === $tmp) {
			trigger_error(socket_strerror(socket_last_error()) . '(socket timeout)', E_USER_WARNING);
			return false;
		}

		$pos = strpos($content, "\r\n\r\n");
		if(false === $pos) {
			trigger_error('invalid prototal', E_USER_WARNING);
			return false;
		}
		$pos += 4;

		socket_close($this->sock);
		$this->time_lastcall = $this->time() - $time_start;
		
		if($pos == $len) {
			return '';
		}else{
			return substr($content, $pos);
		}
	}

	function makeHeader($type, $requestId, $contentLength, $paddingLength)
	{
		$bin = pack("C2n2C2", FCGI_VERSION_1, $type, $requestId, $contentLength, $paddingLength, 0);

		return $bin;
	}

	function makeBeginRequestBody($role, $keepConnection = false)
	{
		$bin = pack("nC6", $role, $keepConnection, 0, 0, 0, 0, 0);

		return $bin;
	}

	function makeNameValueHeader($name, $value)
	{
		$nameLen = strlen($name);
		$valueLen = strlen($value);

		$bin = '';
		if($nameLen < 0x80)
		{
			$bin = chr($nameLen); 
		}else{
			$bin .= chr(($valueLen >> 24) & 0x7f | 0x80);
			$bin .= chr(($valueLen >> 16) & 0xff);
			$bin .= chr(($valueLen >> 8) & 0xff);
			$bin .= chr( $valueLen & 0xff);
		}

		if($valueLen < 0x80)
		{
			$bin .= chr($valueLen); 
		}else{
			$bin .= chr(($valueLen >> 24) & 0x7f | 0x80);
			$bin .= chr(($valueLen >> 16) & 0xff);
			$bin .= chr(($valueLen >> 8) & 0xff);
			$bin .= chr($valueLen & 0xff);
		}

		$bin .= $name . $value;	

		return $bin;
	}
}
