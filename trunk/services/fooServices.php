<?php
class fooServices extends fcgiRPCServices
{
	function index($args)
	{
		echo time();
		//sleep(2);
		echo time();
	}
}
