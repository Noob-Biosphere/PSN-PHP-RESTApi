<?php
namespace restphp\validate;

use restphp\exception\RestException;
use restphp\http\RestHttpStatus;
use restphp\utils\RestStringUtils;

/**
 * Class RestValidateNotEmpty
 * @package restphp\validate
 */
class RestValidateNotEmpty {

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

        if (null == $value || empty($value)) {
            throw new RestException($message, RestValidateConstant::ARGUMENT_NOTNULL, RestHttpStatus::Bad_Request, array($propName));
        }
    }
}