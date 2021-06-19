<?php
/**
 * Created by zj.
 * User: zj
 * Date: 2020/5/17 0017
 * Time: 下午 2:46
 */

namespace restphp\driver;


class RestRedisRepository {
    /**
     * @var array 上下文缓存
     */
    private static $_contextCache = array();

    /**
     * @param $tag
     * @return RestDBRedis instance.
     */
    public static function used($tag = 'default') {
        if (isset(self::$_contextCache[$tag])) {
            return self::$_contextCache[$tag];
        }

        $instance = new RestDBRedis($tag);
        self::$_contextCache[$tag] = $instance;
        return $instance;
    }
}