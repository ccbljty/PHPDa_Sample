<?php
/**
 * 答卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Answers extends Library_Interface_Action{

    private $_answerFields;
    public function __construct() {
        $this->_answerFields = array(
            'answer',
            'questionnaire_id',
            'sno',
            'test_times',
            'create_time',
        );
    }

    public function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        foreach ($this->_answerFields as $field) {
            if(!isset($params[$field])) {
                return false;
            }
        }
        return true;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $params['create_time'] = date('Y-m-d H:i:s');
        $params['sno'] = Library_Env::getSessionBykey('sno');
        if(!isset($params['sno'])) {
            $params['sno'] = 0;
        }

        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        }
        $service = new Service_Answers();
        $params = Library_Util::arrayOrderByKeys($params,$this->_answerFields);
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