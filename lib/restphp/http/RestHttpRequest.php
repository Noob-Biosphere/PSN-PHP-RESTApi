<?php
namespace restphp\http;

use restphp\biz\PageConstant;
use restphp\biz\PageParam;
use restphp\biz\RestContentType;
use restphp\utils\RestClassUtils;
use restphp\utils\RestStringUtils;
use restphp\utils\RestXmlUtil;
use restphp\validate\RestValidate;

/**
 * Class RestHttpRequest
 * @author sofical
 * @date 2017-03-17
 * @package restphp\http
 */
final class RestHttpRequest{
    private static $_arrServer = array();
    private static $_arrRequest = array();
    private static $_arrPost = array();
    private static $_arrGet = array();
    private static $_arrPath = array();
    private static $_oBody = null;

    /**
     * Request init
     */
    public static function init() {
        self::$_arrServer = empty($_SERVER) ? self::$_arrServer : $_SERVER;
        self::$_arrRequest = empty($_REQUEST) ? self::$_arrRequest : $_REQUEST;
        //unset($_REQUEST);
        self::$_arrPost = empty($_POST) ? self::$_arrPost : $_POST;
        //unset($_POST);
        self::$_arrGet = empty($_GET) ? self::$_arrGet : $_GET;
        //unset($_GET);
        self::$_arrPath = array();

        $strBody = file_get_contents('php://input');
        if ($strBody != null) {
            $strContentType = self::getServer('CONTENT_TYPE');
            $arrType = explode(";", $strContentType);
            $strType = $arrType[0];
            switch($strType) {
                case RestContentType::JSON:
                    self::$_oBody = json_decode($strBody, true);
                    break;
                case RestContentType::XML;
                    self::$_oBody = RestXmlUtil::xmlToArr($strBody);
                    break;
                case '':
                default:
                    self::$_oBody = $strBody;
                    break;
            }
        }
    }

    /**
     * 设置路径变量
     * @param $strKey String 变量名
     * @param $strVal String 变量值
     */
    public static function setPathValue($strKey, $strVal) {
        self::$_arrPath[$strKey] = $strVal;
    }

    /**
     * 获取路径变量值
     * @param $strKey String 变量名称
     * @return null | String
     */
    public static function getPathValue($strKey) {
        return isset(self::$_arrPath[$strKey]) ? self::$_arrPath[$strKey] : null;
    }

    /**
     * 获取$_SERVER指定key对应的值
     * @param $strName String SERVER变量名
     * @return null
     */
    public static function getServer($strName) {
        return isset($_SERVER[$strName]) ? $_SERVER[$strName] : null;
    }

    /**
     * 获取所有的HTTP头.
     * @return array.
     */
    public static function getHttpHeaders() {
        $arrHead = array();
        foreach ($_SERVER as $strKey => $mixValue) {
            if (strtoupper(substr($strKey,0, 5)) == 'HTTP_') {
                $arrHead[$strKey] = $mixValue;
            }
        }
        return $arrHead;
    }

    /**
     * 获取指定的Http头值.
     * @param $name
     * @return mixed|null
     */
    public static function getHttpHeader($name) {
        $arrHeader = self::getHttpHeaders();
        $name = "HTTP_" . strtoupper($name);
        $name = str_replace('-', '_', $name);
        return isset($arrHeader[$name]) ? $arrHeader[$name] : null;
    }

    /**
     * 获取分页参数.
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

    /**
     * 获取请求内容
     * @param $toObject
     * @return toObject
     */
    public static function getBody($toObject = null, $executeCheck = false) {
        if (null == $toObject) {
            $body = self::$_oBody;
        } else {
            $body = RestClassUtils::copyFromArr($toObject, self::$_oBody);
        }
        if ($executeCheck) {
            RestValidate::execute($body);
        }
        return $body;
    }

    /**
     * 获取请求内容
     * @param $toObject
     * @return toObject
     * @deprecated
     */
    public static function getRequestBody($toObject = null, $executeCheck = false) {
        return self::getBody($toObject, $executeCheck);
    }

    /**
     * 获取$_REQUEST指定key对应的值
     * @param $strName String REQUEST变量名
     * @param null $default 默认值.
     * @return null | Object
     */
    public static function getRequest($strName, $default = null) {
        return isset(self::$_arrRequest[$strName]) ? self::$_arrRequest[$strName] : $default;
    }

    /**
     * 获取$_POST指定key对应的值
     * @param $strName String POST变量名
     * @return null | Object
     */
    public static function getPost($strName) {
        return isset(self::$_arrPost[$strName]) ? self::$_arrPost[$strName] : null;
    }

    public static function getPosts() {
        return self::$_arrPost;
    }

    /**
     * 获取$_GET指定key对应的值
     * @param $strName String GET变量名
     * @return null
     */
    public static function getGet($strName) {
        return isset(self::$_arrGet[$strName]) ? self::$_arrGet[$strName] : null;
    }

    /**
     * Request parameter to object.
     * @param $object object target object instance.
     * @return object|null
     */
    public static function getParameterAsObject($object) {
        if (!is_array(self::$_arrGet) || !is_array(self::$_arrGet)) {
            return null;
        }

        return RestClassUtils::copyFromArr($object, self::$_arrGet);
    }
}