<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateDomain
 * @package restphp\validate
 */
class RestValidateDomain {
    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws RestException
     */
    public static function validate($doc, $propName, $value) {
        $message = RestValidate::getRuleMessage($doc);

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_NOT_DOMAIN . ']') : $message;

        if (!RestStringUtils::isDomain($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOT_DOMAIN, RestHttpStatus::Bad_Request, array($propName));
        }
    }

    /**
     * 示例方法：customer模式调用入口.
     * @param $value
     * @param $message
     * @param $propName
     * @throws RestException
     */
    public static function customer($value, $message, $propName) {
        if (!RestStringUtils::isDomain($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOT_DOMAIN, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}