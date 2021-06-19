<?php
namespace restphp\validate;

use restphp\utils\RestReflection;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidate
 * @package restphp\validate.
 */
class RestValidate {
    /**
     * @var string[] 支持验证方法.
     */
    private static $_supportValidateFunction = array(
        'length',
        'notnull',
        'mobile',
        'email',
        'domain',
        'date',
        'range',
        'int',
        'ipv4',
        'ipv6',
        'inArray',
        'notEmpty',
        'customer',
    );
    /**
     * execute validate.
     * @param $classInstance 需要检查的类实例.
     * @throws \ReflectionException
     */
    public static function execute($classInstance) {
        $arrToCheck = self::_getCheckItem($classInstance);

        //没有检查项目，退出
        if (!isset($arrToCheck[0])) {
            return;
        }

        foreach ($arrToCheck as $arrCheck) {
            foreach ($arrCheck as $match) {
                $func = '__' . $match['function'];
                if ('customer' == $match['function']) {
                    self::$func($match['doc'], $match['name'], $match['val'], $classInstance);
                } else {
                    self::$func($match['doc'], $match['name'], $match['val']);
                }
            }
        }
    }

    /**
     * 参数长度校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __length($doc, $propName, $value) {
        RestValidateLength::validate($doc, $propName, $value);
    }

    /**
     * 参数不为空校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __notnull($doc, $propName, $value) {
        RestValidateNotnull::validate($doc, $propName, $value);
    }

    /**
     * 参数手机号格式校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __mobile($doc, $propName, $value) {
        RestValidateMobile::validate($doc, $propName, $value);
    }

    /**
     * 参数邮箱格式校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __email($doc, $propName, $value) {
        RestValidateEmail::validate($doc, $propName, $value);
    }

    /**
     * 参数域名格式校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __domain($doc, $propName, $value) {
        RestValidateDomain::validate($doc, $propName, $value);
    }

    /**
     * 参数日期格式校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __date($doc, $propName, $value) {
        RestValidateDate::validate($doc, $propName, $value);
    }

    /**
     * 参数整数校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __int($doc, $propName, $value) {
        RestValidateInt::validate($doc, $propName, $value);
    }

    /**
     * 参数IPV4校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __ipv4($doc, $propName, $value) {
        RestValidateIpv4::validate($doc, $propName, $value);
    }

    /**
     * 参数IPV6校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __ipv6($doc, $propName, $value) {
        RestValidateIpv6::validate($doc, $propName, $value);
    }

    /**
     * 数据大小范围.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __range($doc, $propName, $value) {
        RestValidateRange::validate($doc, $propName, $value);
    }

    /**
     * 混合值范围.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __inArray($doc, $propName, $value) {
        RestValidateInArray::validate($doc, $propName, $value);
    }

    /**
     * 判断数组不能为空.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws \restphp\exception\RestException
     */
    private static function __notEmpty($doc, $propName, $value) {
        RestValidateNotEmpty::validate($doc, $propName, $value);
    }

    /**
     * 自定义方法判断.
     * @param $doc
     * @param $propName
     * @param $value
     * @param $classInstance
     * @throws \restphp\exception\RestException
     */
    private static function __customer($doc, $propName, $value, $classInstance) {
        RestValidateCustomer::validate($doc, $propName, $value, $classInstance);
    }

    /**
     * 获取property首字母.
     * @param $propName
     * @return false|string
     */
    private static function _getGetterName($propName) {
        return RestReflection::getGetterName($propName);
    }

    /**
     * 获取需要检查的项目.
     * @param $classInstance
     * @return array
     * @throws \ReflectionException
     */
    private static function _getCheckItem($classInstance) {
        $reflection = new \ReflectionClass($classInstance);
        $properties = $reflection->getProperties();
        $arrCheck = array();
        foreach ($properties as $propItem) {
            $name = $propItem->getName();
            $getterName = self::_getGetterName($name);
            if (!$reflection->hasMethod($getterName)) {
                continue;
            }
            $val = $classInstance->$getterName();
            $doc = $propItem->getDocComment();

            $arrDoc = self::clearDocToArr($doc);
            $arrMatch = array();
            foreach ($arrDoc as $item) {
                foreach (self::$_supportValidateFunction as $function) {
                    if (RestStringUtils::startWith($item, '@' . $function)) {
                        $arrMatch[] = array(
                            'function' => $function,
                            'doc' => $item,
                            'name' => $name,
                            'val' => $val
                        );
                    }
                }
            }

            if (isset($arrMatch[0])) {
                $arrCheck[] = $arrMatch;
            }
        }
        return $arrCheck;
    }

    /**
     * 备注文档清洗转换成行数组.
     * @param $doc
     * @return array
     */
    public static function clearDocToArr($doc) {
        return RestReflection::clearDocToArr($doc);
    }

    /**
     * 获取length规则值.
     * @param $doc
     * @return false|string
     */
    public static function getRuleStr($doc) {
        $startPos = strpos($doc, '(') + 1;
        return substr($doc, $startPos, strrpos($doc, ')') - $startPos);
    }

    /**
     * 获取规则中的message值.
     * @param $doc
     * @return string
     */
    public static function getRuleMessage($doc) {
        $strRule = self::getRuleStr($doc);
        $arrRule = explode(",", $strRule);
        $message = "";
        foreach ($arrRule as $rule) {
            $arrItem = explode("=", $rule);
            if (!isset($arrItem[1])) {
                continue;
            }
            $name = trim($arrItem[0]);
            $val = trim($arrItem[1]);
            if ($name == 'message') {
                $message = $val;
            }
        }
        return self::clearMessageBoundary($message);
    }

    /**
     * 清洗message边界.
     * @param $message
     * @return false|string
     */
    public static function clearMessageBoundary($message) {
        if (RestStringUtils::startWith($message, "'") || RestStringUtils::startWith($message, '"')) {
            $message = substr($message, 1);
        }
        if (RestStringUtils::endWith($message, "'") || RestStringUtils::endWith($message, '"')) {
            $message = substr($message, 0, strlen($message) - 1);
        }
        return $message;
    }
}