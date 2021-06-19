<?php
/**
 * Created by zj.
 * User: zj
 * Date: 2020/5/17 0017
 * Time: 下午 2:32
 */

namespace restphp\driver;


use restphp\biz\RestErrorCode;
use restphp\exception\RestException;
use restphp\utils\RestStringUtils;

class RestDBRedis {
    public $redis;
    public $server_info;
    public function __construct($type){
        $this->balance($type);
        $this->redis = new \redis();
        try{
            $timeout= isset($this->server_info['timeout']) ? $this->server_info['timeout'] : 5;
            $this->redis->connect($this->server_info['host'], $this->server_info['port'], $timeout);
            if (isset($this->server_info['auth']) && !RestStringUtils::isBlank($this->server_info['auth'])) {
                $this->redis->auth($this->server_info['auth']);
            }
        } catch (\Exception $e){
            throw new RestException($e->getMessage(), RestErrorCode::DB_ERROR_REDIS);
        }
    }

    /**
     * 负载均衡预留
     */
    public function balance($type){
        $arrConfig = isset($GLOBALS['_EVN_PARAM']['REDIS']) ? $GLOBALS['_EVN_PARAM']['REDIS'] : array();
        if (!isset($arrConfig[$type])) {
            throw new RestException("redis config error", RestErrorCode::DB_ERROR_REDIS);
        }
        $this->server_info = $arrConfig[$type];
    }

    /**
     * 设置值
     * @param string $key KEY名称
     * @param string $value 需要缓存的数据
     * @param int $timeOut 时间
     * @return mixed
     */
    public function set($key, $value, $timeOut = 0) {
        $retRes = $this->redis->set($key, $value);
        if ($timeOut > 0) $this->redis->expire($key, $timeOut);
        return $retRes;
    }

    /**
     * 通过KEY获取数据
     * @param string $key KEY名称
     * @return mixed
     */
    public function get($key) {
        return $this->redis->get($key);
    }

    /**
     * 删除一条数据
     * @param string $key KEY名称
     * @return mixed
     */
    public function delete($key) {
        return $this->redis->del($key);
    }

    /**
     * 清空数据
     */
    public function flushAll() {
        return $this->redis->flushAll();
    }

    /**
     * 数据入队列
     * @param string $key KEY名称
     * @param string|array $value 获取得到的数据
     * @param bool $right 是否从右边开始入
     * @return mixed
     */
    public function push($key, $value ,$right = true) {
        $value = json_encode($value);
        return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
    }

    /**
     * 数据出队列
     * @param string $key KEY名称
     * @param bool $left 是否从左边开始出数据
     * @return mixed
     */
    public function pop($key , $left = true) {
        $val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);
        return json_decode($val);
    }

    /**
     * 数据自增
     * @param string $key KEY名称
     * @return mixed
     */
    public function increment($key) {
        return $this->redis->incr($key);
    }

    /**
     * 数据自减
     * @param string $key KEY名称
     * @return mixed
     */
    public function decrement($key) {
        return $this->redis->decr($key);
    }

    /**
     * key是否存在，存在返回ture
     * @param string $key KEY名称
     * @return mixed
     */
    public function exists($key) {
        return $this->redis->exists($key);
    }

    /**
     * 返回redis对象
     * redis有非常多的操作方法，我们只封装了一部分
     * 拿着这个对象就可以直接调用redis自身方法
     */
    public function redis() {
        return $this->redis;
    }
}