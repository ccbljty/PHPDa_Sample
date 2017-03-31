<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 20:27
 */
class Service_Edit extends Library_Interface_Service{


    /***
     * 更新标题
     * @param $params
     * @return bool|mysqli_result|null
     */
    private function updateTitle($params) {
        $titleObj = new Dao_Title();
        $ret = $titleObj->updateQuestionnaireTitleById($params['questionnaire_id'], $params['content']);
        return $ret;
    }

    /**
     * 更新题目
     * @param $params
     * @return bool|mysqli_result|null|string
     */
    private function updateQuestion($params) {
        $questionObj = new Dao_Question();
        $questionId = $params['question_id'];
        unset($params['question_id']);
        $ret = $questionObj->updateQuestionById($questionId, $params);
        return $ret;

    }

    /**
     * 更新选项
     * @param $params
     * @return mixed
     */
    private function updateOption($params) {
        $optionObj = new Dao_Option();
        $optionId = $params['option_id'];
        unset($params['option_id']);
        $ret = $optionObj->updateOptionById($optionId, $params);
        return $ret;
    }

    /**
     * 在问卷中删除
     * @param $params
     * @return bool|mysqli_result|null|string
     */
    private function deleteQuestionnaireQustions($params) {
        $titleObj = new Dao_Title();
        $questionnaireId = $params['questionnaire_id'];
        $questionIds = explode(',', $params['question_id']);
        $ret = $titleObj->deleteQuestionnaireQuestionsById($questionnaireId, $questionIds);
        return $ret;
    }

    /**
     * 删除选项
     * @param $optionIds
     * @return mixed
     */
    private function deleteOptionByOptionIds($optionIds) {
        $optionObj = new Dao_Option();
        $ret = $optionObj->deleteOptionByOptionIds($optionIds);
        return $ret;
    }

    public function execute($params)
    {
        // TODO: Implement execute() method.
        $ret = array();
        $operation = $params['operation'];
        unset($params['operation']);
        if($operation == 'update_title') {
            $ret = $this->updateTitle($params);
        }
        if($operation == 'update_question') {
            $ret = $this->updateQuestion($params);
        }
        if($operation == 'update_option') {
            $ret = $this->updateOption($params);
        }
        if($operation == 'delete_option') {
            $optionIds = $params['option_ids'];
            $ret = $this->deleteOptionByOptionIds($optionIds);
        }
        if($operation == 'delete_questionnaire_questions') {
            $ret = $this->deleteQuestionnaireQustions($params);
        }
        return $ret;
    }

}