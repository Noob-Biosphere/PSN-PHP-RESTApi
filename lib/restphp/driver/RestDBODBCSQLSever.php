<?php
namespace restphp\driver;

use restphp\biz\RestErrorCode;
use restphp\exception\RestException;
use restphp\utils\RestReflection;
use restphp\utils\RestSqlServerBuilderUtils;
use restphp\utils\RestSqlServerHelper;
use restphp\utils\RestStringUtils;

class RestDBODBCSQLSever {
    private static $_dbLinkCache = array();
    private $_link = null;
    private $_charset = 'gb2312';
    private $_safety = 'Y';
    private $_error = array(
        'error_no' => '0',
        'error_message' => 'success'
    );
    public function __construct($strServerHost, $strDbUser, $strDbUserPwd, $strDbName, $charset = 'gb2312', $safety='Y') {
        $strLinkName = $strServerHost . $strDbName;
        $this->_charset = $charset;
        $this->_safety = $safety;
        if (isset(self::$_dbLinkCache[$strLinkName])) {
            $this->_link = self::$_dbLinkCache[$strLinkName];
            return;
        }

        $this->_link = new \PDO('odbc:DRIVER={SQL Server};SERVER=' . $strServerHost . ';DATABASE=' . $strDbName, $strDbUser, $strDbUserPwd);
        self::$_dbLinkCache[$strLinkName] = $this->_link;
    }

    private function _setError($strErrNo, $strErrMsg) {
        $this->_error = array(
            'error_no' => $strErrNo,
            'error_message' => $strErrMsg
        );
    }

    public function getError() {
        return $this->_error;
    }

    /**
     * 执行.
     * @param string $strSql
     * @param array $arrParams
     * @throws RestException
     */
    public function execute($strSql, $arrParams = array()) {
        if (null == $arrParams || empty($arrParams)) {
            $arrParams = array();
        }

        if ('Y' != $this->_safety) {
            $arrRule = explode('?', $strSql);
            $strRuleSub = '';
            $colNum = 0;
            foreach ($arrRule as $rule) {
                if ($strRuleSub == '') {
                    $strRuleSub .= $rule;
                    continue;
                }
                $sqlColumnValue = $arrParams[$colNum++];
                if (RestSqlServerHelper::hasBadSql($sqlColumnValue)) {
                    throw new RestException("find bad sql value: " . $sqlColumnValue, RestErrorCode::DB_ERROR_BAD_SQL);
                }
                if (is_string($sqlColumnValue)) {
                    $sqlColumnValue = RestReflection::clearMessageBoundary($sqlColumnValue);
                    $strRuleSub .= " '{$sqlColumnValue}' ";
                } else {
                    $strRuleSub .= " {$sqlColumnValue} ";
                }
                $strRuleSub .= $rule;
            }
            $arrParams = array();
            $strSql = $strRuleSub;
        } else {
            foreach ($arrParams as &$value) {
                $value = mb_convert_encoding($value, $this->_charset, 'utf-8');
            }
        }

        try {
            $strSql = mb_convert_encoding($strSql, $this->_charset, 'utf-8');
        } catch (\Exception $e) {
            throw new RestException($e->getMessage(), $e->getCode());
        }

        $result = $this->_link->prepare($strSql);
        try {
            if (!$result->execute($arrParams)) {
                $errorMsg = $result->errorInfo();
                foreach ($errorMsg as &$message) {
                    $message = mb_convert_encoding($message, 'utf-8', $this->_charset);
                }
                $errorMsg[]= "error sql: " . $strSql;
                $this->_setError($result->errorCode(), $errorMsg);
                throw new RestException($errorMsg, $result->errorCode());
            }
        } catch (\Exception $e) {
            $errorMsg = $result->errorInfo();
            foreach ($errorMsg as &$message) {
                $message = mb_convert_encoding($message, 'utf-8', $this->_charset);
            }
            $errorMsg[]= "error sql: " . $strSql;
            $this->_setError($result->errorCode(), $errorMsg);
            throw new RestException($errorMsg, $result->errorCode());
        }
    }

    /**
     * 查询SQL结果（二维列表）.
     * @param string $strSql
     * @param  array $arrParams
     * @return array
     * @throws RestException
     */
    public function query($strSql, $arrParams = array()) {
        if (null == $arrParams || empty($arrParams)) {
            $arrParams = array();
        }

        foreach ($arrParams as &$value) {
            $value = mb_convert_encoding($value, $this->_charset, 'utf-8');
        }

        if ('Y' != $this->_safety) {
            $arrRule = explode('?', $strSql);
            $strRuleSub = '';
            $colNum = 0;
            foreach ($arrRule as $rule) {
                if ($strRuleSub == '') {
                    $strRuleSub .= $rule;
                    continue;
                }
                $sqlColumnValue = $arrParams[$colNum++];
                if (RestSqlServerHelper::hasBadSql($sqlColumnValue)) {
                    throw new RestException("find bad sql value: " . $sqlColumnValue, RestErrorCode::DB_ERROR_BAD_SQL);
                }
                if (is_string($sqlColumnValue)) {
                    $sqlColumnValue = RestReflection::clearMessageBoundary($sqlColumnValue);
                    $strRuleSub .= " '{$sqlColumnValue}' ";
                } else {
                    $strRuleSub .= " {$sqlColumnValue} ";
                }
                $strRuleSub .= $rule;
            }
            $arrParams = array();
            $strSql = $strRuleSub;
        }

        $strSql = mb_convert_encoding($strSql, $this->_charset, 'utf-8');

        $result = $this->_link->prepare($strSql);
        try {
            if (!$result->execute($arrParams)) {
                $errorMsg = $result->errorInfo();
                foreach ($errorMsg as &$message) {
                    $message = mb_convert_encoding($message, 'utf-8', $this->_charset);
                }
                $errorMsg[]= "error sql: " . $strSql;
                $this->_setError($result->errorCode(), $errorMsg);
                throw new RestException($errorMsg, $result->errorCode());
            }
        } catch (\Exception $e) {
            $errorMsg = $result->errorInfo();
            foreach ($errorMsg as &$message) {
                $message = mb_convert_encoding($message, 'utf-8', $this->_charset);
            }
            $errorMsg[]= "error sql: " . $strSql;
            $this->_setError($result->errorCode(), $errorMsg);
            throw new RestException($errorMsg, $result->errorCode());
        }

        $arrData = array();
        $arrFetch = $result->fetchAll();
        foreach ($arrFetch as $row) {
            foreach ($row as $key=>&$value) {
                if (!RestStringUtils::isBlank($value)) {
                    $value = mb_convert_encoding($value, 'utf-8', $this->_charset);
                }
            }
            $arrData[] = $row;
        }
        return $arrData;
    }

    /***
     * =======================================================
     * ↓↓↓↓↓↓↓↓		以下为快捷封装	↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
     * =======================================================
     */
    /**
     * SQL 组合对象
     * @var RestSqlServerBuilderUtils
     */
    public static $pSqlBuild;
    /**
     * SQL 组合对象
     * @return RestSqlServerBuilderUtils
     * @return RestSqlServerBuilderUtils.
     */
    public function pSqlBuild(){
        is_object(self::$pSqlBuild) or self::$pSqlBuild = new RestSqlServerBuilderUtils($this->_link);
        return self::$pSqlBuild;
    }

    /**
     * 查询组合
     * @param array $p_arrParams 组合参数解释：
     *        table-表名，
     *        output-获取列,
     *        rule-Where部分条件,
     *        order-排序,limit查询范围限制,
     *        cahcetype-缓存方式（0-文件缓存，1-redis, 2-memcache, 3-mongo），默认为0
     *        timeout-缓存过期时时：0表示使用永久缓存，小于0表示不使用缓存，正数表示缓存时长，单位秒，默认不使用缓存
     *        debug-是否断点输出SQL
     * @return array
     * @throws RestException
     */
    public function select($p_arrParams){
        $strTable	= isset($p_arrParams['table']) ? $p_arrParams['table'] : '';
        $strOutput	= isset($p_arrParams['output']) ? $p_arrParams['output'] : ' * ';
        $mxRule		= isset($p_arrParams['rule']) ? $p_arrParams['rule'] : '';
        $strOrder	= isset($p_arrParams['order']) ? $p_arrParams['order'] : '';
        $mixLimit	= isset($p_arrParams['limit']) ? $p_arrParams['limit'] : '';
        $nTimeout	= isset($p_arrParams['timeout']) ? intval($p_arrParams['timeout']) : -1;
        $bDebug		= isset($p_arrParams['debug']) ? $p_arrParams['debug'] : false;
        $nCacheType	= isset($p_arrParams['cahcetype']) ? intval($p_arrParams['cahcetype']) : 0;
        if ('' == $mixLimit && isset($p_arrParams['page_param'])) {
            $mixLimit = $p_arrParams['page_param'];
        }

        $arrSqlObject = $this->pSqlBuild()->selectConstruct($strTable, $strOutput, $mxRule, $strOrder, $mixLimit);
        $strSql = $arrSqlObject[0];
        $arrSqlParams = $arrSqlObject[1];
        if ($bDebug) {
            throw new RestException("debug sql: " . $strSql . ' . and parameters are: ' . json_encode($arrSqlParams), RestErrorCode::CODE_FILE_LOAD_ERROR);
        }
        //if(-1<$nTimeout){
        //    $arrInfo = $this->select_cache($strSql, $nCacheType, $nTimeout);
        //}else{
        //}

        return $this->query($strSql, $arrSqlParams);
    }

    /**
     * 带缓存的SQL查询
     * @param string $p_strSql SQL查询语句
     * @param integer $p_nCacheType 缓存类型：（0-文件缓存，1-redis, 2-memcache, 3-mongodb），默认为0
     * @param integer $p_nTimeout 超时时间：0表示使用永久缓存，小于0表示不使用缓存，正数表示缓存时长，单位秒，默认不使用缓存
     * @return array
     * @deprecated 暂不支持查询缓存
     */
    public function select_cache($p_strSql, $p_nCacheType, $p_nTimeout, $p_arrSqlParams){
        $arrCacheFuncs = array('_select_cache_file', '_select_cache_redis', '_select_cache_memcache', '_select_cache_mongo');
        $p_nCacheType = intval($p_nCacheType);

        return isset($arrCacheFuncs[$p_nCacheType]) ? $this->$arrCacheFuncs[$p_nCacheType]($p_strSql, intval($p_nTimeout)) : $this->query($p_strSql, $p_arrSqlParams);
    }

    /**
     * 查询SQL文件缓存
     * @param string $strSql SQL语句
     * @param integer $p_nTimeout 超时时间，为0表示永久缓存
     * @return array 查询结果
     * @deprecated 暂不支持查询缓存
     */
    private function _select_cache_file($p_strSql, $p_nTimeout){
        $arrInfo = array();

        $arrObj = pp_static::$arrRunObjConfig[PPFK_RUN_OBJ];
        $strLib = $arrObj['app_cache'].DIRECTORY_SEPARATOR.(isset($arrObj['sql_file_cache_path']) ? $arrObj['sql_file_cache_path'] : '_sql_index').DIRECTORY_SEPARATOR;
        $strSqlCacheFile = $strLib.md5($p_strSql).'.cache';
        unset($arrObj);
        unset($strLib);

        $bRecache = true;
        0===$p_nTimeout and $bRecache = false;

        if($bRecache && file_exists($strSqlCacheFile)){
            $nFileTime = filemtime($strSqlCacheFile);

            $nTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
            ($nTime-$nFileTime)<=$p_nTimeout and $bRecache = false;
        }

        if($bRecache){
            $arrInfo = $this->results($p_strSql);
            file_put_contents($strSqlCacheFile, serialize($arrInfo));
        }else{
            $arrInfo = unserialize(file_get_contents($strSqlCacheFile));
        }

        return $arrInfo;
    }

    /**
     * 带Redis缓存的SQL查询
     * @param string $strSql SQL语句
     * @param integer $p_nTimeout 超时时间
     * @return array
     * @deprecated 暂不支持查询缓存
     */
    private function _select_cache_redis($strSql, $p_nTimeout){
        $arrInfo = array();

        $arrObj = pp_static::$arrRunObjConfig[PPFK_RUN_OBJ];
        $arrConf = isset($arrObj['sql_redis_cache']) ? $arrObj['sql_redis_cache'] : array();
        unset($arrObj);

        if(empty($arrConf)){
            _pp_error_debug('-99', 'Undefined Redis server information！', __FILE__.':'.__CLASS__.'::'.__FUNCTION__, __LINE__);
        }else{
            pp_static::$arrRdsCnf['_sql_redis_cache'] = $arrConf;
            $strKey = md5($strSql);

            $pRedis = new pp_redis('_sql_redis_cache');
            $nDb = isset($arrConf['db']) ? intval($arrConf['db']) : 0;
            $pRedis->redis->select($nDb);
            $mxInfoCache = $pRedis->get($strKey);
            if(empty($mxInfoCache)){
                $arrInfo = $this->results($strSql);
                $pRedis->set($strKey, serialize($arrInfo), $p_nTimeout);
            }else{
                $arrInfo = unserialize($mxInfoCache);
            }
            unset($pRedis);
        }

        return $arrInfo;
    }

    /**
     * 带Memcache缓存的SQL查询
     * @param string $strSql SQL语句
     * @param integer $p_nTimeout 超时时间
     * @return array
     * @deprecated 暂不支持查询缓存
     */
    private function _select_cache_memcache($p_strSql, $p_nTimeout){
        $arrInfo = array();

        $arrObj = pp_static::$arrRunObjConfig[PPFK_RUN_OBJ];
        $arrConf = isset($arrObj['sql_memcache_cache']) ? $arrObj['sql_memcache_cache'] : array();
        unset($arrObj);

        if(empty($arrConf)){
            _pp_error_debug('-99', 'Undefined Memcached server information！', __FILE__.':'.__CLASS__.'::'.__FUNCTION__, __LINE__);
        }else{
            pp_static::$arrMemCnf['_sql_memcache_cache'] = $arrConf;
            $strKey = md5($p_strSql);

            $pMemcache = new pp_memcache('_sql_memcache_cache');
            $mxInfoCache = $pMemcache->get($strKey);
            if(empty($mxInfoCache)){
                $arrInfo = $this->results($p_strSql);
                if(0===$p_nTimeout){
                    $pMemcache->memcache->set($strKey, serialize($arrInfo));
                }else{
                    $pMemcache->set($strKey, serialize($arrInfo), MEMCACHE_COMPRESSED, $p_nTimeout);
                }
            }else{
                $arrInfo = unserialize($mxInfoCache);
            }
            unset($pMemcache);
        }

        return $arrInfo;
    }

    /**
     * 带Mongo缓存的SQL查询
     * @param string $strSql SQL语句
     * @param integer $p_nTimeout 超时时间
     * @return array
     * @deprecated 暂不支持查询缓存
     */
    private function _select_cache_mongo($p_strSql, $p_nTimeout){
        $arrInfo = array();

        $arrObj = pp_static::$arrRunObjConfig[PPFK_RUN_OBJ];
        $strConf = isset($arrObj['sql_memcache_cache']) ? trim($arrObj['sql_mongodb_cache']) : '';
        unset($arrObj);

        if(''===$strConf){
            _pp_error_debug('-99', 'Undefined Mongo server information！', __FILE__.':'.__CLASS__.'::'.__FUNCTION__, __LINE__);
        }else{
            $strDb = '_SQL_Cache_DB';
            $strRule = md5($p_strSql);
            $strTable = '_SQL_Cache_TAB_'.substr($strRule, 0, 16);
            $pMongo = new pp_mongo($strConf);
            if($pMongo->bFlag){
                $pMongo->connect();
                $pMongo->selectDb($strDb);
                $arrCacheInfo = $pMongo->findOne($strTable, array('_md5'=>$strRule));

                $bRecache = true;
                $bAdd = true;
                $nTime = isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time();
                if(!empty($arrCacheInfo)){
                    $bAdd = false;
                    $nCacheTime = $arrCacheInfo['_time'];

                    if(0>=$p_nTimeout || ($nTime-$nCacheTime)<=$p_nTimeout){
                        $bRecache = false;
                        $arrInfo = unserialize($arrCacheInfo['_info']);
                    }
                }

                if($bRecache){
                    $arrInfo = $this->results($p_strSql);

                    if($bAdd){
                        $pMongo->insert($strTable, array(
                            '_md5'	=> $strRule,
                            '_time'	=> $nTime,
                            '_info'	=> serialize($arrInfo)
                        ));
                    }else{
                        $pMongo->update($strTable, array('_md5'=>$strRule), array(
                            '_md5'	=> $strRule,
                            '_time'	=> $nTime,
                            '_info'	=> serialize($arrInfo)
                        ));
                    }
                }
            }else{
                _pp_error_debug('-98', 'Mongo server refused！'.$pMongo->error, __FILE__.':'.__CLASS__.'::'.__FUNCTION__, __LINE__);
            }

            unset($pMongo);
        }

        return $arrInfo;
    }

    /**
     * 插入数据
     * @param string $p_strTable 表名
     * @param array $p_arrRule 组合条件
     * @param boolean $p_bDebug 是否断点调试
     * @return boolean
     */
    public function insert($p_strTable, $p_arrRule, $p_bDebug=false){
        $arrSqlObject = $this->pSqlBuild()->insertConstruct($p_strTable, $p_arrRule);
        $strSql = $arrSqlObject[0];
        $arrSqlParam = $arrSqlObject[1];
        if ($p_bDebug) {
            throw new RestException("debug sql: " . $strSql . ' . and parameters are: ' . json_encode($arrSqlParam), RestErrorCode::CODE_FILE_LOAD_ERROR);
        }
        return $this->execute($strSql, $arrSqlParam);
    }

    /**
     * 更新数据
     * @param string $p_strTable 表名
     * @param mixed $p_mxUp 更新数据
     * @param mixed $p_mxRule 更新条件
     * @param boolean $p_bDebug 是否断点调试
     * @return boolean
     */
    public function update($p_strTable, $p_mxUp, $p_mxRule, $p_bDebug=false){
        $arrSqlObject = $this->pSqlBuild()->updateConstruct($p_strTable, $p_mxUp, $p_mxRule);
        $strSql = $arrSqlObject[0];
        $arrSqlParam = $arrSqlObject[1];
        if ($p_bDebug) {
           throw new RestException("debug sql: " . $strSql . ' . and parameters are: ' . json_encode($arrSqlParam), RestErrorCode::CODE_FILE_LOAD_ERROR);
        }
        return $this->execute($strSql, $arrSqlParam);
    }

    /**
     * 删除数据
     * @param string $p_strTable 表名
     * @param mixed $p_mxRule 删除条件
     * @param boolean $p_bDebug 是否断点调试
     * @return boolean
     */
    public function delete($p_strTable, $p_mxRule, $p_bDebug=false){
        $arrSqlObject = $this->pSqlBuild()->deleteConstruct($p_strTable, $p_mxRule);
        $strSql = $arrSqlObject[0];
        $arrSqlParam = $arrSqlObject[1];
        if ($p_bDebug) {
            throw new RestException("debug sql: " . $strSql . ' . and parameters are: ' . json_encode($arrSqlParam), RestErrorCode::CODE_FILE_LOAD_ERROR);
        }
        return $this->execute($strSql, $arrSqlParam);
    }
}