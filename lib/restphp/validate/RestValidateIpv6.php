<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateIpv6
 * @package restphp\validate
 */
class RestValidateIpv6 {
    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws RestException
     */
    public static function validate($doc, $propName, $value) {
        $message = RestValidate::getRuleMessage($doc);

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_NOT_IPV4 . ']') : $message;

        if (!RestStringUtils::isIpv6($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOT_IPV4, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}