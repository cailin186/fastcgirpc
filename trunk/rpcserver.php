<?php
$rpccfg['services'] = '/opt/app/rpcserver/services';
$rpccfg['display_error'] = true;

require("fastrpc/FastRPCDispatch.php");

$server = new FastRPCDispatch();
$server->run();
