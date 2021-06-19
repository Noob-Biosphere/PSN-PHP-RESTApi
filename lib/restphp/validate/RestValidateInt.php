<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateInt
 * @package restphp\validate
 */
class RestValidateInt {
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
        $min = null;
        $max = null;
        $message = "";
        foreach ($arrRule as $rule) {
            $arrItem = explode("=", $rule);
            if (!isset($arrItem[1])) {
                continue;
            }
            $name = trim($arrItem[0]);
            $val = trim($arrItem[1]);
            if ($name == 'min') {
                $min = intval(RestValidate::clearMessageBoundary($val));
            } else if ($name == 'max') {
                $max = intval(RestValidate::clearMessageBoundary($val));
            } else if ($name == 'message') {
                $message = $val;
            }
        }

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_INT_ERROR . ']') : $message;
        $message = RestValidate::clearMessageBoundary($message);

        if (!is_int($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_INT_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }

        if (is_numeric($min) && $min > intval($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_INT_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }

        if (is_numeric($max) && $max < intval($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_INT_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}