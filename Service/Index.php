<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 20:30
 */
class Service_Index extends Library_Interface_Service{

    /**
     * 添加指标
     * @param $params
     * @return bool|mysqli_result|null
     */
    public function addIndex($params) {
        $fields = array_keys($params);
        $rows = array($params);
        $indexObj = new Dao_Index();
        $res = $indexObj->addIndex($fields, $rows);
        return $res;
    }

    /**
     * 添加问卷指标
     * @param $params
     * @return bool|mysqli_result|null
     */
    public function addQuestionIndex($params) {
        if(empty($params['question_id']) || empty($params['index_id']) || empty($params['create_time']) ) {
            return false;
        }
        $fields = array_keys($params);
        $rows = array();
        $questions = explode(',', $params['question_id']);
        foreach($questions as $one) {
            $rows[] = array(
                $one,
                $params['index_id'],
                $params['create_time'],
            );
        }
        $indexObj = new Dao_Index();
        $res = $indexObj->addQuestionIndex($fields, $rows);
        return $res;
    }


    public function execute($params)
    {
        // TODO: Implement execute() method.
        $operation = $params['operation'];
        unset($params['operation']);
        if($operation == 'add_index') {
            $ret = $this->addIndex($params);
        }
        if($operation == 'add_question_index') {
            $ret = $this->addQuestionIndex($params);
        }
        return $ret;
    }
}