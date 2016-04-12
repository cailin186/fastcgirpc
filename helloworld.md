快速编写一个Hello World服务

## 创建服务程序 ##

进入 services 目录，创建 helloServices.php ，将以下代码复制到文件中：

```
<?php
class helloServices extends fastRPCServices
{
  function sayHandle()
  {
    return 'Hello World!';
  }
}
?>
```

## 创建测试程序 ##

进入 test 目录，创建 hello.php ，将以下代码复制到文件中：

```
<?php
require("/opt/wwwroot/fastRPC/fastRPC.php");
$rpc = new fastRPC();
$rpc->setServer('127.0.0.1', 9000);
$rpc->setTimeout(2, 1);

$ret = $rpc->call("hello.say");
var_dump($ret);
?>
```

## 运行 ##

```
> php test/hello.php
```

输出结果：

```
string(47) "{"state":"200 success","entity":"HelloWorld!"}"
```