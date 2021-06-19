<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateNotnull
 * @package restphp\validate
 */
class RestValidateNotnull {

    /**
     * 校验.
     * @param $doc
     * @param $propName
     * @param $value
     * @throws RestException
     */
    public static function validate($doc, $propName, $value) {
        $message = RestValidate::getRuleMessage($doc);

        $message = RestStringUtils::isBlank($message) ? ('[' . RestValidateConstant::ARGUMENT_NOTNULL . ']') : $message;

        if (RestStringUtils::isBlank($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOTNULL, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}