<?php
return [
    // 应用配置
    'application' => [
        'debug' => false,
        'default_pid_path' => RUNTIME_PATH . 'cli/pid/'
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
                'dbname' => 'phalcon',
                'charset' => 'utf8mb4'
            ]
        ],
        // 文件日志,formatter常用line，adapter常用file
        'logger' => [
            'line' => [
                'format' => '[%date%][' . REQUEST_ID . '][%type%] %message%',
                'date_format' => 'Y-m-d H:i:s'
            ],
            'file' => [
                'debug' => RUNTIME_PATH . 'cli/logs/debug/{Y-m/d/Y-m-d-H}.log',
                'info' => RUNTIME_PATH . 'cli/logs/info/{Y-m/d/Y-m-d-H}.log',
                'notice' => RUNTIME_PATH . 'cli/logs/notice/{Y-m/d/Y-m-d-H}.log',
                'warning' => RUNTIME_PATH . 'cli/logs/warning/{Y-m/d/Y-m-d-H}.log',
                'error' => RUNTIME_PATH . 'cli/logs/error/{Y-m/d/Y-m-d-H}.log',
                'critical' => RUNTIME_PATH . 'cli/logs/critical/{Y-m/d/Y-m-d-H}.log',
                'alert' => RUNTIME_PATH . 'cli/logs/alert/{Y-m/d/Y-m-d-H}.log',
                'emergency' => RUNTIME_PATH . 'cli/logs/emergency/{Y-m/d/Y-m-d-H}.log'
            ]
        ],
        // 加密配置
        'crypt' => [
            // 加密秘钥
            'key' => 'cli',
            // 填充方式，默认是0（PADDING_DEFAULT），1（PADDING_ANSI_X_923）、2（PADDING_PKCS7）、3（PADDING_ISO_10126）、4（PADDING_ISO_IEC_7816_4）、5（PADDING_ZERO）、6（PADDING_SPACE）
            'padding' => '',
            // 加密方法，默认是"aes-256-cfb"
            'cipher' => ''
        ],
        // 缓存配置
        'cache' => [
            'frontend' => [
                // 数据处理方式，支持data（序列化）、json、base64、none、output、igbinary、msgpack
                'data' => [
                    'lifetime' => 86400
                ],
                'output' => [
                    'lifetime' => 86400
                ]
            ],
            'backend' => [
                // 数据缓存方式，支持memcache、file、redis、mongo、apc、apcu、libmemcached、memory、xcache
                'file' => [
                    'cache_dir' => RUNTIME_PATH . 'cache/default/',
                    // 对保存的键名进行md5加密
                    'safekey' => true,
                    'prefix' => ''
                ],
                'memcache' => [
                    'host' => 'localhost',
                    'port' => '11211',
                    'persistent' => false,
                    'prefix' => '',
                    // 默认情况下禁用对缓存键的跟踪
                    'stats_key' => ''
                ],
                'redis' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'auth' => '',
                    'persistent' => false,
                    'prefix' => '',
                    'stats_key' => '',
                    'index' => 0
                ]
            ]
        ],
        // 模型元数据缓存配置
        'models_metadata' => [
            'options' => [
                // 适配器，默认使用memory(内存),还支持apc、apcu、files、libmemcached、memcache、redis、session、xcache
                'adapter' => 'memory'
            ]
            // 'options' => [
            // 'adapter' => 'files',
            // 'meta_data_dir' => BASE_PATH . 'runtime/cache/models_metadata/'
            // ],
            // 'options' => [
            // 'adapter' => 'memcache',
            // 'unique_id' => '',
            // 'prefix' => '',
            // 'persistent' => true,
            // 'lifetime' => 3600
            // ],
            // 'options' => [
            // 'adapter' => 'memory',
            // ],
            // 'options' => [
            // 'adapter' => 'redis',
            // 'unique_id' => '',
            // 'prefix' => 'models_metadata_',
            // 'persistent' => false,
            // 'lifetime' => 3600,
            // 'stats_key' => '_PHCM_MM',
            // 'index' => 1
            // ],
            // 'options' => [
            // 'adapter' => 'session',
            // 'prefix' => '',
            // ]
        ],
        // 模型缓存配置
        'models_cache' => [
            'frontend' => [
                'adapter' => 'data',
                'lifetime' => 86400
            ],
            'backend' => [
                'adapter' => 'file',
                'safekey' => false,
                'prefix' => 'models_cache_',
                'cache_dir' => RUNTIME_PATH . 'cache/models_cache/'
            ]
        ]
    ],
    'task' => [
        'test_main' => [
            'pid_file' => RUNTIME_PATH . 'cli/pid/test_main.pid'
        ]
    ]
];