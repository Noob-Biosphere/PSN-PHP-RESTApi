<?php
namespace restphp\driver;

use restphp\utils\RestReflection;
use restphp\utils\RestStringUtils;

/**
 * Class RestRepositoryHelper
 * @package restphp\driver
 */
final class RestRepositoryHelper {
    /**
     * 获取主键字段。
     * @param $classInstance
     * @return array
     * @throws \ReflectionException
     */
    public static function getPrimaryRule($classInstance) {
        $reflection = new \ReflectionClass($classInstance);
        $properties = $reflection->getProperties();
        $arrCheck = array();
        foreach ($properties as $propItem) {
            $name = $propItem->getName();
            $getterName = RestReflection::getGetterName($name);
            if (!$reflection->hasMethod($getterName)) {
                continue;
            }
            $val = $classInstance->$getterName();
            $doc = $propItem->getDocComment();

            $arrDoc = RestReflection::clearDocToArr($doc);
            foreach ($arrDoc as $item) {
                if (RestStringUtils::startWith($item, '@primary') && !RestStringUtils::isBlank($val)) {
                    return array(self::getPropColumn($name) => $val);
                }
            }
        }
        return $arrCheck;
    }

    /**
     * 获取property对应数据库字段名.
     * @param $propName
     * @return false|string
     */
    public static function getPropColumn($propName) {
        $firstChar = substr($propName, 0, 1);
        if ('_' == $firstChar) {
            $propName = substr($propName, 1);
            return self::getPropColumn($propName);
        }
        return $propName;
    }
}