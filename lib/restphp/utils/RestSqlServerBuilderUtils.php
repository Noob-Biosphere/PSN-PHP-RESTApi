<?php
namespace restphp\utils;
use restphp\biz\RestErrorCode;
use restphp\exception\RestException;

/**
 * SQL 封装函数
 * @author sofical
 * @copyright misssofts.com
 * @since 2014-7-9
 */
class RestSqlServerBuilderUtils {
    /**
     * @var resource|false an ODBC connection or (<b>FALSE</b>) on error.
     */
    private $pDbLinkCurrent;
    public function __construct($dbLink) {
        $this->pDbLinkCurrent = $dbLink;
    }

    /**
	 * 查询构造
	 * @param string $p_strTable 表名
	 * @param string $p_strOutput 获取列
	 * @param string $p_mxRule 查询条件
	 * @param string $p_strOrder 排序
	 * @param string $p_mixLimit 查询范围
     * @return array
	 */
	public function selectConstruct($p_strTable, $p_strOutput='*', $p_mxRule='', $p_strOrder='', $p_mixLimit=''){
	    $arrParams = array();

		$strRuleSub = '';
		if(is_array($p_mxRule)&&!empty($p_mxRule)){
			$strRuleSub = '';
			foreach($p_mxRule as $strKey=>$mxValue){
				if(is_float($mxValue)||is_integer($mxValue)||is_string($mxValue)){
					$strRuleSub .= (''==$strRuleSub) ? " {$strKey}=? " : " AND {$strKey}=? ";
                    $arrParams[] = $mxValue;
				}else{
					if(is_array($mxValue)){
                        $strRuleSub .= (''==$strRuleSub) ? $mxValue[0] : (" AND " . $mxValue[0]);
					    if (count($mxValue) > 1) {
                            if (is_array($mxValue[1])) {
                                $arrParams = array_merge($arrParams, $mxValue[1]);
                            } else {
                                $arrParams[] = $mxValue[1];
                            }
                        }
					}
				}
			}
		}elseif(is_string($p_mxRule)){
			$strRuleSub = $p_mxRule;
		}

		$strLimit = $p_mixLimit;
		$strLimitWhere = "";
        $pageOpera = true;
		if (is_array($p_mixLimit)) {
		    $strLimit = ' top ' . strval($p_mixLimit['size']) . ' ';
		    $nPage = isset($p_mixLimit['page']) ? $p_mixLimit['page'] : 1;
		    $strLimitWhere = ($nPage - 1) * $p_mixLimit['size'];
        } else if (is_object($p_mixLimit) && strpos(get_class($p_mixLimit), "PageParam") > -1) {
            $strLimit = ' top ' .  $p_mixLimit->getSize() . ' ';
            $strLimitWhere = ($p_mixLimit->getPage() - 1) * $p_mixLimit->getSize();
        } else {
            $pageOpera = !RestStringUtils::isBlank($strLimit);
        }

		if ($pageOpera) {
            if(''==$strRuleSub) {
                $strSql = "SELECT {$strLimit} * FROM (" .
                    "     SELECT ROW_NUMBER() OVER ({$p_strOrder}) AS RowNumber,{$p_strOutput} FROM {$p_strTable} "
                    .") as A " .
                    (RestStringUtils::isBlank($strLimitWhere) ? "" :" WHERE RowNumber > {$strLimitWhere} ");
            } else {
                //todo: 占位符子查询方式有问题，待想出更好的分页方式时进行修改
                /*$strSql = "SELECT {$strLimit} {$p_strOutput} FROM (" .
                    "     SELECT ROW_NUMBER() OVER ({$p_strOrder}) AS RowNumber,{$p_strOutput} FROM [{$p_strTable}] where {$strRuleSub} "
                    .") as A " .
                    (RestStringUtils::isBlank($strLimitWhere) ? "" :" WHERE RowNumber > {$strLimitWhere} ");*/
                $arrRule = explode('?', $strRuleSub);
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

                $strSql = "SELECT {$strLimit} * FROM (" .
                    "     SELECT ROW_NUMBER() OVER ({$p_strOrder}) AS RowNumber,{$p_strOutput} FROM {$p_strTable} where {$strRuleSub} "
                    .") as A " .
                    (RestStringUtils::isBlank($strLimitWhere) ? "" :" WHERE RowNumber > {$strLimitWhere} ");

            }
        } else {
            if(''==$strRuleSub) {
                $strSql = "SELECT {$p_strOutput} FROM {$p_strTable} {$p_strOrder} ";
            } else {
                $strSql = "SELECT {$p_strOutput} FROM {$p_strTable} WHERE {$strRuleSub} {$p_strOrder} ";
            }
        }
		

		return array($strSql, $arrParams);
	}
	
	/**
	 * 插入SQL语句构造
	 * @param string $p_strTable 表
	 * @param array $p_arrRule 组合条件
	 * @return array
	 */
	public function insertConstruct($p_strTable, $p_arrRule){
	    $arrParams = array();
		$strSql = '';
		if(is_array($p_arrRule) && !empty($p_arrRule)){
			$strA = '';
			$strB = '';
			foreach($p_arrRule as $strCol=>$mxValue){
				if(''==$strCol) continue;
				
				$strA .= (''==$strA) ? $strCol : ", {$strCol}";
				
				if(is_float($mxValue) || is_integer($mxValue) || is_string($mxValue)){
					$strB .= (''==$strB) ? " ? " : " ,? ";
					$arrParams[] = $mxValue;
				}else{
					if(is_array($mxValue)){
						$strB .= (''==$strB) ? (' '.$mxValue[0]) : (', '.$mxValue[0]);
                        if (count($mxValue) > 1) {
                            if (is_array($mxValue[1])) {
                                $arrParams = array_merge($arrParams, $mxValue[1]);
                            } else {
                                $arrParams[] = $mxValue[1];
                            }
                        }
					} else {
                        $strB .= (''==$strB) ? " ? " : " ,? ";
                        $arrParams[] = $mxValue;
                    }
				}
			}
			$strSql = "INSERT INTO {$p_strTable} ({$strA}) values ({$strB})";
		}
		return array($strSql, $arrParams);
	}
	
	/**
	 * SQL update 语句组合
	 * @param string $p_strTable 表
	 * @param mixed $p_mxUp 更新信息，可以是key-value数组，也可以是string
	 * @param mixed $p_mxRule 更新条件，可以是key-value数组，也可以是string
	 * @return array
	 */
	public function updateConstruct($p_strTable, $p_mxUp, $p_mxRule){
	    $arrParams = array();
		$strUp = '';
		if(is_array($p_mxUp)){
			if(!empty($p_mxUp)){
				foreach($p_mxUp as $strKey=>$mxValue){
					if(is_float($mxValue) || is_integer($mxValue) || is_string($mxValue)){
						$strUp .= (''==$strUp) ? " {$strKey}=? " : ", {$strKey}=? ";
						$arrParams[] = $mxValue;
					}else{
						if(is_array($mxValue)){
							$strUp .= (''==$strUp) ? (' '.$mxValue[0]) : (', '.$mxValue[0]);
                            if (count($mxValue) > 1) {
                                if (is_array($mxValue[1])) {
                                    $arrParams = array_merge($arrParams, $mxValue[1]);
                                } else {
                                    $arrParams[] = $mxValue[1];
                                }
                            }
						} else {
                            $strUp .= (''==$strUp) ? " {$strKey}=? " : ", {$strKey}=? ";
                            $arrParams[] = $mxValue;
                        }
					}
				}
			}
		}elseif(is_string($p_mxUp)){
			$strUp = $p_mxUp;
		}
		
		$strRule = '';
		if(is_array($p_mxRule)){
			if(!empty($p_mxRule)){
				foreach($p_mxRule as $strKey=>$mxValue){
					if(is_float($mxValue) || is_integer($mxValue) || is_string($mxValue)){
						$strRule .= (''==$strRule) ? " {$strKey}=? " : " AND {$strKey}=? ";
                        $arrParams[] = $mxValue;
					}else{
						if(is_array($mxValue)){
							$strRule .= (''==$strRule) ? (' '.$mxValue[0]) : (' AND '.$mxValue[0]);
                            if (count($mxValue) > 1) {
                                if (is_array($mxValue[1])) {
                                    $arrParams = array_merge($arrParams, $mxValue[1]);
                                } else {
                                    $arrParams[] = $mxValue[1];
                                }
                            }
						} else {
                            $strRule .= (''==$strRule) ? " {$strKey}=? " : " AND {$strKey}=? ";
                            $arrParams[] = $mxValue;
                        }
					}
				}
			}
		}elseif(is_string($p_mxRule)){
			$strRule = $p_mxRule;
		}
		
		$strSql = (''==$strRule) ? "UPDATE {$p_strTable} SET {$strUp} " : "UPDATE {$p_strTable} SET {$strUp} where {$strRule}";
		return array($strSql, $arrParams);
	}
	
	/**
	 * SQL delete 语句组合执行
	 * @param string $p_strTable 表名
	 * @param mixed $p_mxRule
	 * @return array
	 */
	public function deleteConstruct($p_strTable, $p_mxRule){
	    $arrParams = array();
		$strRule = '';
		if(is_array($p_mxRule)){
			if(!empty($p_mxRule)){
				foreach($p_mxRule as $strKey=>$mxValue){
					if(is_float($mxValue) || is_integer($mxValue) || is_string($mxValue)){
						$strRule .= (''==$strRule) ? " {$strKey}=? " : " AND {$strKey}=? ";
                        $arrParams[] = $mxValue;
					}else{
						if(is_array($mxValue)){
							$strRule .= (''==$strRule) ? (' '.$mxValue[0]) : (' AND '.$mxValue[0]);
                            if (count($mxValue) > 1) {
                                if (is_array($mxValue[1])) {
                                    $arrParams = array_merge($arrParams, $mxValue[1]);
                                } else {
                                    $arrParams[] = $mxValue[1];
                                }
                            }
						} else {
                            $strRule .= (''==$strRule) ? " {$strKey}=? " : " AND {$strKey}=? ";
                            $arrParams[] = $mxValue;
                        }
					}
				}
			}
		}elseif(is_string($p_mxRule)){
			$strRule = $p_mxRule;
		}
		$strSql = (''==$strRule) ? " DELETE FROM {$p_strTable}" : "DELETE FROM {$p_strTable} WHERE {$strRule}";
		return array($strSql, $arrParams);
	}
}