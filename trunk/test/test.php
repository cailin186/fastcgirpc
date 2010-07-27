<?php
require("/opt/wwwroot/fastRPC/fastRPC.php");
$rpc = new fastRPC();
$rpc->setServer('127.0.0.1', 9000);
$rpc->setTimeout(0.1, 0.09);

echo "-------- foo.rpclist -------\n";
$ret = $rpc->call("foo.rpclist");
var_dump($ret);

echo "-------- foo.test1 -------\n";
$ret = $rpc->call("foo.test1", array('param1' => 'p1', 'param2' => 'p2'));
var_dump($ret);

echo "-------- foo.what_is_the_time_now -------\n";
$ret = $rpc->call("foo.what_is_the_time_now");
var_dump($ret);

echo "-------- invalid call -------\n";
$ret = $rpc->call("foo._privatemethod");
var_dump($ret);

echo "-------- foo.timeout -------\n";
$ret = $rpc->call("foo.timeout");
var_dump($ret);

echo "-------- foo.largereturn  -------\n";
$rpc->setTimeout(2, 1);
$ret = $rpc->call("foo.largereturn");
printf("length = %d\n", strlen($ret));
