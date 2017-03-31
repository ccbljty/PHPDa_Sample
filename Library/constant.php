<?php
/**
 * Created by PhpStorm.
 * desc: 常量定义文件
 * User: bobo
 * Date: 17-1-13
 * Time: 上午11:13
 */

// app 路径
define('APP_PATH', realpath(__DIR__ . '/../'));
// 时区设置
define('TIME_ZONE', 'Asia/Shanghai');
// 日志设置
define('TRACE_PATH', APP_PATH . '/Log/log_' . date('Ymd') . '.log');
define('WARNING_PATH', APP_PATH . '/Log/log_wf_' . date('Ymd') . '.log');
// 请求ID
list($usec, $sec) = explode(' ', microtime());
define('REQ_ID', $sec . ($usec * 1E6));
// 配置文件目录
define('CONF_DIR', APP_PATH . '/Conf/');
// 缓存时间 半个小时
define('REDIS_TTL', 1800);