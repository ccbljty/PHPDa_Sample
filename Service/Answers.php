<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 20:30
 */
class Service_Answers extends Library_Interface_Service{

    /**
     * 提交问卷
     * @param $params
     * @return bool|mysqli_result|null
     */
    public function submitAnswer($params) {
        $fields = array_keys($params);
        $rows = array($params);
        $answerObj = new Dao_Answers();
        $res = $answerObj->insertAnswerByMulti($fields, $rows);
        return $res;
    }


    public function execute($params)
    {
        // TODO: Implement execute() method.
        $ret = $this->submitAnswer($params);
        return $ret;
    }
}