<?php
$rpccfg['services'] = '/opt/wwwroot/services';
$rpccfg['display_error'] = true;

require("fastRPC/fastRPCDispatch.php");

$server = new fastRPCDispatch();
$server->run();
