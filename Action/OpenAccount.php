<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 12:16
 */
class Action_OpenAccount extends Library_Interface_Action {

    /**
     * 检验参数
     * @param $params
     * @return bool
     */
    protected function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        // 添加专家或者教育管理部门
        $enumeration = array('admin', 'department');
        if(!isset($params['type']) || empty($params['type']) || !in_array($params['type'], $enumeration)) {
            return false;
        }
        if(empty($params['account']) || empty($params['passwd']) || empty($params['name'])) {
            return false;
        }
        if($params['type'] == 'department' && (empty($params['province']) || empty($params['city']) || empty($params['county']) || !isset($params['parent_id']) || empty($params['parent_id']) && $params['parent_id'] === 0)) {
            return false;
        }
        return true;
    }

    /**
     * 执行入口
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        } else {
            $service = new Service_OpenAccount();
            $res = $service->execute($params);
            $msg = 'fail';
            if($res) {
                $msg = 'success';
            }
            $this->put($msg);
        }

    }
}