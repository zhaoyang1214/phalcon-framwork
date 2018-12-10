<?php
/**
 * @desc 基础模型
 */
namespace Common;

use Phalcon\Mvc\Model;
use PhalconHelpers\Arr;
use Phalcon\Mvc\Model\Exception;
use Phalcon\Mvc\Model\Message;

class BaseModel extends Model
{

    protected static $tableName;

    protected static $tablePrefix;

    /**
     * 初始化
     */
    public function initialize()
    {
        $dbConfig = $this->getDI()->getConfig()->services->db;
        static::$tablePrefix = $dbConfig->prefix;
        $this->useDynamicUpdate($dbConfig->use_dynamic_update);
        $ormOptions = Arr::camelize($dbConfig->orm_options->toArray());
        $this->setup($ormOptions);
        $this->setSource(static::$tablePrefix . static::$tableName);
    }

    /**
     * 删除缓存
     *
     * @param string $cacheKey
     *            缓存键名
     * @return : bool
     */
    public function deleteCache(string $cacheKey)
    {
        $cache = $this->getDI()->getModelsCache();
        $deleteResult = true;
        if ($cache->exists($cacheKey)) {
            $deleteResult = $cache->delete($cacheKey);
        }
        return $deleteResult;
    }

    /**
     * 根据前缀删除模型缓存
     *
     * @param string $cachePrefix
     *            缓存前缀或键名
     * @return : bool
     */
    public function deleteCacheByPrefix(string $cachePrefix = null)
    {
        $di = $this->getDI();
        $modelsCacheConfig = $di->getConfig()->services->models_cache;
        $prefix = $modelsCacheConfig->backend->prefix ?? '';
        $cache = $di->getModelsCache();
        $keys = $cache->queryKeys($cachePrefix);
        foreach ($keys as $key) {
            $key = substr($key, strlen($prefix));
            $cache->delete($key);
        }
        return true;
    }

    /**
     * 向模型中注入错误信息
     *
     * @param string|\Phalcon\Validation\Message\Group|\Phalcon\Validation\Message|\Phalcon\Mvc\Model\Message $message
     *            错误信息内容或对象
     * @param string|array $field=null
     *            字段
     * @param string $type=null
     *            错误信息类型
     * @param \Phalcon\Mvc\ModelInterface $model=null
     *            模型
     * @param int|null $code=null
     *            错误信息提示码
     * @return : bool
     */
    public function errorMessage($message, $field = null, string $type = null, \Phalcon\Mvc\ModelInterface $model = null, int $code = null)
    {
        if (is_string($message)) {
            $this->appendMessage(new Message($message, $field, $type, $model, $code));
        } else if ($message instanceof \Phalcon\Validation\Message) {
            $this->appendMessage(new Message($message->getMessage(), $message->getField(), $message->getType(), $model, $message->getCode()));
        } else if ($message instanceof \Phalcon\Mvc\Model\Message) {
            $this->appendMessage($message);
        } else if ($message instanceof \Phalcon\Validation\Message\Group) {
            foreach ($message as $msg) {
                $this->appendMessage(new Message($msg->getMessage(), $msg->getField(), $msg->getType(), $model, $msg->getCode()));
            }
        } else {
            throw new Exception('$message参数错误');
        }
        return false;
    }

    /**
     * 获取验证规则
     *
     * @param array $indexs
     *            验证规则的数组索引，为[]时获取全部规则
     * @param bool $isMustCheck
     *            是否必须验证
     * @return : array
     */
    public function getRules(array $indexs = [], bool $isMustCheck = true)
    {
        $rules = static::rules();
        if (! is_array($rules)) {
            throw new Exception('数组规则错误');
        }
        if (empty($indexs)) {
            foreach ($rules as $k => $v) {
                $rules[$k][4] = $isMustCheck ? 1 : 0;
            }
            return $rules;
        }
        $returnRules = [];
        foreach ($indexs as $index) {
            if (! isset($rules[$index])) {
                throw new Exception("索引为{$index}的规则不存在");
            }
            $returnRules[$index] = $rules[$index];
            $returnRules[$index][4] = $isMustCheck ? 1 : 0;
        }
        return $returnRules;
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::$_tableName;
    }

    /**
     * 清除单个模型元数据
     *
     * @param string $tableName
     *            对于扩展表和表单创建的表需要传递表名
     * @author ZhaoYang
     *         @date 2018年8月13日 上午10:16:58
     */
    public function clearModelsMetadata(string $tableName = null)
    {
        $namespaceToArr = explode('\\', static::class);
        $className = end($namespaceToArr);
        $modelsMetadataConfig = $this->getDI()->getConfig()->services->models_metadata->options;
        foreach (MODULE_ALLOW_LIST as $v) {
            $class = APP_NAMESPACE . '\\' . ucfirst($v) . '\\Models\\' . $className;
            if (class_exists($class)) {
                $model = new $class($tableName);
                if ($modelsMetadataConfig->adapter == 'files') {
                    $prepareVirtualPath = strtolower(str_replace('\\', '_', $class));
                    $mapFile = $modelsMetadataConfig->meta_data_dir . 'map-' . $prepareVirtualPath . '.php';
                    $metaFile = $modelsMetadataConfig->meta_data_dir . 'meta-' . $prepareVirtualPath . '-' . $model->getSource() . '.php';
                    if (is_file($mapFile)) {
                        @unlink($mapFile);
                    }
                    if (is_file($metaFile)) {
                        @unlink($metaFile);
                    }
                } else {
                    $modelsMetadata = $model->getModelsMetaData();
                    $modelsMetadata->readMetaData($model);
                    $modelsMetadata->reset();
                }
            }
        }
    }
}