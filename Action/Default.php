<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * Date: 17-1-13
 * Time: 下午4:01
 */
class Action_Default extends Library_Interface_Action {

    protected function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        return true;
    }

    public function execute()
    {
        $msg = 'welcome to this world!';
        $this->put($msg);
    }
}