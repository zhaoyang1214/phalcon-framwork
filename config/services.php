<?php

// 引入配置文件
$config = require_once BASE_PATH . 'config/config_' . NOW_ENV . '.php';

// 引入路由规则
$routerRules = require_once BASE_PATH . 'config/routers.php';

use Phalcon\Config;
use Phalcon\DI;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Php as ViewEnginePhp;
use Phalcon\Mvc\View\Engine\Volt as ViewEngineVolt;
use PhalconPlugins\Dispatcher as DIspatcherPlugin;
use PhalconExtensions\Volt as VoltExtension;
use PhalconHelpers\Filesystem as FilesystemHelper;
use PhalconHelpers\Arr as ArrHelper;
use Phalcon\Logger\Adapter\File as LoggerAdapterFile;
use Phalcon\Logger\Formatter\Line as LoggerFormatterLine;
use Phalcon\Db\Profiler;
use Phalcon\Db\Adapter\Pdo\Mysql;
use PhalconPlugins\DbProfiler;

$di = new FactoryDefault();

/**
 * 注册配置服务
 */
$di->setShared('config', function () use ($config) {
    return new Config($config);
});

/**
 * 注册性能分析组件
 */
$di->setShared('profiler', function () {
    return new Profiler();
});

/**
 * 注册数据库服务
 */
$di->setShared('db', function () {
    $dbConfig = $this->getConfig()->services->db->toArray();
    $mysql = new Mysql($dbConfig['mysql']);
    $eventsManager = new EventsManager();
    $eventsManager->attach('db', new DbProfiler());
    $mysql->setEventsManager($eventsManager);
    return $mysql;
});

/**
 * 注册调度器服务
 */
$di->setShared('dispatcher', function () {
    $dispatcherConfig = $this->getConfig()->services->dispatcher;
    $dispatcher = new Dispatcher();
    if (isset($dispatcherConfig->module_default_namespaces)) {
        $dispatcher->setDefaultNamespace($dispatcherConfig->module_default_namespaces);
    }
    $eventsManager = new EventsManager();
    $dispatcherPlugin = new DIspatcherPlugin($dispatcherConfig->toArray());
    $eventsManager->attach('dispatch', $dispatcherPlugin);
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});

/**
 * 注册路由服务
 */
$di->setShared('router', function () use ($routerRules) {
    $router = new Router();
    // 自动删除末尾斜线
    $router->removeExtraSlashes(true);
    foreach ($routerRules as $k => $v) {
        $router->add($k, $v);
    }
    return $router;
});

/**
 * 注册视图引擎volt服务
 */
$di->setShared('viewEngineVolt', function (View $view, DI $di) {
    $voltConfig = $this->getConfig()->services->view_engine_volt->toArray();
    $voltConfig = ArrHelper::camelize($voltConfig);
    $viewEngineVolt = new ViewEngineVolt($view, $di);
    $voltConfig['compiledPath'] = isset($voltConfig['compiledPath']) ? FilesystemHelper::dirFormat($voltConfig['compiledPath']) : BASE_PATH . 'runtime/' . DEFAULT_MODULE . '/compiled/volt' . DS;
    $mkdirRes = FilesystemHelper::mkdir($voltConfig['compiledPath']);
    if (! $mkdirRes) {
        throw new \Exception('创建目录 ' . $voltConfig['compiledPath'] . ' 失败');
    }
    $viewEngineVolt->setOptions($voltConfig);
    // 获取编译器对象
    $compiler = $viewEngineVolt->getCompiler();
    // 添加扩展
    $compiler->addExtension(new VoltExtension());
    return $viewEngineVolt;
});

/**
 * 注册视图引擎php服务
 */
$di->setShared('viewEnginePhp', function (View $view, DI $di) {
    $viewEnginePhp = new ViewEnginePhp($view, $di);
    return $viewEnginePhp;
});

/**
 * 注册视图服务
 */
$di->set('view', function () {
    $viewConfig = $this->getConfig()->services->view;
    $view = new View();
    if ($viewConfig->disable) {
        $view->disable();
    } else {
        // 设置视图路径
        $view->setViewsDir($viewConfig->view_path);
        $engines = $viewConfig->engines->toArray();
        foreach ($engines as $k => $v) {
            if ($v === false) {
                unset($engines[$k]);
            }
        }
        // 注册视图引擎
        $view->registerEngines($engines);
        $disableLevelConfig = $viewConfig->disable_level;
        // 关闭渲染级别
        $disableLevel = [];
        foreach ($disableLevelConfig as $k => $v) {
            // 设置了就代表disableLevel，与设置的true无关
            if ($v) {
                switch ($k) {
                    case 'level_action_view':
                        $disableLevel[View::LEVEL_ACTION_VIEW] = true;
                        break;
                    case 'level_before_template':
                        $disableLevel[View::LEVEL_BEFORE_TEMPLATE] = true;
                        break;
                    case 'level_layout':
                        $disableLevel[View::LEVEL_LAYOUT] = true;
                        break;
                    case 'level_after_template':
                        $disableLevel[View::LEVEL_AFTER_TEMPLATE] = true;
                        break;
                    case 'level_main_layout':
                        $disableLevel[View::LEVEL_MAIN_LAYOUT] = true;
                        break;
                }
            }
        }
        $view->disableLevel($disableLevel);
    }
    return $view;
});

/**
 * 注册日志服务
 */
$di->set('logger', function (string $file = 'info') {
    $loggerConfig = $this->getConfig()->services->logger;
    $linConfig = $loggerConfig->line;
    $loggerFormatterLine = new LoggerFormatterLine($linConfig->format, $linConfig->date_format);
    
    $fileConfig = $loggerConfig->file;
    if (array_key_exists($file, $fileConfig->toArray())) {
        $file = $fileConfig->$file;
    } else if (empty($file)) {
        $file = $fileConfig->info;
    }
    $file = FilesystemHelper::dirFormat($file);
    $dir = dirname($file);
    $mkdirRes = FilesystemHelper::mkdir($dir);
    if (! $mkdirRes) {
        throw new \Exception('创建目录 ' . $dir . ' 失败');
    }
    $loggerAdapterFile = new LoggerAdapterFile($file);
    $loggerAdapterFile->setFormatter($loggerFormatterLine);
    return $loggerAdapterFile;
});

return $di;

