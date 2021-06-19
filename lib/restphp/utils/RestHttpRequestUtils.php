<?php
/**
 * Created by zj.
 * User: zj
 * Date: 2019/5/29 0029
 * Time: 上午 9:54
 */

namespace restphp\utils;

use restphp\biz\PageConstant;
use restphp\biz\PageParam;
use restphp\http\RestHttpRequest;

class RestHttpRequestUtils {
    /**
     * 获取当前主机头.
     * @return string
     */
    public static function getHost() {
        //var_dump($_SERVER);die();
        $strServerName = RestHttpRequest::getServer("SERVER_NAME");
        if (RestStringUtils::isBlank($strServerName) || "_" == $strServerName) {
            $strServerName = RestHttpRequest::getServer("SERVER_ADDR");
        }
        $strPort = RestHttpRequest::getServer("SERVER_PORT");
        return $strServerName . ("80" == $strPort ? "" : ":{$strPort}");
    }

    /**
     * 获取uri.
     */
    public static function getUri() {
        $strUri = '/';
        if (RestHttpRequest::getServer('REQUEST_URI') != null) {
            $strUri = RestHttpRequest::getServer('REQUEST_URI');
        }
        if (RestHttpRequest::getServer('HTTP_X_ORIGINAL_URL') != null) {
            $strUri = RestHttpRequest::getServer('HTTP_X_ORIGINAL_URL');
        }
        if (strpos($strUri, '?') > -1) {
            $strUri = substr($strUri, 0, strpos($strUri, '?'));
        }
        return $strUri;
    }

    /**
     * 获取 Request scheme.
     * @return string
     */
    public static function getScheme() {
        return RestHttpRequest::getServer('REQUEST_SCHEME');
    }

    /**
     * 获取http method
     */
    public static function getMethod() {
        return RestHttpRequest::getServer("REQUEST_METHOD");
    }

    /**
     * 获取分页参数.
     * @deprecated 新版迁移到RestHttpRequest类中.
     * @return PageParam.
     */
    public static function getPageParam() {
        $nPage = RestHttpRequest::getRequest("page");
        $nSize = RestHttpRequest::getRequest("size");
        $offsetId = RestHttpRequest::getRequest("offsetId", "");
        if (RestStringUtils::isBlank($nPage)) {
            $nPage = 1;
        }
        if (RestStringUtils::isBlank($nSize)) {
            $nSize = PageConstant::PAGE_SIZE;
        }
        if ($nSize > PageConstant::PAGE_MAX) {
            $nSize = PageConstant::PAGE_MAX;
        }

        $pageParam = new PageParam();
        $pageParam->setPage($nPage);
        $pageParam->setSize($nSize);
        $pageParam->setOffsetId($offsetId);
        return $pageParam;
    }
}