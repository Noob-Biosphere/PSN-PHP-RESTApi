<?php
namespace restphp\core;

use restphp\http\RestHttpMethod;
use restphp\utils\RestStringUtils;
use restphp\validate\RestValidate;

/**
 * 构建工具.
 * Class RestBuildV2
 * @package restphp\core
 */
class RestBuildV2 {
    private static $_keyword = "@RequestMapping(";

    /**
     * @param $reflection \ReflectionClass
     */
    public static function _getMapping($reflection) {
        $name = $reflection->getName();
        $doc = $reflection->getDocComment();

        $arrMapping = array();
        $arrDoc = RestValidate::clearDocToArr($doc);
        foreach ($arrDoc as $arrDocItem) {
            if (RestStringUtils::startWith($arrDocItem, self::$_keyword)) {
                $strDocRule = RestValidate::getRuleStr($doc);
                if (RestStringUtils::isBlank($strDocRule)) {
                    continue;
                }

                $strUrlAll = "";
                $method = "";
                $before = "";
                $after = "";

                $arrRule = explode(",", $strDocRule);
                foreach ($arrRule as $rule) {
                    $arrItem = explode('=', trim($rule));
                    if (!isset($arrItem[1])) {
                        $val = $arrItem[0];
                        $val = str_replace('"', "", $val);
                        $val = str_replace("'", "", $val);
                        $strUrlAll .= ('' == $strUrlAll ? '' : ',') . $val;
                        continue;
                    }

                    $itemName = strtolower($arrItem[0]);
                    $val = trim($arrItem[1]);
                    $val = str_replace('"', "", $val);
                    $val = str_replace("'", "", $val);
                    if ('value' == $itemName) {
                        $strUrlAll = trim($val);
                    } else if ('method' == $itemName) {
                        $method = strtoupper($val);
                    } else if ('before' == $itemName) {
                        $before = trim($val);
                    } else if ('after' == $itemName) {
                        $after = trim($val);
                    }
                }

                if (RestStringUtils::startWith($strUrlAll, "[")) {
                    $strUrlAll = substr($strUrlAll,1);
                }
                if (RestStringUtils::endWith($strUrlAll, "]")) {
                    $strUrlAll = substr($strUrlAll, 0, strlen($strUrlAll) -1);
                }

                $arrUrl = explode(",", $strUrlAll);
                foreach ($arrUrl as $url) {
                    $urlSource = $url;
                    $arrArgs = array();

                    $arrArgMatch = array();
                    preg_match("/\\{.*?\\}/i", $url, $arrArgMatch);
                    if (isset($arrArgMatch[0])) {
                        foreach ($arrArgMatch as $arg) {
                            $url = str_replace($arg, "(.*)", $url);
                            $arrArgs[] = substr($arg,1, strlen($arg) - 2);
                        }
                    }

                    $arrMapping[] = array(
                        'url' => $url,
                        'urlSource' => $urlSource,
                        'name' => $name,
                        'method' => $method,
                        'before' => $before,
                        'after' => $after,
                        'args' => $arrArgs
                    );
                }
            }
        }

        return $arrMapping;
    }

    /**
     * 构建.
     * @throws \ReflectionException
     */
    public static function run() {
        //先清构建目标目录
        //self::_delAllFile(RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR);

        //扫描构建目录
        $arrFiles = array();
        $arrDir = explode("|", DIR_BUILD);
        foreach ($arrDir as $eachBuildDir) {
            self::_loadPHPFiles($arrFiles, $eachBuildDir . DIRECTORY_SEPARATOR);
        }
        if (empty($arrFiles)) {
            echo "build success, there is no file to build!";
            return;
        }

        //创建构建缓存
        foreach($arrFiles as $strFile) {
            $strCallClass = substr($strFile, 0,strlen($strFile) - 4);
            $strCallClass = "\\" . str_replace("/", "\\", $strCallClass);
            $reflection = new \ReflectionClass($strCallClass);

            //$namespace = $reflection->getNamespaceName();
            $arrClassMapping = self::_getMapping($reflection);
            $arrFunctions = $reflection->getMethods();
            foreach ($arrFunctions as $reflectionFunction) {
                $arrFunctionMapping = self::_getMapping($reflectionFunction);
                if (!empty($arrFunctionMapping) && empty($arrClassMapping)) {
                    $arrClassMapping = array(
                        'url' => "",
                        'urlSource' => "",
                        'name' => $reflection->getName(),
                        'method' => "",
                        'before' => "",
                        'after' => "",
                        'args' => array()
                    );
                }
                foreach ($arrClassMapping as $arrClassMap) {
                    foreach ($arrFunctionMapping as $arrFunMap) {
                        $url = $arrClassMap['url'] . $arrFunMap['url'];
                        $url = str_replace("//", "/", $url);
                        if (!RestStringUtils::startWith($url, "/")) {
                            $url = "/" . $url;
                        }
                        if (!RestStringUtils::endWith($url,"/")) {
                            $url .= "/";
                        }
                        $urlSource = $arrClassMap['urlSource'] . $arrFunMap['urlSource'];
                        $urlSource = str_replace("//", "/", $urlSource);
                        if (!RestStringUtils::endWith($urlSource,"/")) {
                            $urlSource .= "/";
                        }
                        $urlKey = str_replace("/", "_", $url);
                        $urlFile = str_replace("/", "_", $urlSource);
                        $method = $arrFunMap['method'];
                        $arrMethods = '' == $method ? RestHttpMethod::FULL_HTTP_METHODS() : array($method);
                        $arrArgs = array_merge($arrClassMap['args'], $arrFunMap['args']);
                        if (isset($arrArgs[0])) {
                            foreach ($arrArgs as $arg) {
                                $urlFile = str_replace("{".$arg."}", '$'.$arg, $urlFile);
                            }
                        }
                        foreach ($arrMethods as $signMethod) {
                            $arrTeam = isset(self::$_arrMaps[$signMethod]) ? self::$_arrMaps[$signMethod] : array();
                            $preg = $url . RestConstant::REST_URI_ALL_END();
                            $preg = str_replace("/", '\/', $preg);
                            $arrTeam[$urlKey] = array(
                                'path_param' => $arrArgs,
                                'preg_match' => $preg,
                                'filename' => $urlFile,
                                'namespace' => '',
                                'class' => $arrClassMap['name'],
                                'function' => $arrFunMap['name'],
                                'before' => $arrFunMap['before'],
                                'after' => $arrFunMap['after']
                            );
                            self::$_arrMaps[$signMethod] = $arrTeam;
                        }
                    }
                }
            }
        }

        //开始分创建构建结果
        self::_buildFinal();

        echo "<br />new builder execute success!";
    }

    //构建缓存变量
    private static $_arrMaps = array();

    /**
     * 构建成文件
     */
    private static function _buildFinal() {
        if(empty(self::$_arrMaps)) {
            return;
        }

        //构建Map
        foreach(self::$_arrMaps as $strMethod => $arrMaps) {
            $strMap = "<?php\nreturn array(";
            $strMethodMapFileName = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR . '_v2_' . $strMethod . '.php';
            $intPos = 0;
            foreach($arrMaps as $strUriKey => $arrMap) {
                $strMap .= "\n\t'" . $strUriKey . "'=>array(";
                $strMap .= "\n\t\t'path_param'=>array(" ;
                if (!empty($arrMap['path_param'])) {
                    $intPosSub = 0;
                    foreach($arrMap['path_param'] as $strPathParam) {
                        $strMap .= "\n\t\t\t\t'{$strPathParam}'";
                        ++$intPosSub != count($arrMap['path_param']) and $strMap .=",";
                    }
                }
                $strMap .= "\n\t\t\t),";
                $strMap .= "\n\t\t'preg_match'=>'" . $arrMap['preg_match'] . "',";
                $strMap .= "\n\t\t'filename'=>'" . $arrMap['filename'] . "',";
                $strMap .= "\n\t\t'namespace'=>'" . $arrMap['namespace'] . "',";
                $strMap .= "\n\t\t'class'=>'" . $arrMap['class'] . "',";
                $strMap .= "\n\t\t'function'=>'" . $arrMap['function'] . "'\n\t\t)";
                ++$intPos != count($arrMaps) and $strMap .= ",";

                //构建路由入口
                $strRouteFileDir = RestConstant::REST_TARGET() . DIRECTORY_SEPARATOR . $strMethod . DIRECTORY_SEPARATOR;
                if (!file_exists($strRouteFileDir)) {
                    mkdir($strRouteFileDir);
                }
                $strRouteFileName = $strRouteFileDir . $arrMap['filename'] . '.php';
                $strFileContent = "<?php\n";
                if (!RestStringUtils::isBlank($arrMap['before'])) {
                    $strFileContent .= "\n" . $arrMap['before'] . '();';
                }
                $strFileContent .= "\n" . '$client = new ' . $arrMap['class'] . '();';
                $strFileContent .= "\n" . '$client->' . $arrMap['function'] . '();';
                if (!RestStringUtils::isBlank($arrMap['after'])) {
                    $strFileContent .= "\n" . $arrMap['after'] . '();';
                }
                file_put_contents($strRouteFileName, $strFileContent);
            }
            $strMap .= "\n\t);";
            file_put_contents($strMethodMapFileName, $strMap);
        }
    }

    /**
     * 扫描获取所有项目
     * @param array $arrFiles
     * @param string $strDir
     * @param bool $removeLib
     */
    private static function _loadPHPFiles(&$arrFiles, $strDir, $removeLib = false) {
        if (is_dir($strDir)) {
            $arr = glob($strDir . "*php");
            if ($removeLib) {
                foreach ($arr as &$file) {
                    $file = substr($file, strlen(DIR_LIB . DIRECTORY_SEPARATOR));
                }
            }
            $arrFiles = array_merge($arrFiles, $arr);
            $arrDirs = glob($strDir . '*' . DIRECTORY_SEPARATOR);
            if (!empty($arrDirs)) {
                foreach ($arrDirs as $strSubDir) {
                    self::_loadPHPFiles($arrFiles, $strSubDir, $removeLib);
                }
            }
        } else if (is_dir(DIR_LIB . DIRECTORY_SEPARATOR . $strDir)) {
            $strDir = DIR_LIB . DIRECTORY_SEPARATOR . $strDir;
            self::_loadPHPFiles($arrFiles, $strDir, true);
        } else {
            echo "<p>build dir {$strDir} not found!</p>";
        }
    }
}