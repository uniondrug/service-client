<?php
/**
 * 微服务化客户端(uiondrug service client)
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-01
 * @link www.uniondrug.cn
 */

namespace UniondrugServiceClient;

use UniondrugService\ResultReader;

/**
 * Restful请求结果
 * @package UniondrugServiceClient
 */
class Result extends ResultReader
{

    private $errorCode = 0;
    private $errorMessage = "";
    private $errorTraces = [];
    /**
     * @var \Exception
     */
    private $exception;

    private $resultContents = "";
    private $resultData = [];

    /**
     * @var int 错误类型编号
     */
    private $resultType;

    /**
     * 结果构造
     */
    public function __construct()
    {
        $this->resultType = parent::SERVICE_ERROR_TYPE;
    }

    /**
     * 读取错误码
     * @return int
     */
    public function getErrno()
    {
        return $this->errorCode;
    }

    /**
     * 读取错误原因
     * @return string
     */
    public function getError()
    {
        return $this->errorMessage;
    }

    /**
     * 错误追跟踪
     * @return array
     */
    public function getErrorTrace()
    {
        return $this->errorTraces;
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * 读取原始返回字符串
     * @return string
     */
    public function getContents()
    {
        return $this->resultContents;
    }

    /**
     * 读取数据
     * @return array
     */
    public function getData()
    {
        if ($this->isListType($this->resultType) || $this->isPagingListType($this->resultType)) {
            return $this->resultData["data"]["body"];
        }
        else {
            return $this->resultData["data"];
        }
    }

    /**
     * 读取分页
     * @return array
     */
    public function getPaging()
    {
        if ($this->isPagingListType($this->resultType)) {
            return $this->resultData["paging"];
        }
        return [];
    }

    /**
     * 是否有错误
     * @return bool
     */
    public function hasError()
    {
        return $this->errorCode !== 0;
    }

    /**
     * 设置最近的错误
     *
     * @param string $errorMessage
     * @param int    $errorCode
     */
    public function setError($errorMessage, $errorCode = 1)
    {
        $errorCode === 0 && $errorCode = -1;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        if ($this->exception) {
            $this->errorTraces = $this->exception->getTrace();
        }
        else {
            $this->errorTraces = debug_backtrace();
        }
    }

    /**
     * 设置最近的异常
     *
     * @param \Exception $e
     */
    public function setException(\Exception $e)
    {
        $this->setError(trim($e->getMessage()), $e->getCode());
        $this->exception = &$e;
    }

    /**
     * 设置HTTP返回结果
     *
     * @param \GuzzleHttp\Psr7\Response $httpResponse
     *
     * @return $this
     */
    public function setHttpResponse(& $httpResponse)
    {
        $this->initHttpResponse($httpResponse);
        return $this;
    }

    /**
     * 初始化HTTP请求结果
     *
     * @param \GuzzleHttp\Psr7\Response $httpResponse
     */
    private function initHttpResponse($httpResponse)
    {
        // 1. 读取微服务返回结果
        try {
            $contents = $httpResponse->getBody()->getContents();
        } catch(\Exception $e) {
            $this->setException($e);
            return;
        }
        // 2. 解析JSON格式
        $this->resultContents = &$contents;
        try {
            $data = \GuzzleHttp\json_decode($contents, true);
            if (!is_array($data) || !isset($data["errno"]) || !isset($data["error"])) {
                $this->setError("微服务返回的JSON格式数据解析失败");
                return;
            }
            $data["errno"] = (int) $data["errno"];
            /**
             * 业务错误检查
             */
            $this->resultData = &$data;
            if ($data["errno"] === 0) {
                // 2.1 字段data必须定义且为array/object类型
                if (!isset($data["data"]) || !is_array($data["data"])) {
                    $this->setError("微服务返回的JSON格式未按约定结构输出");
                    return;
                }
            }
            else {
                // 2.2. 微服务返回业务逻辑错误
                $this->setError($data["error"], $data["errno"]);
                return;
            }
            // 3. 对象赋值
            $this->resultData = &$data;
        } catch(\Exception $e) {
            $this->setException($e);
            return;
        }
        // 4. JSON返回类型检查
        if (isset($data["data"]["body"])) {
            $this->resultType = isset($data["data"]["paging"]) ? parent::SERVICE_PAGING_LIST_TYPE : parent::SERVICE_LIST_TYPE;
        }
        else {
            $this->resultType = parent::SERVICE_OBJECT_TYPE;
        }
    }
}