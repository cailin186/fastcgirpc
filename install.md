## 下载 ##

您可以通过SVN方式从我们的源代码仓库中获得，它是一个PHP版本的源代码。

## 环境需求 ##

  * [PHP5.0](http://php.net)以上版本(带socket扩展)
  * [PHP-FPM](http://php-fpm.org/)(端口为9000)

## 安装 ##

将下载的源代码复制到您的cgi程序运行目录

## 配置 ##

  * 进入到fastRPC源代码目录

  * 使用文本编辑器，编辑/rpcserver.php：

```
<?php
$rpccfg['services'] = '/opt/wwwroot/services'; # 改为你所指定的目录
...
```

  * 编辑fastRPC/fastRPC.php：

```
<?php
...
private $rpcRoot = "/opt/wwwroot/rpcserver.php"; # 改为你所指定的目录
```

## 运行 ##

```
> php test/test.php
```