<?php
/**
 * 先添加题目，再添加选项，再添加问卷标题，组卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Status extends Library_Interface_Action{

    public function __construct() {

    }

    public function checkParams($params)
    {
        $operation = $params['operation'];
        if(!isset($operation) || $operation == 'questionnaire' &&  empty($params['questionnaire_id'])) {
            return false;
        }
        return true;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        }
        $service = new Service_Status();
        $res = $service->execute($params);
        $msg = 'fail';
        if($res && is_bool($res)) {
            $msg = 'success';
        } else if(!is_bool($res)) {
            $msg = $res;
        }
        $this->put($msg);
    }

}