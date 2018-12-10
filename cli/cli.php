<?php
use Phalcon\Cli\Console;

/**
 * 命令行入口文件
 */
version_compare(PHP_VERSION, '7.0.0', '>') || exit('Require PHP > 7.0.0 !');
extension_loaded('phalcon') || exit('Please open the Phalcon extension !');

// 引入自定义常量文件
require '../config/define.php';

version_compare(PHALCON_VERSION, '3.0.0', '>') || exit('Require Phalcon > 3.0.0 !');

// 设置时区
date_default_timezone_set('Asia/Shanghai');

NOW_ENV != 'dev' && error_reporting(E_ALL & ~ E_NOTICE);

try {
    
    // 引入composer自动加载
    require_once BASE_PATH . 'vendor/autoload.php';
    
    // 注册自动加载
    require BASE_PATH . 'cli/config/loader.php';
    
    // 引入注册服务
    $di = require BASE_PATH . 'cli/config/services.php';
    
    // 处理请求
    $console = new Console($di);
    
    // 设置选项
    $console->setArgument($argv);
    
    $arguments = [];
    foreach ($argv as $k => $arg) {
        if ($k === 1) {
            $arguments['task'] = $arg;
        } elseif ($k === 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }
    
    // 处理请求
    $console->handle($arguments);
} catch (\Throwable $e) {
    $previous = $e->getPrevious();
    if (! is_object($console->config)) {
        goto SYSTEMERROR;
    }
    $errorMessage = 'Exception： [所在文件：' . $e->getFile() . '] [所在行：' . $e->getLine() . '] [错误码：' . $e->getCode() . '] [错误消息：' . $e->getMessage() . '] '/*  . PHP_EOL . '[异常追踪信息：' . $e->getTraceAsString() . ']' */;
    if (! is_null($previous)) {
        $errorMessage .= '  Previous Exception： [所在文件：' . $previous->getFile() . '] [所在行：' . $previous->getLine() . '] [错误码：' . $previous->getCode() . '] [错误消息：' . $previous->getMessage() . '] '/*  . PHP_EOL . '[异常追踪信息：' . $previous->getTraceAsString() . ']' */;
    }
    $applicationConfig = $console->config->application;
    if ($applicationConfig->debug) {
        SYSTEMERROR:
        echo $errorMessage;
        exit();
    }
    $console->di->get('logger', [
        'error'
    ])->error($errorMessage);
}