<?php
namespace restphp\utils;
use restphp\exception\RestException;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/23 0023
 * Time: 下午 6:01
 */
class RestClientCurlUtils {
    public static function get($url, $arrHeader = array(), $intTimeout = 30){
        if(function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,self::__formatHeader($arrHeader));
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $data = curl_exec($ch);
            curl_close($ch);
        }else{
            $data = RestClientFileGetContentUtils::get($url, $intTimeout, $arrHeader);
        }
        RestLog::writeLog("GET 请求地址【{$url}】，响应内容【{$data}】", "http.c.");
        return $data;
    }

    public static function delete($url, $arrHeader = array(), $intTimeout = 30){
        if(function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,self::__formatHeader($arrHeader));
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            $data = curl_exec($ch);
            curl_close($ch);
        }else{
            $data = RestClientUtils::delete($url, $arrHeader);
        }
        RestLog::writeLog("DELETE 请求地址【{$url}】，响应内容【{$data}】", "http.c.");
        return $data;
    }

    public static function post($url, $query, $arrHeader = array(), $inTimeout = 30){
        $query = is_string($query) ? $query : json_encode($query);
        if(function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER,self::__formatHeader($arrHeader));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $data = curl_exec($ch);
            curl_close($ch);
        }else{
            $data = RestClientFileGetContentUtils::post($url, $query, $inTimeout, $arrHeader);
        }
        RestLog::writeLog("POST 请求地址【{$url}】，请求数据【{$query}】，响应内容【{$data}】", "http.c.");
        return $data;
    }

    public static function put($url, $query, $arrHeader = array(), $inTimeout = 30){
        $query = is_string($query) ? $query : json_encode($query);
        if(function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER,self::__formatHeader($arrHeader));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            $data = curl_exec($ch);
            curl_close($ch);
        }else{
            $data = RestClientUtils::put($url, $query, $arrHeader);
        }
        RestLog::writeLog("PUT 请求地址【{$url}】，请求数据【{$query}】，响应内容【{$data}】", "http.c.");
        return $data;
    }

    public static function getV2($url, $arrHeader = array(), $intTimeout = 30) {
        if(function_exists('curl_init')){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,self::__formatHeader($arrHeader));
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $data = curl_exec($ch);
            $arrResult = self::formatV2Result($ch, $data);
            curl_close($ch);
            return $arrResult;
        }else{
            throw new RestException("Fatal error: not support curl");
        }
    }

    public static function postV2($url, $query, $arrHeader = array(), $inTimeout = 30){
        $strRequestData = "";
        if (is_array($query) && !empty($query)) {
            foreach ($query as $key=>$value) {
                $strRequestData .= (""==$strRequestData ? "" : "&") . $key . "=" . urlencode($value);
            }
        } else if (is_string($query)) {
            $strRequestData = $query;
        }
        if(function_exists('curl_init')){
            $ch = curl_init();
            $arrHeaderFinal = self::__formatHeader($arrHeader);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeaderFinal);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $strRequestData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $data = curl_exec($ch);
            $arrResult = self::formatV2Result($ch, $data);
            curl_close($ch);
            return $arrResult;
        }else{
            throw new RestException("Fatal error: not support curl");
        }
        return $data;
    }

    public static function postFile($url, $query, $arrHeader = array(), $inTimeout = 30){
        if(function_exists('curl_init')){
            $ch = curl_init();
            $arrHeaderFinal = self::__formatHeader($arrHeader);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHeaderFinal);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HEADER, true);
            $data = curl_exec($ch);
            $arrResult = self::formatV2Result($ch, $data);
            curl_close($ch);
            return $arrResult;
        }else{
            throw new RestException("Fatal error: not support curl");
        }
        return $data;
    }

    private static function formatV2Result($ch, $data) {
        $intHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $strHeader = substr($data, 0, $intHeaderSize);
        $arrHeaderSource = explode("\n", $strHeader);
        $arrHeader = array();
        $intStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $protocol = "";
        foreach ($arrHeaderSource as $strHeaderItem) {
            if (strtoupper(substr($strHeaderItem,0, 5)) == "HTTP/") {
                $protocol = $strHeaderItem;
                continue;
            }
            $strHeaderItem = trim($strHeaderItem);
            $arrHeader[] = $strHeaderItem;
        }

        $strBody = substr($data, $intHeaderSize);

        $arrData = array(
            'protocol' => $protocol,
            'status' => $intStatus,
            'header' => $arrHeader,
            'body' => $strBody
        );

        return $arrData;
    }

    private static function __formatHeader($arrHeader) {
        $arrNew = array();
        foreach ($arrHeader as $strKey=>$strValue)  {
            if (substr($strKey, 0, strlen("HTTP_")) == "HTTP_") {
                //$strKey = str_replace("HTTP_", "", $strKey);
                $strKey = substr($strKey, strlen("HTTP_"));
                $strKey = str_replace("_", "-", $strKey);
            }
            $arrNew[] = $strKey.": ".$strValue;
        }
        //$arrNew[] = "content-type: application/x-www-form-urlencoded; charset=UTF-8";
        return $arrNew;
    }
}