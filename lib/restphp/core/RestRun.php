<?php
namespace restphp\core;

use php\exception\BaException;
use restphp\exception\RestException;
use restphp\http\RestHttpRequest;
use restphp\http\RestHttpResponse;
use restphp\biz\RestErrorCode;
use restphp\http\RestHttpStatus;

/**
 * 路由控制
 * @author sofical
 * @date 2017-03-17
 * @package restphp\core
 */
class RestRun{
    public static function run() {
        //异常直接捕获处理: 7 以下不支持Throwable，暂不用Throwable
        set_error_handler(function ($errno, $errStr, $errFile, $errLine, $errContext) {
            $strError = "Fatal error: (" . $errno . "): " . $errStr;
            $strError .= "<br />File: " . $errFile . " on Line (" . $errLine . ") ";
            $strError .= "<br />Detail: " . json_encode($errContext);
            throw new \Exception($strError, $errno);
        });
        try {
            self::runReal();
        } catch (\Exception $e) {
            throw new RestException($e->getMessage(), $e->getCode(), RestHttpStatus::Internal_Server_Error);
        }
    }
    public static function runReal() {
        //Http请求数据预处理
        RestHttpRequest::init();

        $strMethod = RestHttpRequest::getServer('REQUEST_METHOD');
        if ($strMethod == null) {
            self::_unknownMethod();
        }
        $strMethod = strtoupper($strMethod);

        //路由匹配
        $strUri = '/';
        if (RestHttpRequest::getServer('REQUEST_URI') != null) {
            $strUri = RestHttpRequest::getServer('REQUEST_URI');
        }
        if (RestHttpRequest::getServer('HTTP_X_ORIGINAL_URL') != null) {
            $strUri = RestHttpRequest::getServer('HTTP_X_ORIGINAL_URL');
        }

        $nUrlParamPOS = strpos($strUri, "?");
        $strUri = strpos($strUri, "?") > -1 ? substr($strUri, 0, $nUrlParamPOS) : $strUri;

        $arrFilter = isset($GLOBALS['_FILTER']) ? $GLOBALS['_FILTER'] : array();
        if (!empty($arrFilter)) {
            foreach ($arrFilter as $arrItem) {
                //兼容php7：变量函数需要先固定，不能二次读取
                $function = $arrItem['function'];
                $arrItem['class']::$function($strUri);
            }
        }

        $strMapFile = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR . $strMethod . '.php';
        $arrMap = array();
        if (file_exists($strMapFile)) {
            $arrMap = include($strMapFile);
        }
        $strMapFileV2 = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR . '_v2_' . $strMethod . '.php';
        if (file_exists($strMapFileV2)) {
            $arrMapV2 = include($strMapFileV2);
            if (is_array($arrMapV2)) {
                $arrMap = array_merge($arrMap, $arrMapV2);
            }
        }
        if (empty($arrMap)) {
            self::_notFound(array('method' => $strMethod, 'uri' => $strUri));
        }

        $strUri = str_replace('//', '/', $strUri);
        if (substr($strUri, strlen($strUri)-1, 1) == '/') {
            $strUri = substr($strUri, 0, strlen($strUri)-1);
        }
        $strUriKey = str_replace('/', '_', $strUri);

        if (isset($arrMap[$strUriKey . "_"])) {
            $strFileEnter = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR .
                $strMethod . DIRECTORY_SEPARATOR . $arrMap[$strUriKey . "_"]['filename'] . ".php";
            include($strFileEnter);
            return;
        } else {
            $strM = $strUri . '/' . RestConstant::REST_URI_SIGE_END();
            foreach($arrMap as $strKey => $arrMatchedMap) {
                if ($strKey == '_') {
                    continue;
                }
                $strR = '/' . $arrMatchedMap['preg_match'] . '/';
                preg_match($strR, $strM, $arrMatched);
                if (isset($arrMatched[0])) {
                    $arrPathParam = $arrMatchedMap['path_param'];
                    for($nPos = 1; $nPos < count($arrMatched); $nPos++) {
                        $strPathVal = str_replace('/', '', $arrMatched[$nPos]);
                        RestHttpRequest::setPathValue($arrPathParam[$nPos-1], $strPathVal);
                    }
                    $strFileEnter = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR .
                        $strMethod . DIRECTORY_SEPARATOR . $arrMatchedMap['filename'] . ".php";
                    include($strFileEnter);
                    return;
                }
            }

            $strM = $strUri . '/' . RestConstant::REST_URI_ALL_END();
            //$arrLike = array();
            foreach($arrMap as $strKey => $arrMatchedMap) {
                if ($strKey == '_') {
                    continue;
                }
                $strR = '/' . $arrMatchedMap['preg_match'] . '/';
                preg_match($strR, $strM, $arrMatched);
                //echo $strR;
                if (isset($arrMatched[0])) {
                    //var_dump($arrMatched);
                    $strReCheck = $arrMatched[count($arrMatched) - 1];
                    if (substr($strReCheck, strlen($strReCheck) - 1) == '/') {
                        $strReCheck = substr($strReCheck, 0, strlen($strReCheck)-1);
                    }
                    if ( strpos($strReCheck, '/') > -1) {
                        continue;
                    }
                    $arrPathParam = $arrMatchedMap['path_param'];
                    //$arrPathParamThis = array();
                    for($nPos = 1; $nPos < count($arrMatched); $nPos++) {
                        $strPathVal = str_replace('/', '', $arrMatched[$nPos]);
                        RestHttpRequest::setPathValue($arrPathParam[$nPos-1], $strPathVal);
                        //$arrPathParamThis[$arrPathParam[$nPos-1]] = $strPathVal;
                    }

                    $strFileEnter = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR .
                        $strMethod . DIRECTORY_SEPARATOR . $arrMatchedMap['filename'] . ".php";
                    include($strFileEnter);

                    /*$arrLike[] = array(
                        'file' => $strFileEnter,
                        'path_param' => $arrPathParamThis,
                        'r' => $strR
                    );*/
                    return;
                }
            }
        }

        self::_notFound(array('method' => $strMethod, 'uri' => $strUri));
    }

    private static function _notFound($arrArgs) {
        $strResponseBody = "the method '[" . $arrArgs['method'] . "'] of '" . $arrArgs['uri'] . "' is not Found!";
        RestHttpResponse::err($strResponseBody, RestErrorCode::URI_NOT_FOUND, '404');
    }

    private static function _unknownMethod() {
        $strResponseBody = 'method missed';
        RestHttpResponse::err($strResponseBody, RestErrorCode::UNKNOWN_METHOD, '405');
    }
}