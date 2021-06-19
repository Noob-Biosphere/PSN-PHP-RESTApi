<?php
namespace restphp\utils;

class RestSqlServerHelper {
    private static $_getFilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private static $_postFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private static $_cookieFilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

    /**
     * 是否有疑似注入SQL.
     * 参考来源：https://www.cnblogs.com/milantgh/p/3673838.html
     * @param $strValue
     * @return bool
     */
    public static function hasBadSql($strValue) {
        if (preg_match("/" . self::$_getFilter . "/is", $strValue) == 1) {
            return true;
        }
        if (preg_match("/" . self::$_postFilter . "/is", $strValue) == 1) {
            return true;
        }
        if (preg_match("/" . self::$_cookieFilter . "/is", $strValue) == 1) {
            return true;
        }
    }
}