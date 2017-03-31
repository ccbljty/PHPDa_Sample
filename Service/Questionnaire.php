<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:58
 */
class Service_Questionnaire extends Library_Interface_Service{

    /**
     * 添加问卷标题
     * @param $rows
     * @return bool|mysqli_result|null
     */
    private function addTitleByMulti($fields, $rows) {
        $title = new Dao_Title();
        $ret = $title->insertIntoTitleByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 添加题目
     * @param $rows
     * @return bool|mysqli_result|null
     */
    private function addQuestionByMulti($fields, $rows) {
        $question = new Dao_Question();
        $ret = $question->insertIntoQuestionByMulti($fields, $rows);
        if(is_int($ret)) {
            $ret = array(
                'new_question_id' => $ret,
            );
        }
        return $ret;
    }

    /**
     * 添加选项
     * @param $rows
     * @return bool|mysqli_result|null
     */
    private function addOptionByMulti($fields, $rows) {
        $option = new Dao_Option();
        $ret = $option->insertIntoOptionByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 组卷
     * @param $rows
     * @return bool|mysqli_result|null
     */
    private function createQuestionnaireByMulti($fields, $rows) {
        $option = new Dao_Title();
        $ret = $option->insertIntoQuestionnaireByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 创建问卷
     * @param $params
     * @return bool
     */
    public function addQuestionnaire($params) {
        $type = $params['operation'];
        unset($params['operation']);
        $fields = array_keys($params);
        if($type == 'add_title') {
            $rows = array($params);
            $res = $this->addTitleByMulti($fields, $rows);
        }
        if($type == 'add_question') {
            $rows = array($params);
            $res = $this->addQuestionByMulti($fields, $rows);
            return $res;
        }
        if($type == 'add_option') {
            $options = json_decode($params['options'], true);
            foreach($options as & $option) {
                $option['create_time'] = $params['create_time'];
            }
            $fields = array_keys($option);
            unset($option);
            $res = $this->addOptionByMulti($fields, $options);
        }
        if($type == 'add_questionnaire') {
            $questionIds = explode(',', $params['question_id']);
            $rows = array();
            foreach($questionIds as $questionId) {
                $rows[] = array(
                    $params['questionnaire_id'],
                    $questionId,
                    $params['create_time'],
                );
            }
            $res = $this->createQuestionnaireByMulti($fields, $rows);
        }
        return empty($res) ? false : true;
    }

    /**
     * 加载问卷
     * @param $questionnaireId
     * @return array
     */
    public function getQuestionnaireById($questionnaireId) {

        $titleObj = new Dao_Title();

        // 获取问卷标题
        $questionnaireTitle = $titleObj->getTitleById($questionnaireId);
        if(empty($questionnaireTitle)) {
            Library_Log::warning('questionnaireId ' . $questionnaireId . 'does not exists');
            return false;
        }

        // 获取问卷题目id
        $questionnaireQuestionIds = $titleObj->getQuestionnaireQuestionIdsById($questionnaireId);
        if(empty($questionnaireQuestionIds)) {
            Library_Log::warning('questionnaireId ' . $questionnaireId . ' questions do not exists');
            return false;
        }

        // 获取问卷题目
        $questionIds =  Library_Util::arrayColumn($questionnaireQuestionIds,'question_id');
        $questionObj = new Dao_Question();
        $questions = $questionObj->getQuestionsByIds($questionIds);
        if(empty($questions)) {
            Library_Log::warning('questionnaireId ' . $questionnaireId . ' questions do not exists');
            return false;
        }
        // 获取题目选项
        $optionObj = new Dao_Option();
        $options = $optionObj->getOptionsByQuestionIds($questionIds);
        if(empty($options)) {
            Library_Log::warning('questionnaireId ' . $questionIds . ' options do not exists');
            return false;
        }

        // 拼字段
        $retMsg = array();
        $retMsg['title'] = array(
            'questionnaire_id' => $questionnaireTitle[0]['id'],
            'content' => $questionnaireTitle[0]['title'],
        );
        $retMsg['questions'] = array();
        foreach($questionnaireQuestionIds as $key => $item) {
            $retMsg['questions'][$item['question_id']]['num'] = $key + 1;
            $retMsg['questions'][$item['question_id']]['question_id'] = $item['question_id'];
        }
        foreach($questions as $question) {
            $retMsg['questions'][$question['id']]['content'] = $question['content'];
            $retMsg['questions'][$question['id']]['type'] = $question['type'];
            $retMsg['questions'][$question['id']]['options'] = array();
        }
        foreach($options as $option) {
            $retMsg['questions'][$option['question_id']]['options'][] = array(
                'option_id' => $option['id'],
                'content' => trim($option['content']),
                'order' => strtoupper($option['order']),
            );
        }
        $retMsg['questions'] = array_values($retMsg['questions']);
        return $retMsg;
    }

    /**
     * 发布问卷
     * @param $params
     * @return bool|mysqli_result|null
     */
    public function publishQuestionnaire($params) {
        if(isset($params['operation'])) {
            unset($params['operation']);
        }
        $fields = array_keys($params);
        $rows = array($params);
        $obj = new Dao_QuestionnaireTest();
        $ret = $obj->insertIntoOptionByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 执行入口
     * @param $params
     * @return bool
     */
    public function execute($params)
    {
        // TODO: Implement execute() method.
        // 添加问卷
        $addOperation = array(
            'add_title',
            'add_question',
            'add_option',
            'add_questionnaire',
        );
        if(in_array($params['operation'], $addOperation)) {
            $res = $this->addQuestionnaire($params);
            if($params['operation'] == 'add_question') {
                return $res;
            }
        }
        if($params['operation'] == 'view_questionnaire') {
            $res = $this->getQuestionnaireById($params['id']);
            if($res === false) {
                $res = 'questions are incomplete';
            }
            return $res;
        }

        if($params['operation'] == 'publish_questionnaire') {
            $res = $this->publishQuestionnaire($params);
            return $res;
        }

        return empty($res) ? false : true;
    }

}