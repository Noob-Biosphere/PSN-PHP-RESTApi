<?php
namespace restphp\utils;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/24 0024
 * Time: 上午 9:23
 */
class RestStringUtils {
    /**
     * 是否为邮箱
     * @param string $str
     * @return boolean
     **/
    public static function isEmail($str){
        return preg_match("/^([a-za-z0-9_-])+@([a-za-z0-9_-])+(\.[a-za-z0-9_-])+/",$str);
    }

    /**
     * 是否为英文和字母
     * @param string $str
     * @return boolean
     */
    public static function isEchr($str){
        return preg_match("/^[A-Za-z0-9]+$/", $str);
    }

    /**
     * 是否为大陆手机号码.
     * @param string $str
     * @return false|int
     */
    public static function isChMobile($str){
        return preg_match("/^1\\d{10}+$/", $str);
    }

    /**
     * 是否为IPv4地址.
     * @param string $str
     * @return bool
     */
    public static function isIpv4($str) {
        if (self::isBlank($str)) {
            return false;
        }

        if (preg_match("/^(\d+)\.(\d+)\.(\d+)\.(\d+)$/", $str, $arrResult)) {
            if ($arrResult[1] < 256 && $arrResult[2] < 256 && $arrResult[3] < 256 && $arrResult[4] < 256) {
                return true;
            }
        }
        return false;
    }

    /**
     * 是否为IPV6
     * @param string $str
     * @return false|int
     * 来源：https://www.cnblogs.com/imadin/archive/2011/04/29/2032832.html
     */
    public static function isIpv6($str) {
        return preg_match('/\A
(?:
(?:
(?:[a-f0-9]{1,4}:){6}
|
::(?:[a-f0-9]{1,4}:){5}
|
(?:[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){4}
|
(?:(?:[a-f0-9]{1,4}:){0,1}[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){3}
|
(?:(?:[a-f0-9]{1,4}:){0,2}[a-f0-9]{1,4})?::(?:[a-f0-9]{1,4}:){2}
|
(?:(?:[a-f0-9]{1,4}:){0,3}[a-f0-9]{1,4})?::[a-f0-9]{1,4}:
|
(?:(?:[a-f0-9]{1,4}:){0,4}[a-f0-9]{1,4})?::
)
(?:
[a-f0-9]{1,4}:[a-f0-9]{1,4}
|
(?:(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}
(?:[0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])
)
|
(?:
(?:(?:[a-f0-9]{1,4}:){0,5}[a-f0-9]{1,4})?::[a-f0-9]{1,4}
|
(?:(?:[a-f0-9]{1,4}:){0,6}[a-f0-9]{1,4})?::
)
)\Z/ix',
            $str
        );
    }

    /**
     * 是否为英文域名.
     * @param string $str
     * @return bool|false|int
     */
    public static function isEnDomain($str) {
        if (strpos($str,"--") > -1) {
            return false;
        }
        if (strpos($str, "-.") > -1) {
            return false;
        }
        if (strpos($str, ".-") > -1) {
            return false;
        }
        return preg_match("/^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][a-zA-Z0-9]{0,62})+$/", $str);
    }

    /**
     * 是否为二级英文域名.
     * @param string $str
     * @return false|int
     */
    public static function isSecondEnDomain ($str) {
        return preg_match("/^(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/i", $str);
    }

    /**
     * 是否为中文域名.
     * @param string $str
     * @return false|int
     */
    public static function isCnDomain($str) {
        return preg_match("/^[A-Za-z0-9_\u4E00-\u9FA5]+([\.\-][A-Za-z0-9_\u4E00-\u9FA5]+)*$/", $str);
    }

    /**
     * 是否为域名.
     * @param string $str
     * @return bool
     */
    public static function isDomain($str) {
        if (self::isEnDomain($str)) {
            return true;
        }
        if (self::isSecondEnDomain($str)) {
            return true;
        }
        if (self::isCnDomain($str)) {
            return true;
        }
        return false;
    }

    /**
     * 是否为正确的SRV记录值.
     * @param $str
     * @return bool
     */
    public static function isSRV($str) {
        if (self::isBlank($str)) {
            return false;
        }
        if (strpos($str, ':') == -1) {
            return false;
        }
        $arrStr = explode(':', $str);
        if (!self::isDomain($arrStr[0])) {
            return false;
        }
        if (!self::isBlank($arrStr[1]) || !is_numeric($arrStr[1])) {
            return false;
        }
        if (strlen($arrStr[1]) > 5) {
            return false;
        }
        return true;
    }

    /**
     * 生成随机数
     * @return string
     */
    public static function randomCode() {
        $ranCharArray = array(
            '00' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz',
            '01' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
            '02' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            '03' => 'abcdefghijklmnopqrstuvwxyz',
            '11' => '0123456789',
            '21' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            '22' => 'abcdefghijklmnopqrstuvwxyz0123456789',
        );

        $argsNum = func_num_args();
        $numeric = 0;
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);

        $hash = '';
        switch($argsNum){
            case 1:
                $length = func_get_arg(0);
                if($numeric) {
                    $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
                } else {
                    $hash = '';
                    $chars = $ranCharArray['00'];
                    $max = strlen($chars) - 1;
                    for($i = 0; $i < $length; $i++) {
                        $hash .= $chars[mt_rand(0, $max)];
                    }
                }

                break;
            case 2:
                $length = func_get_arg(0);
                $type = func_get_arg(1);
                if($numeric) {
                    $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
                } else {
                    $hash = '';
                    $chars = $ranCharArray[$type];
                    if(strlen($chars)<10){
                        $hash = self::randomCode($length);
                        return $hash;
                    }else{
                        $max = strlen($chars) - 1;
                        for($i = 0; $i < $length; $i++) {
                            $hash .= $chars[mt_rand(0, $max)];
                        }
                    }
                }

                break;
            default:
                break;
        }

        return $hash;
    }

    /**
     * 是否为utf-8
     * @param string $string
     * @return boolean
     **/
    public static function isUtf8($string){
        return preg_match('%^(?:
				[\x09\x0A\x0D\x20-\x7E] # ASCII
				| [\xC2-\xDF][\x80-\xBF] # non-overlong 2-byte
				| \xE0[\xA0-\xBF][\x80-\xBF] # excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
				| \xED[\x80-\x9F][\x80-\xBF] # excluding surrogates
				| \xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
				| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
		)*$%xs', $string);
    }

    /**
     * 是否为utf-8
     * @param string $string
     * @return boolean
     * @deprecated 命名规范修整
     **/
    public static function is_utf8($string){
        return self::isUtf8($string);
    }

    /**
     * 判断是否为国标
     * @param string $str
     * @return boolean
     */
    public static function isGbk($str) {
        if (strlen($str)>=2){
            $str=strtok($str,"");
            if ((ord($str[0])<161) || (ord($str[0])>247)){
                return false;
            }else{
                if((ord($str[1])<161)||(ord($str[1])>254)){
                    return false;
                }else{
                    return true;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * 判断是否为国标
     * @param string $str
     * @return boolean
     * @deprecated 命名规范修整.
     **/
    public static function is_gbk($str){
        return self::isGbk($str);
    }

    /**
     * 是否为big
     * @param string $str
     * @return boolean
     **/
    public static function isBig5($str) {
        if(strlen($str)>=2){
            $str=strtok($str,"");
            if(ord($str[0]) < 161){
                return false;
            }else{
                if (((ord($str[1]) >= 64)&&(ord($str[1]) <= 126))||((ord($str[1]) >= 161)&&(ord($str[1]) <= 254))){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }

    /**
     * 是否为big
     * @param string $str
     * @return boolean
     * @deprecated 命名规范修整.
     **/
    public static function is_big5($str){
        return self::isBig5($str);
    }

    /**
     * 字符串是否为空.
     * @param $str string 字符串.
     * @return bool
     */
    public static function isBlank($str) {
        return "" == strval($str);
    }

    /**
     * 指定字符串$str是否以$match开头.
     * @param $str string find string.
     * @param $match string matcher.
     * @return bool result.
     */
    public static function startWith($str, $match) {
        if (strlen($str) < $match) {
            return false;
        }
        return substr($str, 0, strlen($match)) == $match;
    }

    /**
     * 指定字符串$str是否以$match结尾..
     * @param $str string find string.
     * @param $match string matcher.
     * @return bool result.
     */
    public static function endWith($str, $match) {
        if (strlen($str) < $match) {
            return false;
        }
        return substr($str, strlen($str) - strlen($match)) == $match;
    }
}