<?php
/**
 * Created by zj.
 * User: zj
 * Date: 2019/5/24 0024
 * Time: 上午 11:23
 */
namespace restphp\i18n;

use restphp\http\RestHttpRequest;
use restphp\utils\RestStringUtils;

class RestLangUtils {
    static private $_strLangParamName = "HTTP_ACCEPT_LANGUAGE";
    /**
     * 多语言替换.
     * @param $strMessage
     * @return mixed
     */
    public static function replace($strMessage) {
        $strLang = RestHttpRequest::getServer(self::$_strLangParamName);
        if (RestStringUtils::isBlank($strLang)) {
            $strLang = "zh-cn";
        }
        $arrLangAccept = explode(",", $strLang);
        $strLang = strtolower($arrLangAccept[0]);

        //框架自带语言包
        $arrFrameLang = require (DIR_RESTPHP . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'lang.' . $strLang . '.php');

        //业务系统定义部分
        $_LANG = isset($GLOBALS['_LANG']) ? $GLOBALS['_LANG'] : array();
        $arrLang = isset($_LANG[$strLang]) ? $_LANG[$strLang] : array();

        //合并语方包，业务可覆盖框架
        $arrLang = array_merge($arrFrameLang, $arrLang);

        return str_replace(array_keys($arrLang), $arrLang, $strMessage);
    }
}