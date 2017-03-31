<?php
/**
 * 先添加题目，再添加选项，再添加问卷标题，组卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Questionnaire extends Library_Interface_Action{
    private $_titleFields;
    private $_questionFields;
    private $_optionFields;
    private $_questionnaireFields;
    private $_viewQuestionnaireFields;
    private $_publishQuestionnaireFields;

    public function __construct() {
        $this->_titleFields = array(
            'title',
            'create_time',
        );

        $this->_questionFields = array(
            'content',
            'type',
            'create_time',
        );

        $this->_optionFields = array(
            'options',
            'create_time',
        );

        $this->_questionnaireFields = array(
            'questionnaire_id',
            'question_id',
            'create_time',
        );

        $this->_viewQuestionnaireFields = array(
            'id',
        );

        $this->_publishQuestionnaireFields = array(
            'questionnaire_id',
            'test_times',
            'create_time',
        );
    }

    private function getFieldsVariableByType($type) {
        if(empty($type)) {
            return false;
        }
        switch($type) {
            case 'add_title' :
                $prefix = '_title';
                break;
            case 'add_question' :
                $prefix = '_question';
                break;
            case 'add_option' :
                $prefix = '_option';
                break;
            case 'add_questionnaire' :
                $prefix = '_questionnaire';
                break;
            case 'view_questionnaire' :
                $prefix = '_viewQuestionnaire';
                break;
            case 'publish_questionnaire' :
                $prefix = '_publishQuestionnaire';
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
        // 校验添加标题所需字段
        $whiteList = array(
            'questionnaire_list',
            'question_list',
        );
        if(in_array($params['operation'], $whiteList)) {
            return true;
        }
        $fields = $this->getFieldsVariableByType($params['operation']);
        if($fields === false) {
            return false;
        }

        foreach($this->$fields as $field) {
            if(empty($params[$field])) {
                return false;
            }
        }

        // 添加选项特别校验
        if($params['operation'] == 'add_option') {
            $optionsFields = array(
                'question_id',
                'content',
                'order',
            );
            $options = json_decode($params['options'], true);
			if(!is_array($options)) {
				return false;
			}
           foreach($options as $option) {
               $tmpArr = Library_Util::arrayOrderByKeys($option, $optionsFields);
               if(count($tmpArr) != count($optionsFields)) {
                    return false;
               }
           }
        }
        return true;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        $params['create_time'] = date('Y-m-d H:i:s');
        $checkRet = $this->checkParams($params);
        if(!$checkRet) {
            $this->putParamsError();
        } else {
            $type = $params['operation'];
            $service = new Service_Questionnaire();
            $fields = $this->getFieldsVariableByType($params['operation']);
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

}