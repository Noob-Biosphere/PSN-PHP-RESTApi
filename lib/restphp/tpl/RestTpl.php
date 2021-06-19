<?php
/**
 * Created by zj.
 * User: zj
 * Date: 2019/8/5 0005
 * Time: 上午 10:30
 */
namespace restphp\tpl;

use restphp\biz\RestErrorCode;
use restphp\exception\RestException;

class RestTpl {
    public $caching=false;
    public $cache_ext = "_cache.php";
    public $cache_dir = '';
    public $template_dir = '';

    function __construct(){
        $this->cache_dir = $GLOBALS['_EVN_PARAM']['TPL_CACHE_DIR'];
        $this->template_dir = $GLOBALS['_EVN_PARAM']['TPL_DIR'];
    }

    /**
     * 控制器方法：包含（输出）模板文件.
     * @param $file
     * @throws RestException
     */
    public function display($file){
        $cache_file = $this->cache_dir.DIRECTORY_SEPARATOR.str_replace(".", "_", str_replace("/", "_", $file)).$this->cache_ext;

        if($this->caching){
            if(file_exists($cache_file)) require_once($cache_file);
            else{
                $content = $this->explode_display($file);
                self::make_cache_file($cache_file, $content);
                require_once($cache_file);
            }
        }else{
            $content = $this->explode_display($file);
            self::make_cache_file($cache_file, $content);
            require_once($cache_file);
        }
    }

    /**
     * 控制器方法：获取模板文件内容.
     * @param $file
     * @return bool|string
     * @throws RestException
     */
    public function explode_display($file){
        $tpl_file = $this->template_dir.DIRECTORY_SEPARATOR.$file;
        if(!file_exists($tpl_file)) {
            throw new RestException( "Fatal error: template file is not exists: {$file}, real path {$tpl_file}", RestErrorCode::TPL_FILE_NOT_FOUND);
        }
        $content = file_get_contents($tpl_file);
        return $content;
    }

    public static $var = array();

    /**
     * 控制器方法：注册模板变量.
     * @param $paramname
     * @param $paramvalue
     */
    public function assign($paramname, $paramvalue){
        if(is_string($paramname)){
            self::$var[$paramname] = $paramvalue;
        }
    }

    /**
     * 模板方法：获取控制器注册的变量。
     * @param $strParamName
     * @return mixed|null
     */
    public static function get($strParamName){
        return isset(self::$var[$strParamName]) ? self::$var[$strParamName] : null;
    }

    public static $var_fuc = array();

    /**
     * 控制器方法：注册供模板可直接调用的方法.
     * @param $paramname
     * @param $paramvalue
     */
    public function register_function($paramname, $paramvalue){
        if(is_string($paramname)&&function_exists($paramvalue)){
            self::$var_fuc[$paramname] = $paramvalue;
        }
    }

    /**
     * 模板方法：获取控制器注册的方法.
     * @param $strFuncName
     * @return mixed|null
     */
    public static function fuc($strFuncName){
        return isset(self::$var_fuc[$strFuncName]) ? self::$var_fuc[$strFuncName] : null;
    }

    public static $var_obj = array();

    /**
     * 控制器方法：注册对象.
     * @param $paramname
     * @param $paramvalue
     */
    public function register_object($paramname, $paramvalue){
        if(is_string($paramname)&&is_object($paramvalue)){
            self::$var_obj[$paramname] = $paramvalue;
        }
    }

    /**
     * 模板方法：获取控制器注册的方法对像.
     * @param $strObjName
     * @return mixed|null
     */
    public static function obj($strObjName){
        return isset(self::$var_obj[$strObjName]) ? self::$var_obj[$strObjName] : null;
    }

    /**
     * 查勘板方法：生成缓存文件.
     * @param $cache_file
     * @param $content
     * @return bool
     */
    public static function make_cache_file($cache_file, $content){
        @file_put_contents($cache_file, $content);
        return true;
    }

    /**
     * 模板方法：die with msg.
     * @param $msg
     */
    public static function stop($msg){
        die($msg);
    }

    /**
     * 模板方法：加载模板文件.
     * @param $p_strFile
     * @throws RestException
     */
    public static function load($p_strFile){
        $strFile = $GLOBALS['_EVN_PARAM']['TPL_DIR'].DIRECTORY_SEPARATOR.$p_strFile;
        if(file_exists($strFile)) {
            include_once($strFile);
        } else {
            throw new RestException("Fatal error: include file is not exists: {$p_strFile}, real file: {$strFile}", RestErrorCode::TPL_FILE_NOT_FOUND);
        }
    }

    /**
     * 模板方法：获取默认uri路径.
     * @param $strFile
     * @param $ext
     * @return string
     */
    public static function getTplUri($strFile = '', $ext = '.php') {
        return "/" . $GLOBALS['_EVN_PARAM']['TPL_DIR'] . ('' == $strFile ? '' : ('/' . $strFile . $ext));
    }

    /**
     * 获取模板根目录.
     * @return string
     */
    public static function getTplRoot() {
        return "/" . $GLOBALS['_EVN_PARAM']['TPL_DIR'];
    }

    /**
     * 获取环镜变量.
     */
    public static function getEnv() {
        return PROJ_ENV;
    }
}