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
use PhalconValidate\Validate;
use PhalconHelpers\Arr;
use Phalcon\Session\Factory as SessionFactory;
use Phalcon\Text;
use Phalcon\Cache\Frontend\Factory as CacheFrontendFactory;
use Phalcon\Cache\Backend\Factory as CacheBackendFactory;
use Phalcon\Crypt;
use Phalcon\Http\Response\Cookies;
use Phalcon\Flash\Direct as FlashDirect;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Security;
use Phalcon\Mvc\Url;

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
    $voltConfig['compiledPath'] = FilesystemHelper::dirFormat($voltConfig['compiledPath']);
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

/**
 * 注册验证服务
 */
$di->set('validate', function () {
    return new Validate();
});

/**
 * 注册session服务
 */
$di->setShared('session', function () {
    $sessionConfig = $this->getConfig()->services->session;
    $backendConfig = $this->getConfig()->services->cache->backend;
    $optionsArr = $sessionConfig->options->toArray();
    if (! isset($optionsArr['adapter'])) {
        throw new \Exception('session必须设置adapter');
    }
    if (array_key_exists($optionsArr['adapter'], $backendConfig->toArray())) {
        $backendOption = clone $backendConfig->{$optionsArr['adapter']};
        $optionsArr = $backendOption->merge(new Config($optionsArr))
            ->toArray();
    }
    $optionsArr = Arr::camelize($optionsArr);
    if (version_compare(PHALCON_VERSION, '3.2.0', '>')) {
        $session = SessionFactory::load($optionsArr);
    } else {
        $adapterClassName = 'Phalcon\\Session\\Adapter\\' . Text::camelize($optionsArr['adapter']);
        $session = new $adapterClassName($optionsArr);
    }
    $sessionConfig->auto_start && $session->start();
    return $session;
});

/**
 * 注册加密服务
 */
$di->set('crypt', function (string $key = null, int $padding = null, string $cipher = null) {
    $cryptConfig = $this->getConfig()->services->crypt;
    $crypt = new Crypt();
    if (! empty($cryptConfig->key) || ! empty($padding)) {
        $crypt->setKey($key ?? $cryptConfig->key);
    }
    if (! empty($cryptConfig->padding) || ! empty($key)) {
        $crypt->setPadding($padding ?? $cryptConfig->padding);
    }
    if (! empty($cryptConfig->cipher) || ! empty($cipher)) {
        $crypt->setCipher($cipher ?? $cryptConfig->cipher);
    }
    return $crypt;
});

/**
 * 注册cookies服务
 */
$di->set('cookies', function () {
    $cookiesConfig = $this->getConfig()->services->cookies;
    $cookies = new Cookies();
    isset($cookiesConfig->use_encryption) && $cookies->useEncryption((bool) $cookiesConfig->use_encryption);
    return $cookies;
});

/**
 * 注册缓存
 */
$di->set('cache', function (array $options = []) {
    $cacheConfig = $this->getConfig()->services->cache;
    $frontendConfig = $cacheConfig->frontend;
    if (isset($options['frontend']['adapter'])) {
        $frontendOption = new Config($options['frontend']);
        if (array_key_exists($options['frontend']['adapter'], $frontendConfig->toArray())) {
            $frontendOptionClone = clone $frontendConfig->{$options['frontend']['adapter']};
            $frontendOptionClone->merge($frontendOption);
            $frontendOption = $frontendOptionClone;
        }
    } else {
        $frontendOption = clone $frontendConfig->data;
        $frontendOption->adapter = 'data';
    }
    $frontendOption = Arr::camelize($frontendOption->toArray());
    if (version_compare(PHALCON_VERSION, '3.2.0', '>')) {
        $frontendCache = CacheFrontendFactory::load($frontendOption);
    } else {
        $frontendClassName = 'Phalcon\\Cache\\Frontend\\' . Text::camelize($frontendOption['adapter']);
        $frontendCache = new $frontendClassName($frontendOption);
    }
    $backendConfig = $cacheConfig->backend;
    if (isset($options['backend']['adapter'])) {
        $backendOption = new Config($options['backend']);
        if (array_key_exists($options['backend']['adapter'], $backendConfig->toArray())) {
            $backendOptionClone = clone $backendConfig->{$options['backend']['adapter']};
            $backendOptionClone->merge($backendOption);
            $backendOption = $backendOptionClone;
        }
    } else {
        $backendOption = clone $backendConfig->file;
        $backendOption->adapter = 'file';
    }
    if ($backendOption->adapter == 'file') {
        if (empty($dir = $backendOption->cache_dir)) {
            throw new \Exception('缓存目录不能为空');
        }
        $dir = FilesystemHelper::dirFormat($dir);
        $mkdirRes = FilesystemHelper::mkdir($dir);
        if (! $mkdirRes) {
            throw new \Exception('创建目录 ' . $dir . ' 失败');
        }
    }
    $backendOption = Arr::camelize($backendOption->toArray());
    if (version_compare(PHALCON_VERSION, '3.2.0', '>')) {
        $backendOption['frontend'] = $frontendCache;
        $backendCache = CacheBackendFactory::load($backendOption);
    } else {
        $backendClassName = 'Phalcon\\Cache\\Backend\\' . Text::camelize($backendOption['adapter']);
        $backendCache = new $backendClassName($frontendCache, $backendOption);
    }
    return $backendCache;
});

/**
 * 注册 modelsMetadata服务
 */
$di->setShared('modelsMetadata', function () {
    $modelsMetadataConfig = $this->getConfig()->services->models_metadata;
    $backendConfig = $this->getConfig()->services->cache->backend;
    $optionsArr = $modelsMetadataConfig->options->toArray();
    if (! isset($optionsArr['adapter'])) {
        throw new \Exception('modelsMetadata必须设置adapter');
    }
    if (array_key_exists($optionsArr['adapter'], $backendConfig->toArray())) {
        $backendOption = clone $backendConfig->{$optionsArr['adapter']};
        $optionsArr = $backendOption->merge(new Config($optionsArr))
            ->toArray();
    }
    if ($optionsArr['adapter'] == 'files') {
        if (empty($optionsArr['meta_data_dir'])) {
            throw new \Exception('缓存目录不能为空');
        }
        $dir = FilesystemHelper::dirFormat($optionsArr['meta_data_dir']);
        $mkdirRes = FilesystemHelper::mkdir($dir);
        if (! $mkdirRes) {
            throw new \Exception('创建目录 ' . $dir . ' 失败');
        }
    }
    $optionsArr = Arr::camelize($optionsArr);
    $modelsMetadataClassName = 'Phalcon\\Mvc\\Model\\MetaData\\' . Text::camelize($optionsArr['adapter']);
    $modelsMetadata = new $modelsMetadataClassName($optionsArr);
    return $modelsMetadata;
});

/**
 * 注册modelsCache服务
 */
$di->set('modelsCache', function (array $options = []) {
    $modelsCacheConfig = clone $this->getConfig()->services->models_cache;
    ! empty($options) && $modelsCacheConfig->merge(new Config($options));
    $options = $modelsCacheConfig->toArray();
    $modelsCache = $this->get('cache', [
        $options
    ]);
    return $modelsCache;
});

/**
 * 注册视图缓存
 */
$di->set('viewCache', function (array $options = []) {
    $viewCacheConfig = clone $this->getConfig()->services->view_cache;
    ! empty($options) && $viewCacheConfig->merge(new Config($options));
    $options = $viewCacheConfig->toArray();
    $viewCache = $this->get('cache', [
        $options
    ]);
    return $viewCache;
});

/**
 * 注册数据缓存
 */
$di->set('dataCache', function (array $options = []) {
    $dataCacheConfig = clone $this->getConfig()->services->data_cache;
    ! empty($options) && $dataCacheConfig->merge(new Config($options));
    $options = $dataCacheConfig->toArray();
    $dataCache = $this->get('cache', [
        $options
    ]);
    return $dataCache;
});

/**
 * 注册url服务
 */
$di->setShared('url', function () {
    $urlConfig = $this->getConfig()->services->url;
    $url = new Url();
    $urlConfig->base_uri && $url->setBaseUri($urlConfig->base_uri);
    $urlConfig->static_base_uri && $url->setStaticBaseUri($urlConfig->static_base_uri);
    $urlConfig->base_path && $url->setBasePath($urlConfig->base_path);
    return $url;
});

/**
 * 注册flash服务
 */
$di->set('flash', function () {
    $flashConfig = $this->getConfig()->services->flash;
    $flashDirect = new FlashDirect($flashConfig->css_classes->toArray());
    $flashDirect->setAutoescape($flashConfig->autoescape);
    $flashDirect->setAutomaticHtml($flashConfig->automatic_html);
    $flashDirect->setImplicitFlush($flashConfig->implicit_flush);
    return $flashDirect;
});

/**
 * 注册flashSession服务
 */
$di->set('flashSession', function () {
    $flashSessionConfig = $this->getConfig()->services->flash_session;
    $flashSession = new FlashSession($flashSessionConfig->css_classes->toArray());
    $flashSession->setAutoescape($flashSessionConfig->autoescape);
    $flashSession->setAutomaticHtml($flashSessionConfig->automatic_html);
    $flashSession->setImplicitFlush($flashSessionConfig->implicit_flush);
    return $flashSession;
});

/**
 * 注册安全服务
 */
$di->set('security', function () {
    $securityConfig = $this->getConfig()->services->security;
    $security = new Security();
    $securityConfig->random_bytes && $security->setRandomBytes($securityConfig->random_bytes);
    $securityConfig->default_hash && $security->setDefaultHash($securityConfig->default_hash);
    $securityConfig->work_factor && $security->setWorkFactor($securityConfig->work_factor);
    return $security;
});

return $di;

