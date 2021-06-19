<?php
namespace restphp\utils;
use restphp\http\RestHttpMethod;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23 0023
 * Time: 下午 6:02
 */
class RestClientUtils {
    /**
     * 数据请求(powered by file_get_content).
     * @param string $method Http Method
     * @param string $url 目录地址
     * @param array $data POST数据Key-Value数组
     * @param array $header 头部信息Key-Value数组
     * @param string $contentType 请求数据格式
     * @param string $charset 请求数据编码.
     * @param integer $timeout 超时时间
     * @return string
     */
    public static function request($method, $url, $data=array(), $header=array(), $contentType = 'application/json', $charset='utf-8', $timeout=3){
        $opts = array(
            'http'=>array(
                'method'=>strtolower($method),
                'timeout'=>$timeout,
            )
        );
        if(is_array($header)&&!empty($header)){
            $header_string = "";
            foreach ($header as $key=>$value){
                $header_string.="{$key}:{$value}\r\n";
            }
            $opts['http']['header'] = $header_string;
        }
        if(is_array($data)&&!empty($data)){
            if ('application/json' == strtolower($contentType)) {
                $query_string = json_encode($data);
            } else {
                $query_string = http_build_query($data);
            }
            $query_length = strlen($query_string);
            $opts['http']['header'] = isset($opts['http']['header'])?$opts['http']['header']:'';
            $opts['http']['header'] .= "Content-length:{$query_length}\r\n";
            $opts['http']['header'] .= "Content-type: {$contentType}; charset={$charset}\r\n";
            $opts['http']['header'] .= "Connection: close";
            $opts['http']['content'] = $query_string;
        }
        $context = stream_context_create($opts);
        $body = null;
        $error = "";
        try {
            $body = @file_get_contents($url, false, $context);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        RestLog::writeLog("{$method} 请求地址【{$url}】，请求数据【" . json_encode($opts) . "】，响应内容【" .
            $body ."】，异常【{$error}】", "http.f.");
        return $body;
    }

    /**
     * GET请求.
     * @param string $url 目录地址
     * @param array $header 头部信息Key-Value数组
     * @param string $contentType 请求数据格式
     * @param string $charset 请求数据编码.
     * @param integer $timeout 超时时间
     * @return string
     */
    public static function get($url, $header=array(), $contentType = 'application/json', $charset='utf-8', $timeout=3) {
        return self::request(RestHttpMethod::GET, $url, array(), $header, $contentType, $charset, $timeout);
    }

    /**
     * POST请求.
     * @param string $url 目录地址
     * @param array $data POST数据Key-Value数组
     * @param array $header 头部信息Key-Value数组
     * @param string $contentType 请求数据格式
     * @param string $charset 请求数据编码.
     * @param integer $timeout 超时时间
     * @return string
     */
    public static function post($url, $data=array(), $header=array(), $contentType = 'application/json', $charset='utf-8', $timeout=3) {
        return self::request(RestHttpMethod::POST, $url, $data, $header, $contentType, $charset, $timeout);
    }

    /**
     * PUT请求.
     * @param string $url 目录地址
     * @param array $data POST数据Key-Value数组
     * @param array $header 头部信息Key-Value数组
     * @param string $contentType 请求数据格式
     * @param string $charset 请求数据编码.
     * @param integer $timeout 超时时间
     * @return string
     */
    public static function put($url, $data=array(), $header=array(), $contentType = 'application/json', $charset='utf-8', $timeout=3) {
        return self::request(RestHttpMethod::PUT, $url, $data, $header, $contentType, $charset, $timeout);
    }

    /**
     * DELETE请求.
     * @param string $url 目录地址
     * @param array $header 头部信息Key-Value数组
     * @param string $contentType 请求数据格式
     * @param string $charset 请求数据编码.
     * @param integer $timeout 超时时间
     * @return string
     */
    public static function delete($url, $header=array(), $contentType = 'application/json', $charset='utf-8', $timeout=3) {
        return self::request(RestHttpMethod::DELETE, $url, array(), $header, $contentType, $charset, $timeout);
    }
}