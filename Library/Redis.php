<?php
/**
 * redis 封装
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 20:18
 */
class Library_Redis{
    private $_redis;

    function __construct() {
        $this->_init();
    }

    /**
     * 初始化
     */
    private function _init() {
        $confs = Library_Conf::getConf('redis');
        $host = $confs['server']['host'];
        $port = $confs['server']['port'];
        $this->_redis = new Redis();
        $ret = $this->_redis->connect($host, $port);
        if(!$ret) {
            Library_Log::warning('redis connect failed . host:' . $host . ' port:' . $port);
        } else {
            Library_Log::trace('redis connect success');
        }
    }

    /**
     * 默认调用方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $methods = array('set','get','setex','delete');
        if(in_array($name, $methods)) {
               return call_user_func_array(array($this->_redis, $name), $arguments);
        } else {
            Library_Log::warning($name . 'is illegal');
        }
    }
}