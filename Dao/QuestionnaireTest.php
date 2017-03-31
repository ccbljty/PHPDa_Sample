<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_QuestionnaireTest extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db('questionnaires_db');
        $this->_table = 'que_test_times';
    }

    /**
     * 发布问卷
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoOptionByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        if($res === true && $this->_db->insertId == 0) {
            return 'already published';
        }
        return $res;
    }

    /**
     * 获取发布次数
     * @param $questionnaireId
     * @return int
     */
    public function getQuestionnaireTestTimesByid($questionnaireId) {
        $maxTimes = 0;
        if(empty($questionnaireId)) {
            return $maxTimes;
        }
        $sql = 'SELECT max(test_times) max_times FROM ' . $this->_table . ' WHERE questionnaire_id = ' . $questionnaireId;
        $res = $this->_db->query($sql);
        if(empty($res)) {
            return $maxTimes;
        }
        $maxTimes = $res[0]['max_times'];
        if(empty($maxTimes)) {
            $maxTimes = 0;
        }
        return $maxTimes;
    }
    public function execute() {
    }
}