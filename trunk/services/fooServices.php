<?php
class fooServices extends fastRPCServices
{
	function test1Handle($args)
	{
		if(count($args) == 0)
		{
			$this->setError('args is empty');
			return false;
		}
		else
			return $args;
	}

	function what_is_the_time_nowHandle()
	{
		return date("Y-m-d H:i:j", time());
	}

	function _privatemethodHandle()
	{
		return true;
	}

	function largereturnHandle()
	{
		return str_repeat('1', 1024 * 10);
	}

	function timeoutHandle()
	{
		sleep(1);
		return true;
	}
}
