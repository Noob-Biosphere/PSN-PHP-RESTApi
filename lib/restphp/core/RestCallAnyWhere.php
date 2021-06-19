<?php
namespace restphp\core;

/**
 * Class RestCallAnyWhere
 * @package restphp\core
 */
final class RestCallAnyWhere {
    const PARAMETER_NAME_CLASS = "class";
    const PARAMETER_NAME_FUNCTION = "function";
    const PARAMETER_NAME_STDCALL = "stdcall";
    const PARAMETER_NAME_PARAMETERS = "params";
    const PARAMETER_NAME_CONSTRUCT_PARAM = "construct";

    private static $_param_not_set = '__RESTPHP_NO_SET';

    /**
     * @param $arrConfig array(
     *                      'class' => '\php\service\upload\UploadRightCheckService',
     *                      'function' => 'check',
     *                      'stdcall' => 'static',
     *                      'params' => ''
     *                      )
     * @return mixed
     */
    public static function call($arrConfig) {
        $strFunction = $arrConfig[self::PARAMETER_NAME_FUNCTION];
        $params = isset($arrConfig[self::PARAMETER_NAME_PARAMETERS]) ? $arrConfig[self::PARAMETER_NAME_PARAMETERS] : self::$_param_not_set;
        if (isset($arrConfig[self::PARAMETER_NAME_CLASS])) {
            $strClass = $arrConfig[self::PARAMETER_NAME_CLASS];
            $stdcall = isset($arrConfig[self::PARAMETER_NAME_STDCALL]) ? $arrConfig[self::PARAMETER_NAME_STDCALL] : 'default';
            $constructParams = isset($arrConfig[self::PARAMETER_NAME_CONSTRUCT_PARAM]) ? $arrConfig[self::PARAMETER_NAME_CONSTRUCT_PARAM] : null;
            return self::classCall($strClass, $strFunction, $stdcall, $params, $constructParams);
        } else {
            return self::functionCall($strFunction, $params);
        }
    }

    /**
     * class call.
     * @param $strClass
     * @param $strFunction
     * @param $stdcall
     * @param $functionParams
     * @param null $constructParams
     * @return mixed
     */
    public static function classCall($strClass, $strFunction, $stdcall, $functionParams, $constructParams = null) {
        if ('static' == $stdcall) {
            if ($functionParams == self::$_param_not_set) {
                return $strClass::$strFunction();
            } else {
                return $strClass::$strFunction($functionParams);
            }
        } else {
            $oClass = null;
            if (null != $constructParams) {
                $oClass = new $strClass();
            } else {
                $oClass = new $strClass($constructParams);
            }
            if ($functionParams == self::$_param_not_set) {
                return $oClass->$strFunction();
            } else {
                return $oClass->$strFunction($functionParams);
            }
        }
    }

    /**
     * function call.
     * @param $strFunction
     * @param $functionParams
     * @return mixed
     */
    public static function functionCall($strFunction, $functionParams) {
        if ($functionParams == self::$_param_not_set) {
            return $strFunction();
        } else {
            return $strFunction($functionParams);
        }
    }
}