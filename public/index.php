<?php
use \Phalcon\Mvc\Application;

// 检查版本，搭建用到php7一些新特性,运行成功后注释判断
version_compare(PHP_VERSION, '7.0.0', '>') || exit('Require PHP > 7.0.0 !');

// 检查是否安装phalcon扩展，运行成功后注释判断
extension_loaded('phalcon') || exit('Please open the Phalcon extension !');

// 引入自定义常量文件
require_once '../config/define.php';

// 检查phalcon版本，运行成功后注释判断
version_compare(PHALCON_VERSION, '3.0.0', '>') || exit('Require Phalcon > 3.0.0 !');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

NOW_ENV != 'dev' && error_reporting(E_ALL & ~ E_NOTICE);

try {
    
    // 引入composer自动加载
    require_once BASE_PATH . 'vendor/autoload.php';
    
    // 引入注册服务
    $di = require_once BASE_PATH . 'config/services.php';
    
    // 处理请求
    $application = new Application($di);
    
    // 组装应用程序模块
    $modules = [];
    foreach (MODULE_ALLOW_LIST as $v) {
        $modules[$v] = [
            'className' => APP_NAMESPACE . '\\' . ucfirst($v) . '\\Module',
            'path' => APP_PATH . $v . '/Module.php'
        ];
    }
    
    // 加入模块分组配置
    $application->registerModules($modules);
    
    // 输出请求内容
    echo $application->handle()->getContent();
} catch (\Throwable $e) {
    $previous = $e->getPrevious();
    if (! is_object($application->config)) {
        goto SYSTEMERROR;
    }
    $errorMessage = 'Exception： [所在文件：' . $e->getFile() . '] [所在行：' . $e->getLine() . '] [错误码：' . $e->getCode() . '] [错误消息：' . $e->getMessage() . '] '/*  . PHP_EOL . '[异常追踪信息：' . $e->getTraceAsString() . ']' */;
    if (! is_null($previous)) {
        $errorMessage .= '  Previous Exception： [所在文件：' . $previous->getFile() . '] [所在行：' . $previous->getLine() . '] [错误码：' . $previous->getCode() . '] [错误消息：' . $previous->getMessage() . '] '/*  . PHP_EOL . '[异常追踪信息：' . $previous->getTraceAsString() . ']' */;
    }
    $applicationConfig = $application->config->application;
    if ($applicationConfig->debug) {
        SYSTEMERROR:
        echo $errorMessage;
        exit();
    }
    $application->di->get('logger', [
        'error'
    ])->error($errorMessage);
}