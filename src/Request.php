<?php
/**
 * 微服务化客户端(uiondrug service client)
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-01
 * @link www.uniondrug.cn
 */

namespace UniondrugServiceClient;

use \GuzzleHttp\Client;
use Phalcon\Di;
use UniondrugService\Registry;

/**
 * 发起服务请求
 * @package UniondrugServiceClient
 */
class Request extends \stdClass
{

    private static $httpClient = null;
    private static $httpOptions = [];

    /**
     * 发送DELETE请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function delete($name, $route, $query = [], $body = [])
    {
        return $this->fetch("DELETE", $name, $route, $query, $body);
    }

    /**
     * 发送GET请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     *
     * @return Result
     */
    public function get($name, $route, $query = [])
    {
        return $this->fetch("GET", $name, $route, $query);
    }

    /**
     * 发送HEAD请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     *
     * @return Result
     */
    public function head($name, $route, $query = [])
    {
        return $this->fetch("HEAD", $name, $route, $query);
    }

    /**
     * 发送OPTIONS请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function options($name, $route, $query = [], $body = [])
    {
        return $this->fetch("OPTIONS", $name, $route, $query, $body);
    }

    /**
     * 发送PATCH请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function patch($name, $route, $query = [], $body = [])
    {
        return $this->fetch("PATCH", $name, $route, $query, $body);
    }

    /**
     * 发送POST请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function post($name, $route, $query = [], $body = [])
    {
        return $this->fetch("POST", $name, $route, $query, $body);
    }

    /**
     * 发送PUT请求
     *
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function put($name, $route, $query = [], $body = [])
    {
        return $this->fetch("PUT", $name, $route, $query, $body);
    }

    /**
     * 获取Restful结果
     *
     * @param string $method 请求类型(DELETE/GET/HEAD/OPTIONS/PATCH/POST/PUT)
     * @param string $name 服务名称(如: core)
     * @param string $route 服务路由(如: menu/index)
     * @param array  $query URL请求参数(在menu/index?a=b&c=d中出现的结果)
     * @param array  $body HTTPBody数据
     *
     * @return Result
     */
    public function fetch($method, $name, $route, $query = [], $body = [])
    {
        /**
         * 实例化HTTP请求对象
         */
        if (self::$httpClient === null) {
            self::$httpClient = new \GuzzleHttp\Client();
        }
        /**
         * 初始化结果
         */
        $begin = (float) microtime(true);
        $result = new Result();
        $loggerError = "";
        try {
            $url = Registry::getUrl($name, $route);
            $client = self::$httpClient->request($method, $url, $this->fetchOptions($query, $body));
            $result->setHttpResponse($client);
        } catch(\Exception $e) {
            $result->setException($e);
            $loggerError = $e->getTraceAsString();
        }
        /**
         * 组织日志
         */
        $loggerData  = '【用时】'.sprintf('%.04f', microtime(true) - $begin).'秒';
        $loggerData .= '【状态】'.($result->hasError() ? '失败' : '成功');
        $loggerData .= '【接口】'.$method.' '.$url;
        $loggerData .= '【参数】'.json_encode($body, true);
        $loggerData .= $result->hasError() ? '【错误】'.$result->getError() : '【返回】'.$result->getContents();
        if ($result->hasError()){
            Di::getDefault()->getLogger('error')->error($loggerData."\r\n".$loggerError);
        } else {
            Di::getDefault()->getLogger('service')->info($loggerData);
        }
        /**
         * 返回结果
         */
        return $result;
    }

    /**
     * 初始化请求参数
     *
     * @param array $query
     * @param array $body
     *
     * @return array
     */
    private function fetchOptions($query, $body)
    {
        $options = self::$httpOptions;
        /**
         * URL-Query字符串
         */
        if (is_array($query) && count($query)) {
            $options["query"] = $query;
        }
        /**
         * URL-Body字符串
         */
        if (is_array($body) && count($body)) {
            try {
                $options["body"] = \GuzzleHttp\json_encode($body, true);
            } catch(\Exception $e) {
            }
        }

        return $options;
    }
}