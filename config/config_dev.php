<?php
return [
    // 应用配置
    'application' => [
        'debug' => false
    ],
    // 服务配置
    'services' => [
        // 数据库配置
        'db' => [
            // 使用动态更新
            'use_dynamic_update' => true,
            // ORM选项配置
            'orm_options' => [
                // 是否对字段是否为空的判断
                'not_null_validations' => false
            ],
            'prefix' => 'ph_',
            'mysql' => [
                'host' => 'localhost',
                'port' => 3306,
                'username' => 'root',
                'password' => '123456',
                'dbname' => 'phalconcms',
                'charset' => 'utf8'
            ]
        ],
        // 调度器配置
        'dispatcher' => [
            // 处理 Not-Found错误配置
            'notfound' => [
                // 错误跳转的页面
                'namespace' => DEFAULT_MODULE_NAMESPACE . '\\Controllers',
                'controller' => 'error',
                'action' => 'error404'
            ]
        ],
        // volt引擎相关配置
        'view_engine_volt' => [
            // 编译模板目录
            'compiled_path' => BASE_PATH . 'runtime/cache/compiled/',
            // 是否实时编译
            'compile_always' => true,
            // 附加到已编译的PHP文件的扩展名
            'compiled_extension' => '.php',
            // 使用这个替换目录分隔符
            'compiled_separator' => '%%',
            // 是否要检查在模板文件和它的编译路径之间是否存在差异
            'stat' => true,
            // 模板前缀
            'prefix' => '',
            // 支持HTML的全局自动转义
            'autoescape' => false
        ],
        // 模板相关配置
        'view' => [
            // 是否关闭视图
            'disable' => false,
            // 模板路径
            'view_path' => APP_PATH . DEFAULT_MODULE . '/views' . DS,
            // 模板引擎,根据模板后缀自动匹配视图引擎，不启用则设为false
            'engines' => [
                '.volt' => 'viewEngineVolt',
                '.phtml' => 'viewEnginePhp'
            ],
            'disable_level' => [
                'level_action_view' => false,
                'level_before_template' => true,
                'level_layout' => true,
                'level_after_template' => true,
                'level_main_layout' => true
            ]
        ],
        // 过滤器设置
        'filter' => [
            // 过滤类型，支持string、trim、absint、int!、email、float、int、float!、alphanum、striptags、lower、upper、url、special_chars
            'default_filter' => 'string,trim'
        ],
        // 文件日志,formatter常用line，adapter常用file
        'logger' => [
            'line' => [
                'format' => '[%date%][' . REQUEST_ID . '][%type%] %message%',
                'date_format' => 'Y-m-d H:i:s'
            ],
            'file' => [
                'debug' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/debug/{Y-m/d/Y-m-d-H}.log',
                'info' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/info/{Y-m/d/Y-m-d-H}.log',
                'notice' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/notice/{Y-m/d/Y-m-d-H}.log',
                'warning' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/warning/{Y-m/d/Y-m-d-H}.log',
                'error' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/error/{Y-m/d/Y-m-d-H}.log',
                'critical' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/critical/{Y-m/d/Y-m-d-H}.log',
                'alert' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/alert/{Y-m/d/Y-m-d-H}.log',
                'emergency' => BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/logs/emergency/{Y-m/d/Y-m-d-H}.log'
            ]
        ]
    ]
];