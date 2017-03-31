<?php
/**
 * 先添加题目，再添加选项，再添加问卷标题，组卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Edit extends Library_Interface_Action{
    private $_titleFields;
    private $_questionFields;
    private $_optionFields;
    private $_questionnaireFields;
    private $_delOptionFields;

    public function __construct() {
        $this->_titleFields = array(
            'must_field' => 'questionnaire_id',
            'content',
        );

        $this->_questionFields = array(
            'must_field' => 'question_id',
            'content',
            'type',
        );

        $this->_optionFields = array(
            'must_field' => 'option_id',
            'content',
            'question_id',
            'order',
        );
         $this->_delOptionFields = array(
             'must_field' => 'option_ids',
        );

        $this->_questionnaireFields = array(
            'must_field' => 'questionnaire_id',
            'question_id',
        );
    }

    private function getFieldsVariableByType($type) {
        if(empty($type)) {
            return false;
        }
        switch($type) {
            case 'update_title' :
                $prefix = '_title';
                break;
            case 'update_question' :
                $prefix = '_question';
                break;
            case 'update_option' :
                $prefix = '_option';
                break;
            case 'delete_option' :
                $prefix = '_delOption';
                break;
            case 'delete_questionnaire_questions' :
                $prefix = '_questionnaire';
                break;
            default :
                return false;
        }
        $fields = $prefix . 'Fields';
        return $fields;
    }
    public function checkParams($params)
    {

        // TODO: Implement checkParams() method.
        $fields = $this->getFieldsVariableByType($params['operation']);
        $fields = $this->$fields;
        if($fields === false || empty($params[$fields['must_field']])) {
            return false;
        }

        $keys = array_keys($params);
        $intArr = array_intersect($keys, $fields);
        // 删除选项时不必执行
        if($params['operation'] != 'delete_option' && count($intArr) < 2) {
            return false;
        }

        foreach($intArr as $field) {
            if(empty($params[$field])) {
                return false;
            }
        }
        return true;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $checkRet = $this->checkParams($params);
        if(!$checkRet) {
            $this->putParamsError();
        } else {
            $serviceObj = new Service_Edit();
            $res = $serviceObj->execute($params);
            $msg = 'fail';
            if($res && is_bool($res)) {
                $msg = 'success';
            } else if(!is_bool($res)) {
                $msg = $res;
            }
            $this->put($msg);
        }
    }
}