<?php
/**
 * 微服务化客户端
 * @author wsfuyibing <websearch@163.com>
 * @date 2017-11-01
 * @link www.uniondrug.cn
 */
namespace UniondrugServiceClient;

use Phalcon\Di\Injectable;

/**
 * 日志
 * @property \Phalcon\Logger\Adapter\File $logger
 * @package UniondrugServiceClient
 */
class Log extends Injectable
{
    /**
     * @var Result
     */
    private $result;
    private $url;
    private $duration;
    private $args;
    private $logData = "";
    private $lineComma = "\n";
    private $tableComma = "";

    private $enableAppendArgument = true;
    private $enableBacktrace = true;

    public function __construct(&$result, $url, $duration, $args)
    {
        $this->result = &$result;
        $this->url = $url;
        $this->duration = (double) sprintf('%.03f', $duration);
        $this->args = &$args;
    }

    /**
     * 保存日志
     */
    public function save()
    {
        /**
         * Log头信息
         */
        if ($this->result->hasError()) {
            $this->logData = '请求['.$this->url.']失败 - 用时['.$this->duration.']秒 - '.$this->result->getError();
        } else {
            $this->logData = '请求['.$this->url.']成功 - 用时['.$this->duration.']秒 - '.$this->result->getContents();
        }
        /**
         * 写入日志
         */
        if ($this->result->hasError()) {
            $this->enableAppendArgument && $this->appendArguments();
            if ($this->enableBacktrace){
                $exception = $this->result->getException();
                if ($exception) {
                    $this->appendException($exception);
                } else {
                    $this->appendBacktrace();
                }
            }
            $this->logger->error($this->logData);
        } else {
            $this->logger->info($this->logData);
        }
    }

    /**
     * 追加服务请求参数
     */
    private function appendArguments()
    {
        $this->logData .= $this->lineComma.$this->tableComma."请求参数: ".count($this->args)."个";
        foreach ($this->args as $key => $arg) {
            $this->logData .= $this->lineComma.$this->tableComma.$this->tableComma."#{$key} ".$this->appendArugmentsValue($arg);
        }
    }

    /**
     * 获取参数值
     *
     * @param mixed $value
     *
     * @return string
     */
    private function appendArugmentsValue($value)
    {
        $valueType = gettype($value);
        switch ($valueType) {
            case 'integer' :
            case 'float' :
            case 'double' :
                return (string) $value;
                break;
            case 'boolean' :
                return $value ? 'true' : 'false';
                break;
            case 'string' :
                return "'".$value."'";
                break;
            case 'object' :
                return get_class($value);
                break;
            case 'array' :
                return '(array) '.json_encode($value, true);
                break;
            default :
                return '('.$valueType.')';
                break;
        }
    }

    /**
     * 加入异常跟踪
     *
     * @param \Exception $exception
     */
    private function appendException(& $exception)
    {
        $this->logData .= $this->lineComma.$this->tableComma."异常跟踪:";
        foreach (explode("\n", $exception->getTraceAsString()) as $trace) {
            $trace = trim($trace);
            if ($trace !== "") {
                $this->logData .= $this->lineComma.$this->tableComma.$this->tableComma."{$trace}";
            }
        }
    }

    /**
     * 加入
     */
    private function appendBacktrace()
    {
        $traces = $this->result->getErrorTrace();
        if (is_array($traces)) {
            $this->logData .= $this->lineComma.$this->tableComma."错误跟踪:";
            foreach ($traces as $key => $trace) {
                $this->logData .= $this->lineComma.$this->tableComma.$this->tableComma."#{$key} ";
                // script
                if (isset($trace["file"], $trace["line"])) {
                    $this->logData .= $trace["file"]."(".$trace["line"]."): ";
                } else {
                    $this->logData .= "internal(0): ";
                }
                // class
                if (isset($trace['class'], $trace['type'])) {
                    $this->logData .= $trace['class'].$trace['type'];
                }
                // method
                if (isset($trace['function'])) {
                    $this->logData .= $trace['function'].'(';
                    if (isset($trace['args']) && is_array($trace['args'])) {
                        $comma = '';
                        foreach ($trace['args'] as $arg) {
                            $this->logData .= $comma.$this->appendArugmentsValue($arg);
                            $comma = ', ';
                        }
                    }
                    $this->logData .= ')';
                }
            }
        }
    }
}