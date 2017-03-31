<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/17
 * Time: 21:39
 */
require_once (__DIR__ . '/Constant.php');
function __autoload($className) {
    $relPath = implode('/', explode('_', $className));
    require_once (APP_PATH . '/' . $relPath . '.php');
}
// 允许跨域访问
header('Access-Control-Allow-Origin:*');
// 开启会话
session_start();
// 设置时区
date_default_timezone_set(TIME_ZONE);
