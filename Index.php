<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * Date: 17-1-13
 * Time: 上午10:54
 */

require_once (__DIR__ . '/Library/Init.php');

class Index{
    /**
     * 执行入口
     */
    public function execute(){
        $action = Library_Env::getAction();
        $obj = new $action();
        $obj->execute();
    }
}
$obj = new Index();
$obj->execute();