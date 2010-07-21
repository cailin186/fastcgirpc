<?php
$rpccfg['services'] = '/opt/wwwroot/services';

require("fastRPC/fastRPCServer.php");

$server = new fastRPCServer();
$server->run();
