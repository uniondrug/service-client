<?php
/**
 * 微服务
 *
 * @author wsfuyibing <websearch@163.com>
 * @date   2017-12-21
 */

namespace UniondrugServiceClient;

use UniondrugService\Exception;
use UniondrugService\RequestReader;
use UniondrugService\ResponseData;
use UniondrugService\ResponsePaging;
use UniondrugService\ResponseWriter;

/**
 * 微服务的客户端入口
 * @method ResponseWriter setPaging(int $total, int $page = 1, int $pageSize = 10)
 * @method ResponseData withError(string $error, int $errno)
 * @method ResponseData withList(array $data)
 * @method ResponseData withObject(array $data)
 * @method ResponseData withPaging(array | \stdClass $data, ResponsePaging $paging = null)
 * @method ResponseData withSuccess()
 * @method RequestReader delete(string $name, string $route, array $query, array $body)
 * @method RequestReader get(string $name, string $route, array $query, array $body)
 * @method RequestReader head(string $name, string $route, array $query, array $body)
 * @method RequestReader options(string $name, string $route, array $query, array $body)
 * @method RequestReader patch(string $name, string $route, array $query, array $body)
 * @method RequestReader post(string $name, string $route, array $query, array $body)
 * @method RequestReader put(string $name, string $route, array $query, array $body)
 *
 * @package UniondrugServiceClient
 */
class Client extends \stdClass
{
    private static $requestMethods = [
        'DELETE',
        'HEAD',
        'GET',
        'PATCH',
        'POST',
        'PUT',
        'OPTIONS',
    ];
    /**
     * @var ResponseWriter
     */
    private static $response = null;

    /**
     * Magic Dispatcher
     *
     * @param string $name      方法名称
     * @param array  $arguments 方法接受的参数
     *
     * @return Request|Response
     * @throws Exception
     */
    function __call($name, $arguments)
    {
        // 1. Restful请求
        $method = strtoupper($name);
        if (in_array($method, self::$requestMethods)) {
            array_unshift($arguments, $method);

            return call_user_func_array('\UniondrugService\RequestReader::send', $arguments);
        }
        // 2. Response返回
        if (self::$response === null) {
            self::$response = new ResponseWriter();
        }
        if (method_exists(self::$response, $name)) {
            return call_user_func_array([
                self::$response,
                $name,
            ], $arguments);
        }
        // 3. 未定义
        throw new Exception("微服务的客户端未定义'{$name}'方法");
    }
}