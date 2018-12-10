<?php
/** 
 * @desc 控制器基类
 */
namespace Common;

use Phalcon\Mvc\Controller;
use Phalcon\Exception;

class BaseController extends Controller
{

    /**
     * 获取get参数
     *
     * @param string $name
     *            参数名
     * @param string|array $filter
     *            过滤类型，支持string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     *            当为false时，不使用默认过滤，当为字符串例如'string,trim'时采用参数过滤 ，当为数组例如['string','trim']时采用参数+默认过滤，当为null等其他值时时采用默认过滤
     * @param mixed $defaultValue
     *            默认值
     * @param bool $noRecursive
     *            不递归过滤（即$data不作为数组循环过滤）
     * @return mixed
     */
    final protected function get(string $name = null, $filter = null, $defaultValue = null, bool $noRecursive = false)
    {
        $data = array_merge($this->request->getQuery(), $this->dispatcher->getParams());
        unset($data['_url']);
        return $this->sanitize($data, $name, $filter, $defaultValue, $noRecursive);
    }

    /**
     * 获取post参数
     *
     * @param string $name
     *            参数名
     * @param string|array $filter
     *            过滤类型，支持string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     *            当为false时，不使用默认过滤，当为字符串'string,trim'时采用参数过滤 ，当为数组['string','trim']时采用参数+默认过滤，当为null等其他值时时采用默认过滤
     * @param mixed $defaultValue
     *            默认值
     * @param bool $noRecursive
     *            不递归过滤（即$data不作为数组循环过滤）
     * @return mixed
     */
    final protected function post(string $name = null, $filter = null, $defaultValue = null, bool $noRecursive = false)
    {
        $data = $this->request->getPost();
        return $this->sanitize($data, $name, $filter, $defaultValue, $noRecursive);
    }

    /**
     * 获取post或者get参数
     *
     * @param string $name
     *            参数名
     * @param string|array $filter
     *            过滤类型，支持string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     *            当为false时，不使用默认过滤，当为字符串例如'string,trim'时采用参数过滤 ，当为数组例如['string','trim']时采用参数+默认过滤，当为null等其他值时时采用默认过滤
     * @param mixed $defaultValue
     *            默认值
     * @param bool $noRecursive
     *            不递归过滤（即$data不作为数组循环过滤）
     * @return mixed
     */
    final protected function request(string $name = null, $filter = null, $defaultValue = null, bool $noRecursive = false)
    {
        if (isset($name) && $name !== '') {
            $value = $this->post($name, $filter, null, $noRecursive) ?? $this->get($name, $filter, null, $noRecursive);
            return $value ?? $this->post($name, $filter, $defaultValue, $noRecursive);
        }
        return array_merge($this->post(null, $filter, $defaultValue, $noRecursive), $this->get(null, $filter, $defaultValue, $noRecursive));
    }

    /**
     *
     * @param string $name
     *            参数名
     * @param string|array $filter
     *            过滤类型，支持string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     *            当为false时，不使用默认过滤，当为字符串例如'string,trim'时采用参数过滤 ，当为数组例如['string','trim']时采用参数+默认过滤，当为null等其他值时时采用默认过滤
     * @param mixed $defaultValue
     *            默认值
     * @param bool $noRecursive
     *            不递归过滤（即$data不作为数组循环过滤）
     * @return mixed
     */
    final protected function json(string $name = null, $filter = null, $defaultValue = null, bool $noRecursive = false)
    {
        $data = $this->request->getJsonRawBody(true);
        if (! is_array($data)) {
            return [];
        }
        return $this->sanitize($data, $name, $filter, $defaultValue, $noRecursive);
    }

    /**
     *
     * @param array $data
     *            数据源
     * @param string $name
     *            参数名
     * @param string|array $filter
     *            过滤类型，支持string、trim、absint、int、email、float、int!、float!、alphanum、striptags、lower、upper、url、special_chars
     *            当为false时，不使用默认过滤，当为字符串例如'string,trim'时采用参数过滤 ，当为数组例如['string','trim']时采用参数+默认过滤，当为null等其他值时时采用默认过滤
     * @param mixed $defaultValue
     *            默认值
     * @param bool $noRecursive
     *            不递归过滤（即$data不作为数组循环过滤）
     * @return mixed
     */
    final protected function sanitize(array $data, string $name = null, $filter = null, $defaultValue = null, bool $noRecursive = false)
    {
        $nowFilter = null;
        if (is_string($filter) && ! empty($filter)) {
            $nowFilter = explode(',', $filter);
        } else if ($filter !== false) {
            $defaultFilter = $this->config->services->filter->default_filter;
            $defaultFilter = isset($defaultFilter) ? explode(',', $defaultFilter) : [];
            if (is_array($filter)) {
                $defaultFilter = array_unique(array_merge($filter, $defaultFilter));
            }
            if (! empty($defaultFilter)) {
                $nowFilter = $defaultFilter;
            }
        }
        if (isset($name) && $name !== '') {
            $data = $data[$name] ?? $defaultValue;
        }
        if (isset($nowFilter)) {
            if (is_array($data) && ! $noRecursive) {
                foreach ($data as $k => $v) {
                    $data[$k] = is_array($v) ? $this->sanitize($data[$k], null, $filter, $defaultValue, $noRecursive) : $this->filter->sanitize($data[$k], $nowFilter, $noRecursive);
                }
            } else {
                $data = $this->filter->sanitize($data, $nowFilter, $noRecursive);
            }
        }
        return $data;
    }

    /**
     * 转发到其他动作
     *
     * @param array|string $url
     *            'App\Home\Controllers\forward/index/a/aaa?b=bbb' or 'forward/index/a/aaa?b=bbb' or 'index?b=bbb'
     * @param array|string $vars
     *            参数 ['a'=>'aaa','b'=>'bbb'] or 'a=aaa&b=bbb'
     * @param sring $namespace
     *            命名空间
     * @return void
     */
    final protected function forward($url, $vars = null, $namespace = null)
    {
        if (is_array($url)) {
            $forward = $url;
        } else if (is_string($url)) {
            $forward = [];
            $lastbBackslash = strrpos($url, '\\');
            if ($lastbBackslash) {
                $namespace = substr($url, 0, $lastbBackslash);
            }
            if (! empty($namespace)) {
                $forward['namespace'] = $namespace;
            }
            $start = $lastbBackslash === false ? 0 : $lastbBackslash + 1;
            $rest = substr($url, $start);
            $restStrposRes = strpos($rest, '?');
            if ($rest == '' || $restStrposRes === 0) {
                throw new Exception('方法不能为空');
            }
            if ($restStrposRes === false) {
                $capname = $rest;
                $paramsString = null;
            } else {
                list ($capname, $paramsString) = explode('?', $rest, 2);
                $capname = trim($capname, '/');
                if (empty($capname)) {
                    throw new Exception('控制器或方法不能为空');
                }
            }
            $capnameArr = explode('/', $capname);
            $capnameArrCount = count($capnameArr);
            if ($capnameArrCount == 1) {
                $forward['action'] = $capnameArr[0];
            } else {
                $forward['controller'] = $capnameArr[0];
                $forward['action'] = $capnameArr[1];
                for ($i = 2; $i < $capnameArrCount; $i += 2) {
                    $forward['params'][$capnameArr[$i]] = $capnameArr[$i + 1] ?? null;
                }
            }
            if ($paramsString !== null) {
                parse_str($paramsString, $paramsArr);
                $forward['params'] = array_merge($forward['params'] ?? [], $paramsArr);
            }
        } else {
            throw new Exception('url只能为字符串或者数组');
        }
        if (is_string($vars)) {
            $vars = trim($vars, '?');
            parse_str($vars, $vars);
        }
        if (is_array($vars)) {
            $forward['params'] = array_merge($forward['params'] ?? [], $vars);
        }
        $this->dispatcher->forward($forward);
    }

    /**
     * 成功跳转
     *
     * @param string $message
     *            提示信息
     * @param string $jumpUrl
     *            跳转地址
     * @param bool $redirect
     *            是否使用response->redirect
     * @param bool $externalRedirect
     *            是否跳转到外部地址
     */
    protected function success(string $message, string $jumpUrl = null, bool $redirect = false, bool $externalRedirect = false)
    {
        if (is_null($jumpUrl)) {
            $this->flashSession->success($message);
            echo '<script>history.go(-1);</script>';
            return false;
        } else if ($jumpUrl === false) {
            $this->flashSession->error($message);
            return false;
        } else if ($redirect || strpos($jumpUrl, '://') !== false) {
            $this->flashSession->success($message);
            return $this->response->redirect($jumpUrl, $externalRedirect);
        } else {
            $this->flash->success($message);
            return $this->forward($jumpUrl);
        }
    }

    /**
     * 失败跳转
     *
     * @param string $message
     *            提示信息
     * @param string $jumpUrl
     *            跳转地址
     * @param bool $redirect
     *            是否使用response->redirect
     * @param bool $externalRedirect
     *            是否跳转到外部地址
     */
    protected function error(string $message, string $jumpUrl = null, bool $redirect = false, bool $externalRedirect = false)
    {
        if (is_null($jumpUrl)) {
            $this->flashSession->error($message);
            echo '<script>history.go(-1);</script>';
            return false;
        } else if ($jumpUrl === false) {
            $this->flashSession->error($message);
            return false;
        } else if ($redirect || strpos($jumpUrl, '://') !== false) {
            $this->flashSession->error($message);
            return $this->response->redirect($jumpUrl, $externalRedirect);
        } else {
            $this->flash->error($message);
            return $this->forward($jumpUrl);
        }
    }

    /**
     * 响应json数据
     *
     * @param string|array $responseData
     *            数据或错误提示
     * @param int $status
     *            返回状态
     * @param int $jsonOptions
     *            json选项
     * @param int $depth
     *            处理数组深度
     * @return object
     */
    protected function sendJson($responseData, int $status = 10000, int $jsonOptions = null, int $depth = 512)
    {
        $responseData = [
            'status' => $status,
            'message' => $status == 10000 ? (is_string($responseData) ? $responseData : 'success') : $responseData,
            'data' => $status == 10000 ? $responseData : ''
        ];
        return $this->response->setJsonContent($responseData, $jsonOptions, $depth)->send();
    }
}