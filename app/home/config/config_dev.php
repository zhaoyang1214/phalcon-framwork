<?php
// 模块名称
define('MODULE_NAME', 'home');
// 模块命名空间
define('MODULE_NAMESPACE', APP_NAMESPACE . '\\Home');

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
            // 模板路径
            'view_path' => APP_PATH . MODULE_NAME . '/views/',
            'disable_level' => [
                'level_action_view' => false,
                'level_before_template' => false,
                'level_layout' => true,
                'level_after_template' => true,
                'level_main_layout' => true
            ]
        ],
        'logger' => [
            'file' => [
                'debug' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/debug/{Y-m/d/Y-m-d-H}.log',
                'info' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/info/{Y-m/d/Y-m-d-H}.log',
                'notice' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/notice/{Y-m/d/Y-m-d-H}.log',
                'warning' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/warning/{Y-m/d/Y-m-d-H}.log',
                'error' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/error/{Y-m/d/Y-m-d-H}.log',
                'critical' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/critical/{Y-m/d/Y-m-d-H}.log',
                'alert' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/alert/{Y-m/d/Y-m-d-H}.log',
                'emergency' => BASE_PATH . 'runtime/' . MODULE_NAME . '/logs/emergency/{Y-m/d/Y-m-d-H}.log'
            ]
        ]
    ]
];