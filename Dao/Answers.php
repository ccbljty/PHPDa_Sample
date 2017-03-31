<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Answers extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db('questionnaires_db');
        $this->_table = 'que_answer';
    }


    /**
     * 填问卷
     * @param $fields
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertAnswerByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        if($res == true && $this->_db->insertId > 0) {
            $res = true;
        } else{
            $res = false;
        }
        return $res;
    }

    /**
     * 获取取所有人答案
     * @param int $page
     * @param int $pageSize
     * @param $testTimes
     * @return array|null
     */
    public function getAllAnswers($page = 1, $pageSize = 10, $testTimes) {
        $questionnaireIds = Library_Conf::getConf('questionnaire');
        $questionnaireIds = $questionnaireIds['questionnaire_id'];
        $fields = array(
            'sno',
            'answer',
            'test_times',
        );
        $start = ($page-1)*$pageSize;
        $append = array(
            'limit ' . $start . ', ' . $pageSize,
        );
        $condition = null;
        $condition = array(
            'questionnaire_id in' => '(' . $questionnaireIds . ')',
        );
        if(0 !== $testTimes) {
            $condition['test_times ='] = $testTimes;
        }
        $res = $this->_db->select($fields, $this->_table, $condition, $append);
        return $res;
    }

    /**
     * 获取答案
     * @param $stuId
     * @param $testTimes
     * @return array|bool|null
     */
    public function getIndividualAnswer($stuId, $testTimes) {
        if(empty($stuId)) {
            return false;
        }
        $fields = array(
            'answer',
            'test_times',
        );
        $condition = array(
            'sno =' => $stuId,
        );
        if(!empty($testTimes)) {
            $condition['test_times ='] = $testTimes;
        }
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }


    public function execute() {
    }
}