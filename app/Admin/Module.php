<?php
/**
 * @desc 模块配置
 */
namespace App\Admin;

use Phalcon\DiInterface;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Config\Adapter\Php as ConfigAdapterPhp;

class Module implements ModuleDefinitionInterface
{

    // 模块配置文件目录
    private static $_configPath = __DIR__ . '/config/config_' . NOW_ENV . '.php';

    public function registerAutoloaders(DiInterface $di = NULL)
    {}

    public function registerServices(DiInterface $di)
    {
        // 这里可以注册和重写服务
        // 注册配置文件服务,合并主配置和模块配置
        $this->registerConfigService($di);
    }

    /**
     * 注册配置服务
     */
    private function registerConfigService(DiInterface $di)
    {
        $config = $di->getConfig();
        $di->setShared('config', function () use ($config) {
            $moduleConfigPath = self::$_configPath;
            if (is_file($moduleConfigPath)) {
                $override = new ConfigAdapterPhp($moduleConfigPath);
                $config->merge($override);
            }
            return $config;
        });
    }
}