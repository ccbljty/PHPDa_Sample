<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 20:30
 */
class Service_Status extends Library_Interface_Service{

    /**
     * 获取问卷中题目个数
     * @param $questionnaireId
     * @return int
     */
    public function getQuestionNumsByid($questionnaireId) {
        $questionObj = new Dao_Title();
        $res = $questionObj->getQuestionsNumByid($questionnaireId);
        return $res;
    }

    /**
     * 获取问卷数量
     * @return int
     */
    public function getAllQuestionnaireNum() {
        $questionObj = new Dao_Title();
        $res = $questionObj->getAllQuestionnaireNum();
        return $res;
    }

    /**
     * 获取题库问题数量
     * @return int
     */
    public function getAllQuestionNum() {
        $questionObj = new Dao_Question();
        $res = $questionObj->getAllQuestionsNum();
        return $res;
    }

    /**
     * 获取问卷发布次数
     * @param $questionnaireId
     * @return int
     */
    public function getQustionnaireTestTimesByid($questionnaireId) {
        $testTimesObj = new Dao_QuestionnaireTest();
        $res = $testTimesObj->getQuestionnaireTestTimesByid($questionnaireId);
        return $res;
    }

    public function execute($params)
    {
        // TODO: Implement execute() method.
        $allQuestionsNum = $this->getAllQuestionNum();
        // 题库状态信息
        if($params['operation'] == 'questions_lib') {
            $questionnaireNum = $this->getAllQuestionnaireNum();
            $ret = array(
                'questionnaire_num' => $questionnaireNum,
                'lib_questions_num' => $allQuestionsNum,
            );
        // 问卷状态信息
        } elseif($params['operation'] == 'questionnaire') {
            $questionnaireId = $params['questionnaire_id'];
            $testTimes = $this->getQustionnaireTestTimesByid($questionnaireId);
            $questionsNum = $this->getQuestionNumsByid($questionnaireId);
            $ret = array(
                'test_times' => $testTimes,
                'questions_num' => $questionsNum,
                'not_exists_lib_questions_num' => $allQuestionsNum - $questionsNum,
                'lib_questions_num' => $allQuestionsNum,
            );
        }

        return $ret;
    }
}