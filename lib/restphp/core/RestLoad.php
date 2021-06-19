<?php
namespace restphp\core;

use restphp\biz\RestErrorCode;
use restphp\exception\RestException;

/**
 * 自动加载
 * @author sofical
 * @date 2017-03-17
 * @package restphp\core
 */
class RestLoad {
    public static function load($strName) {
        self::$arrMap[] = array(
            'tag' => 'restphp',
            'dir' => (DIR_RESTPHP . DIRECTORY_SEPARATOR)
        );
        self::$arrMap[] = array(
            'tag' => '',
            'dir' => (defined('DIR_LIB') ? DIR_LIB : 'lib') . DIRECTORY_SEPARATOR
        );
        if (!self::_loadRestphp($strName)) {
            throw new RestException('cloud not load class:' . $strName, RestErrorCode::CODE_FILE_LOAD_ERROR);
        }// or die('cloud not load class:' . $strName);
    }

    private static $arrMap = array(
    );

    public static function _loadRestphp($strName) {
        foreach (self::$arrMap as $arrRoute) {
            if (substr($strName, 0, strlen($arrRoute['tag'])) == $arrRoute['tag']) {
                $filePath = $arrRoute['dir'] . substr($strName, strlen($arrRoute['tag'])) . '.php';
                $filePath = str_replace("\\", DIRECTORY_SEPARATOR, $filePath);
                $filePath = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $filePath);
                if (file_exists($filePath)) {
                    include_once($filePath);
                    return true;
                }
            }
        }
        $strMaybe = $strName . '.php';
        $strMaybe = str_replace("\\", DIRECTORY_SEPARATOR, $strMaybe);
        if (file_exists($strMaybe)) {
            include_once($strMaybe);
            return true;
        }
        return false;
    }
}