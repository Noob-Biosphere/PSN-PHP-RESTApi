<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateInArray
 * @package restphp\validate
 */
class RestValidateInArray {
    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws RestException
     */
    public static function validate($doc, $propName, $value) {
        $strRule = RestValidate::getRuleStr($doc);
        $arrRule = explode(",", $strRule);
        $valueRange = array();
        $message = "";
        foreach ($arrRule as $rule) {
            $arrItem = explode("=", $rule);
            if (!isset($arrItem[1])) {
                continue;
            }
            $name = trim($arrItem[0]);
            $val = trim($arrItem[1]);
            if ($name == 'value') {
                $valueRange = self::parseValue($val);
            } else if ($name == 'message') {
                $message = $val;
            }
        }

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_RANGE_ERROR . ']') : $message;
        $message = RestValidate::clearMessageBoundary($message);

        if (!in_array($value, $valueRange)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_RANGE_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }
    }

    /**
     * 格式化数据.
     * @param $value
     * @return array
     */
    private static function parseValue($value) {
        if (RestStringUtils::startWith($value, "[")) {
            $value = substr($value, 1);
        }
        if (RestStringUtils::endWith($value, "]")) {
            $value = substr($value, 0, strlen($value) - 1);
        }

        $arrValueFinal = array();
        $arrValue = explode("|", $value);
        foreach ($arrValue as $valueItem) {
            $valueItem = RestValidate::clearMessageBoundary($valueItem);
            $arrValueFinal[] = $valueItem;
        }
        return $arrValueFinal;
    }
}