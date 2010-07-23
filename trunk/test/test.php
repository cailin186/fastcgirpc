<?php
require("/opt/wwwroot/fastRPC/fastRPC.php");

$rpc = new fastRPC();
$rpc->setTimeout(0.1, 0.09);

$t1 = microtime_float();
$ret = $rpc->call("foo.index");
$t2 = microtime_float();

$t = $t2 - $t1;
printf("time = %f\tcontent='%s'\n",  $t, false === $ret ? 'false' : $ret);

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
