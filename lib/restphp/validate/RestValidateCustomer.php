<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateCustomer
 * @package restphp\validate
 */
class RestValidateCustomer {
    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @param $classInstance
     * @throws RestException
     */
    public static function validate($doc, $propName, $value, $classInstance) {
        $strRule = RestValidate::getRuleStr($doc);
        $arrRule = explode(",", $strRule);
        $method = "";
        $message = "";
        foreach ($arrRule as $rule) {
            $arrItem = explode("=", $rule);
            if (!isset($arrItem[1])) {
                continue;
            }
            $name = trim($arrItem[0]);
            $val = trim($arrItem[1]);
            if ($name == 'method') {
                $method = RestValidate::clearMessageBoundary($val);
            } else if ($name == 'message') {
                $message = $val;
            }
        }

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_CUSTOMER_ERROR . ']') : $message;

        if (RestStringUtils::isBlank($method)) {
            throw new RestException('[' . RestValidateConstant::ARGUMENT_CUSTOMER_METHOD_ERROR . ']', RestValidateConstant::ARGUMENT_CUSTOMER_METHOD_ERROR, RestHttpStatus::Bad_Request, array($propName));
        }

        $method($value, $message, $propName, $classInstance);
    }
}