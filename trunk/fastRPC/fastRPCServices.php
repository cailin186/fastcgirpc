<?php
/**
 * fastRPC Services base class
 *
 */
class fastRPCServices
{
	private $error = '';

	function __construct(){
		//TODO
	}


	function getError() {
		return $this->error;
	}

	function setError($error) {
		$this->error = $error;	
	}
}
