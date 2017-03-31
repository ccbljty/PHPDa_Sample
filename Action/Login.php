<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 9:25
 */

class Action_Login extends Library_Interface_Action {
    private $_loginFields;

    function __construct() {
        $this->_loginFields = array(
            'login_type',
            'account',
            'pass',
        );
    }

    protected function checkParams($params)
    {
        // 登陆字段校验
        if($params['operation'] == 'login') {
            foreach($this->_loginFields as $field) {
                if(!isset($params[$field]) || empty($params[$field])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 入口
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        }else {
            $service = new Service_Login();
            $msg = $service->execute($params);
            $this->put($msg);
        }
    }
}