<?php
/**
 * @desc 基础模型
 */
namespace App\Common;

use Phalcon\Mvc\Model;
use PhalconHelpers\Arr;

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
}