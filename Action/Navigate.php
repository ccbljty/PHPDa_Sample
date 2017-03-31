<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/30
 * Time: 9:25
 */

class Action_Navigate extends Library_Interface_Action {


    protected function checkParams($params)
    {
        // 字段校验
        if($params['operation'] == 'school' && !isset($params['school_id'])) {
            return false;
        }
        if($params['operation'] == 'edu_depart' && !isset($params['edu_depart_id'])) {
            return false;
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
            $service = new Service_Navigate();
            $msg = $service->execute($params);
            $this->put($msg);
        }
    }
}