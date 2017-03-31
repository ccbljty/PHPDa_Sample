<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 12:16
 */
class Action_Register extends Library_Interface_Action {
    private $_studentFields;
    private $_schoolFields;

    function __construct() {
        $this->_schoolFields = array(
            'account',
            'passwd' ,
            'name' ,
            'province' ,
            'city' ,
            'county' ,
            'detail_addr' ,
            'stu_count' ,
            'tea_count' ,
            'contacter',
            'phone',
            'edu_depart_id',
            'create_time',
        );
        $this->_studentFields = array(
            'account',
            'passwd',
            'gender',
            'school_id',
            'grade',
            'class',
            'create_time',
        );
    }

    protected function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        // 学生、学校注册
        $enumeration = array('student', 'school');
        if(!isset($params['type']) || empty($params['type']) || !in_array($params['type'], $enumeration)) {
            return false;
        }
        // 学校注册字段检查
        if($params['type'] == 'school') {
            foreach($this->_schoolFields as $field) {
                if(empty($params[$field])) {
                    return false;
                }
            }
        }

        // 学生注册字段选择
        if($params['type'] == 'student') {

            foreach($this->_studentFields as $field) {
                if(empty($params[$field])) {
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
        $params['create_time'] = date('Y-m-d H:i:s');
        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        }else {
            $type = $params['type'];
            $service = new Service_Register();
            if($params['type'] == 'school') {
                $params = Library_Util::arrayOrderByKeys($params,$this->_schoolFields);
            }else if($params['type'] == 'student' ) {
                $params = Library_Util::arrayOrderByKeys($params, $this->_studentFields);
            }
            $params['type'] = $type;
            $res = $service->execute($params);
            $msg = 'fail';
            if($res) {
                $msg = 'success';
            }
            $this->put($msg);
        }
    }
}