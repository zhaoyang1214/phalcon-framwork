<?php

// 应用开始时间
define('APP_START_TIME', microtime(true));

// phalcon版本
define('PHALCON_VERSION', Phalcon\Version::get());

// 请求ID
define('REQUEST_ID', uniqid() . mt_rand(10000, 99999));

// 重新命名文件分隔符，建议路径后面加上分隔符
define('DS', DIRECTORY_SEPARATOR);

// 应用程序名称（应用程序所在目录名）
define('APP_NAME', 'app');

// 顶级命名空间
define('APP_NAMESPACE', 'App');

// 项目根目录
define('BASE_PATH', dirname(__DIR__) . DS);

// 应用程序所在目录
define('APP_PATH', BASE_PATH . APP_NAME . DS);

// web目录
define('PUBLIC_PATH', BASE_PATH . 'public/');

// 运行目录
define('RUNTIME_PATH', BASE_PATH . 'runtime/');

// 模块列表
define('MODULE_ALLOW_LIST', [
    'Home',
    'Admin',
    'Api'
]);

// 默认模块
define('DEFAULT_MODULE', 'Home');

// 默认模块命名空间
define('DEFAULT_MODULE_NAMESPACE', APP_NAMESPACE . '\\Home');

// 默认使用的配置文件名，dev、test、pro
define('NOW_ENV', 'dev');