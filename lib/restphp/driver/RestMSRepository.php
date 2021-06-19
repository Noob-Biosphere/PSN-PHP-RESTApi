<?php
namespace restphp\driver;

use restphp\biz\RestPageReturn;
use restphp\utils\RestClassUtils;
use restphp\utils\RestStringUtils;

/**
 * Class RestMSRepository
 * @package restphp\driver
 */
abstract class RestMSRepository {
    protected $dbPrefix;
    protected $dbCnf;
    protected $tableName;
    function __construct($tableName){
        $this->dbCnf = isset($GLOBALS['_DB_SQL_SERVER']) ? $GLOBALS['_DB_SQL_SERVER'] : array();
        $this->dbPrefix = isset($dbCnf['DB_PREFIX']) ? $dbCnf['DB_PREFIX'] : '';
        $this->tableName = $tableName;
    }

    /**
     * 表名.
     * @param bool $justName
     * @return mixed
     */
    public function getTable($justName = false) {
        $table = $this->tableName;
        if (!RestStringUtils::startWith($table, '[')) {
            $table = '[' . $table;
        }
        if (!RestStringUtils::endWith($table, ']')) {
            $table .= ']';
        }
        return $justName ? $this->tableName : $table;
    }

    private function _getDbInstance($type) {
        $arrDb = $this->dbCnf[$type];
        $host = $arrDb['dbhost'];
        $user = $arrDb['dbuser'];
        $pass = $arrDb['dbpass'];
        $name = $arrDb['dbname'];
        $charset = isset($arrDb['charset']) ? $arrDb['charset'] : 'gb2312';
        $safety = isset($arrDb['safety']) ? $arrDb['safety'] : 'Y';
        return new RestDBODBCSQLSever($host, $user, $pass, $name, $charset, $safety);
    }

    private static $_pSelectDB;
    public function pSelectDB() {
        is_object(self::$_pSelectDB) or self::$_pSelectDB = $this->_getDbInstance('Def_Select');
        return self::$_pSelectDB;
    }

    private static $_pUpdateDB;
    public function pUpdateDB(){
        is_object(self::$_pUpdateDB) or self::$_pUpdateDB = $this->_getDbInstance('Def_Update');
        return self::$_pUpdateDB;
    }

    /**
     * 获取总数.
     * @param $arrParams
     * @return int
     */
    public function count($arrParams) {
        if(!isset($arrParams["table"])) $arrParams["table"]=$this->getTable();
        $arrParams["output"]=" count(1) as total ";
        $arrInfo = $this->pSelectDB()->select($arrParams);
        return isset($arrInfo[0]) ? intval($arrInfo[0]['total']) : 0;
    }

    /**
     * 获取总数.
     * @param $mixRule
     * @return int
     */
    public function countByMixRule($mixRule, $debug = false) {
        $arrParams = array('rule' => $mixRule);
        $arrParams['debug'] = $debug;
        return $this->count($arrParams);
    }

    /**
     * 获取信息
     * @param array $arrParams 查询条件
     * @param null $listBean
     * @return array data collection
     * @throws \restphp\exception\RestException
     */
    public function select($arrParams, $listBean = null){
        if(!isset($arrParams["table"])) $arrParams["table"]=$this->getTable();
        if(!isset($arrParams["output"])) $arrParams["output"]=" * ";
        $itemList = $this->pSelectDB()->select($arrParams);
        if (null == $itemList) {
            $itemList = array();
        }
        if (null != $listBean && null != $itemList) {
            $newItemList = array();
            foreach ($itemList as $item) {
                $toListBean = clone $listBean;
                $newItem = RestClassUtils::copyFromArr($toListBean, $item);
                $newItemList[] = $newItem;
            }
            $itemList = $newItemList;
        }
        return $itemList;
    }

    /**
     * 插入数据
     * @param array $arrRule 新增数据
     * @param boolean $debug 是否调试打印SQL语句
     * @return boolean
     */
    public function insert($arrRule, $debug=false){
        return $this->pUpdateDB()->insert($this->getTable(), $arrRule, $debug);
    }

    /**
     * 保存对象.
     * @param $obj
     * @param bool $debug
     * @return bool
     * @throws \ReflectionException
     */
    public function save($obj, $debug=false) {
        $arrRule = RestRepositoryHelper::getPrimaryRule($obj);
        $arrData = RestClassUtils::beanToArr($obj, false);
        if (empty($arrRule)) {
            return $this->insert($arrData, $debug);
        } else {
            // SQL Server 通常不允许修改主键.
            foreach ($arrRule as $key=>$val) {
                unset($arrData[$key]);
            }
            return $this->update($arrData, $arrRule, $debug);
        }
    }

    /**
     * 获取Insert 产生的LastInsertID
     */
    public function last_insert_id(){
        $strSql = "select @@IDENTITY as id";
        $arrInfo = $this->pUpdateDB()->query($strSql);
        if (!isset($arrInfo[0])) {
            return null;
        }
        return $arrInfo[0]['id'];
    }

    /**
     * 更新数据
     * @param <array/string> $params 更新内容
     * @param <array/string> $rule 条件
     * @param boolean $debug 是否调试打印SQL语句
     * @return boolean
     */
    public function update($params, $rule, $debug=false){
        return $this->pUpdateDB()->update($this->getTable(), $params, $rule, $debug);
    }

    /**
     * 删除数据
     * @param <array/string> $rule 条件
     * @param boolean $debug 是否调试打印SQL语句
     * @return boolean
     */
    public function delete($rule, $debug=false){
        return $this->pUpdateDB()->delete($this->getTable(), $rule, $debug);
    }

    /**
     * 批量删除.
     * @param array $arrValue 删除范围，如 array('1','2','3'...)
     * @param string $column 列名.
     * @param boolean $debug 是否调试SQL.
     */
    public function batchDelete($arrValue, $column = 'id', $debug = false) {
        $strDelRule = '';
        $arrSqlParam = array();
        foreach ($arrValue as $id) {
            $strDelRule .= ('' == $strDelRule ? '' : ',') . '?';
            $arrSqlParam[] = $id;
        }
        $arrRule = array(
            "k" => array(
                " {$column} in ({$strDelRule}) ",
                $arrSqlParam
            )
        );
        $this->delete($arrRule, $debug);
    }

    /**
     * 获取标准分页数据.
     * @param $arrParams
     * @return RestPageReturn
     */
    public function getPageReturn($arrParams, $listBean = null) {
        $arrCountRule = array();
        if (isset($arrParams['table'])) {
            $arrCountRule['table'] = $arrParams['table'];
        }
        if (isset($arrParams['rule'])) {
            $arrCountRule['rule'] = $arrParams['rule'];
        }
        if (isset($arrParams['debug'])) {
            $arrCountRule['debug'] = $arrParams['debug'];
        }
        $total = $this->count($arrCountRule);
        if (isset($arrParams['listDebug'])) {
            $arrParams['debug'] = $arrParams['listDebug'];
        }
        $itemList = $this->select($arrParams, $listBean);
        $pageReturn = new RestPageReturn();
        $pageReturn->setTotal($total);
        $pageReturn->setItems($itemList);
        return $pageReturn;
    }

    /**
     * 根据条件获取一条信息.
     * @param $mixRule
     * @param string $order
     * @return mixed|null
     * @throws \restphp\exception\RestException
     * @deprecated 命名错误，修正为findOne
     */
    public function fineOne($mixRule, $order = ' order by id ') {
        return $this->findOne($mixRule, $order);
    }

    /**
     * 根据条件获取一条信息.
     * @param $mixRule
     * @param string $order
     * @return mixed|null
     * @throws \restphp\exception\RestException
     */
    public function findOne($mixRule, $order = ' order by id ') {
        $arrParam = array(
            'rule' => $mixRule,
            'limit' => ' top 1 ',
            'order' => $order
        );
        $arrList = $this->select($arrParam);
        return isset($arrList[0]) ? $arrList[0] : null;
    }

    /**
     * 根据条件查询所有数据.
     * @param $mixRule
     * @param string $order
     * @return array
     * @throws \restphp\exception\RestException
     */
    public function findAll($mixRule, $order = ' order by id ') {
        $arrParam = array(
            'rule' => $mixRule,
            'order' => $order
        );
        return $this->select($arrParam);
    }
}