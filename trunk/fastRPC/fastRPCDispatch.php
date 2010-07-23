<?php
/**
 * fast RPC Server Side Dispatch framework
 *
 */

class fastRPCDispatch
{
	private $method;
	private $rpccfg;

	function __construct() {
		global $rpccfg;
		$this->rpccfg = &$rpccfg;

		ob_start();

		if(isset($this->rpccfg['display_error']) && $this->rpccfg['display_error']) {
			error_reporting(E_ALL ^ E_NOTICE);
			set_error_handler(frpc_error_handle, E_ALL ^ E_NOTICE);
		}else{
			error_reporting(0);
		}

	}

	function __destruct() {
		ob_end_flush();
	}

	function run() {

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
		$file = $this->rpccfg['services'] . '/' . $class . '.php';
		if(!file_exists($file)) {
			trigger_error('invaild services [' . $services . ']', E_USER_ERROR);
			return false;
		}

		include_once(dirname(__FILE__) . '/fastRPCServices.php');
		include_once($file);

		if(!class_exists($class))
			return false;

		$obj = new $class;

		if('fastRPCServices' != get_parent_class($obj))
		{
			trigger_error('parent is wrong!', E_USER_ERROR);
			return false;
		}

		if(!method_exists($obj, $handle . 'Handle'))
		{
			trigger_error('handle [' . $handle . '] not found', E_USER_ERROR);
			return false;
		}

		ob_clean();
		$ret = call_user_method_array($handle . 'Handle', &$obj, array($params));
		$ob_contents = ob_get_contents();
		ob_clean();
		if($ret) {
			echo json_encode(array('state' => '200 success', 'entity' => $ob_contents));
		}else{
			echo json_encode(array('state' => '300 failed', 'error' => $ob_contents));
		}
	}

}

function frpc_error_handle($errno, $errstr, $errfile, $errline)
{
	ob_clean();
	echo json_encode(array('state' => '500 failed', 'error' => 'file=' . $errfile . ', line=' . $errline . ', error=' . $errstr));
	ob_end_flush();

	exit;
}

