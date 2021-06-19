<?php
namespace restphp\utils;

/**
 * Class RestClassUtils
 * @package restphp\utils
 */
class RestClassUtils {
    /**
     * 从数组对象中复制对象.
     * @param $object object
     * @param $arrSource
     * @return object $object
     */
    public static function copyFromArr($object, $arrSource) {
        $objectInvoke = new \ReflectionObject($object);
        $arrProperties = $objectInvoke->getProperties();
        $arrMethodsSource = $objectInvoke->getMethods();
        $arrMethods = array();
        foreach ($arrMethodsSource as $method) {
            $arrMethods[] = $method->getName();
        }
        foreach ($arrProperties as $property) {
            $column = $property->getName();
            if ('_' == substr($column, 0, 1)) {
                $column = substr($column, 1);
            }
            $setFuncName = 'set' . strtoupper(substr($column, 0, 1)) . substr($column, 1);
            if (isset($arrSource[$column]) && in_array($setFuncName, $arrMethods)) {
                $object->$setFuncName($arrSource[$column]);
            }
        }
        return $object;
    }

    /**
     * class to array. //Azimiao(admin@azimiao.com fix @20210620)
     * @param $object
     * @return array
     */
    public static function beanToArr($object, $skipNull = true) {

        $objectInvoke = new \ReflectionObject($object);
        $arrProperties = $objectInvoke->getProperties();
        $arrData = array();
        try{
            foreach ($arrProperties as $property) {
                $column = $property->getName();
                if ('_' == substr($column, 0, 1)) {
                    $column = substr($column, 1);
                }

                $mixValue = $property->getValue($object);
                
                if ($skipNull && !is_numeric($mixValue) && null == $mixValue) {
                    continue;
                }

                $arrData[$column] = $mixValue;
            }
        }catch(\Throwable $th){
            error_log($th);
        }
        //子集中的对象转数组
        $arrData = self::subBeanToArr($arrData, $skipNull);
        return $arrData;
    }

    /**
     * 子集中的对象转数组.
     * @param $arrSource
     * @param $object
     * @param bool $skipNull
     * @return mixed
     */
    public static function subBeanToArr($arrSource, $skipNull = true) {
        foreach ($arrSource as $key=>&$value) {
            if (is_array($value)) {
                $value = self::subBeanToArr($value, $skipNull);
            } else if (is_object($value)) {
                $value = self::beanToArr($value, $skipNull);
            }
        }
        return $arrSource;
    }

    /**
     * class bean to json string.
     * @param $object
     * @return string
     */
    public static function beanToJsonString($object) {
        $arrBean = self::beanToArr($object);
        return json_encode($arrBean);
    }
}