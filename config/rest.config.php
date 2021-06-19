<?php
//RESTPHP 相关配置
define('REST_PHP_VERSION', '2.0');
define('DIR_LIB', 'lib');
define('DIR_RESTPHP', DIR_LIB . DIRECTORY_SEPARATOR . 'restphp');
define('DIR_BUILD', 'classes');
define('DIR_BUILD_TARGET', 'runtime/target');
define('HTTP_VERSION', '1.1');
define('CONTENT_TYPE', 'application/json');
define('SYS_TIME', time());
define('SYS_MICRO_TIME', microtime(true));