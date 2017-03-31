<?php
/**
 * 答卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Index extends Library_Interface_Action{

    private $_indexFields;
    private $_indexQuestionFields;
    public function __construct() {
        $this->_indexFields = array(
            'content',
            'weight',
            'parent_id',
            'create_time',
        );

        $this->_indexQuestionFields = array(
            'question_id',
            'index_id',
            'create_time',
        );
    }

    /**
     * 参数检查
     * @param $params
     * @return bool
     */
    public function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        $fields = $this->getFieldsVariableByType($params['operation']);
        if($fields === false) {
            return false;
        }
        foreach ($this->$fields as $field) {
            if(!isset($params[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取field名
     * @param $type
     * @return bool|string
     */
    private function getFieldsVariableByType($type) {
        if(empty($type)) {
            return false;
        }
        switch($type) {
            case 'add_index' :
                $prefix = '_index';
                break;
            case 'add_question_index' :
                $prefix = '_indexQuestion';
                break;
            default :
                return false;
        }
        $fields = $prefix . 'Fields';
        return $fields;
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
        }
        $type = $params['operation'];
        $service = new Service_Index();
        $fields = $this->getFieldsVariableByType($type);
        $params = Library_Util::arrayOrderByKeys($params,$this->$fields);
        $params['operation'] = $type;
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