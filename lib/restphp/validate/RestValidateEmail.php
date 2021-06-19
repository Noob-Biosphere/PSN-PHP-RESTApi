<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateEmail
 * @package restphp\validate
 */
class RestValidateEmail {
    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws RestException
     */
    public static function validate($doc, $propName, $value) {
        $message = RestValidate::getRuleMessage($doc);

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_NOT_EMAIL . ']') : $message;

        if (!RestStringUtils::isEmail($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOT_EMAIL, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}