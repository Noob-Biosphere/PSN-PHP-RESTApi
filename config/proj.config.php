<?php
//开发配置定义

//当前环镜
define('PROJ_ENV', 'pro');

//环镜参数
$_EVN_PARAM_ALL = include('env.config.php');
$_EVN_PARAM = $_EVN_PARAM_ALL[PROJ_ENV];
$_DB_MYSQL = isset($_EVN_PARAM) ? $_EVN_PARAM['DATABASE_MYSQL'] : array();

//加载多语言
$_LANG = include('lang.config.php');

//日志目录
define('APP_LOG_DIR', 'runtime/log');
