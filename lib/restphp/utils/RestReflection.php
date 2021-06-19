<?php
namespace restphp\utils;

final class RestReflection {
    /**
     * 获取property Getter 方法名.
     * @param $propName
     * @return string
     */
    public static function getGetterName($propName) {
        $firstChar = substr($propName, 0, 1);
        $propName = substr($propName, 1);
        if ('_' == $firstChar) {
            return self::getGetterName($propName);
        }
        return 'get' . strtoupper($firstChar) . $propName;
    }

    /**
     * 备注文档清洗转换成行数组.
     * @param $doc
     * @return array
     */
    public static function clearDocToArr($doc) {
        $doc = str_replace("/**", "", $doc);
        $doc = str_replace("*/", "", $doc);
        $arrDoc = explode("\n", $doc);
        $newArrDoc = array();
        foreach ($arrDoc as $line) {
            $line = trim($line);
            if (RestStringUtils::isBlank($line)) {
                continue;
            }
            $line = self::_clearStartStar($line);
            $newArrDoc[] = $line;
        }
        return $newArrDoc;
    }

    /**
     * 清洗行开头的*.
     * @param $str
     * @return mixed
     */
    private static function _clearStartStar($str) {
        if (RestStringUtils::startWith($str, "*")) {
            $str = substr($str, 1);
            return self::_clearStartStar($str);
        }
        return trim($str);
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