<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 20:27
 */
class Service_List extends Library_Interface_Service{

    /**
     * 获取问卷list
     * @return array
     */
    public function getQuestionnaireList($page, $pageSize) {
        $ret = array();
        $titleObj = new Dao_Title();
        $list = $titleObj->getQuestionnaireList($page, $pageSize);
        if(!empty($list)) {
            foreach($list as $row) {
                $ret[] = array(
                    'questionnaire_id' => $row['id'],
                    'content' => $row['title'],
                );
            }
        }
        return $ret;
    }

    /**
     * 获取题目list
     * @return array
     */
    public function getQuestionList($page, $pageSize) {
        $ret = array();
        $titleObj = new Dao_Question();
        $list = $titleObj->getQuestionList($page, $pageSize);
        if(!empty($list)) {
            foreach($list as $row) {
                $ret[$row['id']] = array(
                    'question_id' => $row['id'],
                    'content' => $row['content'],
                    'type' => $row['type'],
                );
            }
        }
        $questionIds = Library_Util::arrayColumn($ret, 'question_id');
        // 获取题目选项
        $optionObj = new Dao_Option();
        $options = $optionObj->getOptionsByQuestionIds($questionIds);
        if(empty($options)) {
            Library_Log::warning('questionnaireId ' . $questionIds . ' options do not exists');
            return false;
        }
        // 合并问题选项
        foreach($options as $option) {
            $ret[$option['question_id']]['options'][] = array(
                'option_id' => $option['id'],
                'content' => trim($option['content']),
                'order' => strtoupper($option['order']),
            );
        }
        $ret = array_values($ret);
        return $ret;
    }

    /**
     * 获取指标
     * @param $showQuestions
     * @return array|null
     */
    public function getIndexes($showQuestions) {
        $show = false;
        if(isset($showQuestions) && $showQuestions == 1) {
            $show = true;
        }
        $indexObj = new Dao_Index();
        $res = $indexObj->getAllIndexes();
        // 显示指标下的题目id
        if($show) {
            $questionIndexes = $indexObj->getAllIndexQuestions();
            $questionIndexes = Library_Util::arrayGroup($questionIndexes, 'index_id');
            if(empty($questionIndexes)) {
                return $res;
            }
            foreach($res as & $row) {
                if(!empty($row['index2s'])) {
                    foreach($row['index2s'] as & $index) {
                        $index['question_ids'] = array();
                        if(isset($questionIndexes[$index['id']])) {
                            $index['question_ids'] = Library_Util::arrayColumn($questionIndexes[$index['id']],'question_id');
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 获取题库中剩余问题
     * @param $quesitonnaireId
     * @param $page
     * @param $pageSize
     * @return array
     */
    public function getNoExitsLibQuestionList($quesitonnaireId, $page, $pageSize) {
        $ret = array();
        $titleObj = new Dao_Question();
        $list = $titleObj->getNoExitsLibQuestionList($quesitonnaireId, $page, $pageSize);
        $start = ($page-1)*$pageSize + 1;
        if(!empty($list)) {
            foreach($list as $key => $row) {
                $ret[] = array(
                    'num' => $start + $key,
                    'question_id' => $row['id'],
                    'content' => $row['content'],
                    'type' => $row['type'],
                );
            }
        }
        return $ret;
    }

    public function execute($params)
    {
        // TODO: Implement execute() method.
        $ret = array();
        if($params['operation'] == 'questionnaire_list') {
            $ret = $this->getQuestionnaireList($params['page'], $params['page_size']);
        }
        if($params['operation'] == 'question_list') {
            $ret = $this->getQuestionList($params['page'], $params['page_size']);
        }
        // 获取题库中剩余问题
        if($params['operation'] == 'no_exists_questionnaire_list') {
            $ret = $this->getNoExitsLibQuestionList($params['questionnaire_id'], $params['page'], $params['page_size']);
        }

        // 获取指标
        if($params['operation'] == 'get_indexes') {
            $ret = $this->getIndexes($params['show_questions']);
        }
        return $ret;
    }

}