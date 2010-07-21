<?php
require("/opt/wwwroot/fastRPC/fastRPC.php");

$rpc = new fastRPC();

$max = 0;
for($i = 1; $i<= 10; $i++)
{
	$t1 = microtime_float();
	$ret = $rpc->call("foo", array('user' => 'lowell', 'time' => time()));
	$t2 = microtime_float();

	$t = $t2 - $t1;
	printf("%d\t%f\t%s\n", $i, $t, $ret ? 'true' : 'false');
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
