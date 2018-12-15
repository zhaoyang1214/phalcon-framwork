<?php
// 模块名称
define('MODULE_NAME', 'Api');
// 模块命名空间
define('MODULE_NAMESPACE', APP_NAMESPACE . '\\Api');

return [
    // 服务配置
    'services' => [
        // 调度器配置
        'dispatcher' => [
            // 模块默认的命名空间
            'module_default_namespaces' => MODULE_NAMESPACE . '\\Controllers',
            // 处理 Not-Found错误配置
            'notfound' => [
                // 错误跳转的页面
                'namespace' => MODULE_NAMESPACE . '\\Controllers',
                'controller' => 'error',
                'action' => 'error404'
            ]
        ],
        // 模板相关配置
        'view' => [
            // 是否关闭视图
            'disable' => true
        ],
        'logger' => [
            'file' => [
                'debug' => RUNTIME_PATH . MODULE_NAME . '/logs/debug/{Y-m/d/Y-m-d-H}.log',
                'info' => RUNTIME_PATH . MODULE_NAME . '/logs/info/{Y-m/d/Y-m-d-H}.log',
                'notice' => RUNTIME_PATH . MODULE_NAME . '/logs/notice/{Y-m/d/Y-m-d-H}.log',
                'warning' => RUNTIME_PATH . MODULE_NAME . '/logs/warning/{Y-m/d/Y-m-d-H}.log',
                'error' => RUNTIME_PATH . MODULE_NAME . '/logs/error/{Y-m/d/Y-m-d-H}.log',
                'critical' => RUNTIME_PATH . MODULE_NAME . '/logs/critical/{Y-m/d/Y-m-d-H}.log',
                'alert' => RUNTIME_PATH . MODULE_NAME . '/logs/alert/{Y-m/d/Y-m-d-H}.log',
                'emergency' => RUNTIME_PATH . MODULE_NAME . '/logs/emergency/{Y-m/d/Y-m-d-H}.log'
            ]
        ],
        'session' => [
            'auto_start' => true,
            'options' => [
                'adapter' => 'files',
                'unique_id' => MODULE_NAME
            ]
        ],
        'crypt' => [
            'key' => MODULE_NAME
        ],
        // url配置
        'url' => [
            'base_uri' => DS . MODULE_NAME . DS,
            'static_base_uri' => DS . MODULE_NAME . '/static/',
            'base_path' => ''
        ]
    ]
];