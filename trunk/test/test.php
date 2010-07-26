<?php
require("/opt/app/rpcserver/fastrpc/FastRPC.php");
$rpc = new FastRPC();
$rpc->setServer('127.0.0.1', 9000);
$rpc->setTimeout(0.1, 0.09);

echo "-------- Foo.rpclist -------\n";
$ret = $rpc->call("Foo.rpclist");
var_dump($ret);

echo "-------- Foo.test1 -------\n";
$ret = $rpc->call("Foo.test1", array('param1' => 'p1', 'param2' => 'p2'));
var_dump($ret);

echo "-------- Foo.what_is_the_time_now -------\n";
$ret = $rpc->call("Foo.what_is_the_time_now");
var_dump($ret);

echo "-------- invalid call -------\n";
$ret = $rpc->call("Foo._privatemethod");
var_dump($ret);

echo "-------- Foo.timeout -------\n";
$ret = $rpc->call("Foo.timeout");
var_dump($ret);

echo "-------- Foo.largereturn  -------\n";
$rpc->setTimeout(2, 1);
$ret = $rpc->call("Foo.largereturn");
printf("length = %d\n", strlen($ret));
