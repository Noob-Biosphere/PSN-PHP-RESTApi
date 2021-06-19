<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateLength
 * @package restphp\validate
 */
class RestValidateLength {
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
        $min = -1;
        $max = -1;
        $message = "";
        foreach ($arrRule as $rule) {
            $arrItem = explode("=", $rule);
            if (!isset($arrItem[1])) {
                continue;
            }
            $name = trim($arrItem[0]);
            $val = trim($arrItem[1]);
            if ($name == 'min' && is_numeric($val)) {
                $min = intval($val);
            } else if ($name == 'max' && is_numeric($val)) {
                $max = intval($val);
            } else if ($name == 'message') {
                $message = $val;
            }
        }

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_LENGTH_ERROR . ']') : $message;
        $message = RestValidate::clearMessageBoundary($message);

        if ($min > -1 && strlen($value) < $min) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_LENGTH_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }
        if ($max > -1 && strlen($value) > $max) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_LENGTH_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}