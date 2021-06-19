<?php
namespace restphp\driver;

use restphp\biz\RestPageReturn;
use restphp\utils\RestClassUtils;

/**
 * Class RestRepository
 * @package restphp\driver
 */
class RestRepository {
    protected $dbPrefix;
    protected $tableName;
    public function __construct($tableName){
        $dbCnf = isset($GLOBALS['_DB_MYSQL']) ? $GLOBALS['_DB_MYSQL'] : array();
        $this->dbPrefix = isset($dbCnf['DB_PREFIX']) ? $dbCnf['DB_PREFIX'] : '';
        $this->tableName = $tableName;
    }
    protected function getTable() {
        return $this->dbPrefix . $this->tableName;
    }

    private static $_pSelectDB;
    public function pSelectDB() {
        is_object(self::$_pSelectDB) or self::$_pSelectDB = new RestDbMysql("Def_Select");
        return self::$_pSelectDB;
    }

    private static $_pUpdateDB;
    public function pUpdateDB(){
        is_object(self::$_pUpdateDB) or self::$_pUpdateDB = new RestDbMysql("Def_Update");
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
     * 获取信息
     * @param array $arrParams 查询条件
     * @return array data collection
     */
    public function select($arrParams){
        if(!isset($arrParams["table"])) $arrParams["table"]=$this->getTable();
        if(!isset($arrParams["output"])) $arrParams["output"]=" * ";
        $arrInfo = $this->pSelectDB()->select($arrParams);
        return $arrInfo;
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
        $arrData = RestClassUtils::beanToArr($obj);
        if (empty($arrRule)) {
            return $this->insert($arrData, $debug);
        } else {
            return $this->update($arrData, $arrRule, $debug);
        }
    }

    /**
     * 获取Insert 产生的LastInsertID
     */
    public function last_insert_id(){
        return $this->pUpdateDB()->insert_id();
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
     * 获取标准分页数据.
     * @param $arrParams
     * @return RestPageReturn
     */
    public function getPageReturn($arrParams) {
        $arrCountRule = array();
        if (isset($arrParams['table'])) {
            $arrCountRule['table'] = $arrParams['table'];
        }
        if (isset($arrParams['rule'])) {
            $arrCountRule['rule'] = $arrParams['rule'];
        }
        $total = $this->count($arrCountRule);
        $item = $this->select($arrParams);
        $pageReturn = new RestPageReturn();
        $pageReturn->setTotal($total);
        $pageReturn->setItems($item);
        return $pageReturn;
    }

    /**
     * 根据条件获取一条信息.
     * @param $mixRule
     * @return mixed|null
     */
    public function fineOne($mixRule) {
        $arrParam = array(
            'rule' => $mixRule,
            'limit' => ' limit 1'
        );
        $arrList = $this->select($arrParam);
        return isset($arrList[0]) ? $arrList[0] : null;
    }
}