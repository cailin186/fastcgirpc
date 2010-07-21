<?php
/**
 * fast RPC server framework
 *
 */

class fastRPCServer
{
	private $method;

	function __construct() {
		//TODO
	}

	function run() {
		global $rpccfg;

		$params = array();
		$method = $_SERVER['FRPC_METHOD'];
		if(empty($method)) {
			return false;
		}

		list($services, $handle) = explode('.', $method);
		if(empty($services))
			return false;

		if(empty($handle))
			$handle = 'index';
		
		foreach($_SERVER as $key => $value) {
			if(0 == strncmp($key, 'FRPC_ARGS_', 10)) {
				$params[substr($key, 10)] = $_SERVER[$key];
			}
		}

		$class = $services . 'Services';
		$file = $rpccfg['services'] . '/' . $class . '.php';
		if(!file_exists($file))
			return false;

		include_once($file);

		if(!class_exists($class))
			return false;

		$obj = new $class;

		if('fcgiRPCServices' != get_parent_class($obj))
			return false;

		if(!method_exists($obj, $handle))
			return false;

		call_user_method_array($handle, &$obj, array($params));
	}
}


/**
 * Services base class
 *
 */
class fcgiRPCServices
{
	function __construct(){
		//TODO
	}
}
