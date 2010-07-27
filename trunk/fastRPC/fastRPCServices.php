<?php
/**
 * fastRPC Services base class
 *
 */
class fastRPCServices
{
	private $__error = '';

	function __construct(){
		//TODO
	}

	function getError() {
		return $this->__error;
	}

	function setError($error) {
		$this->__error = $error;
	}

	function rpclistHandle() {
		$ret = '';
		$methods = get_class_methods($this);
		foreach($methods as $method_name) {
			if(substr($method_name, -6) == 'Handle' && $method_name[0] != '_')
				$ret .= (empty($ret) ? '' : ',') . substr($method_name, 0, -6);
		}

		return $ret;
	}
}
