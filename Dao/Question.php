<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Question extends Library_Interface_Dao{
    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db('questionnaires_db');
        $this->_table = 'que_question';
    }

    /**
     * 添加题目
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoQuestionByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        if($res === true) {
            return $this->_db->insertId;
        }
        return $res;
    }

    /**
     * 根据问题ids查询问题
     * @param $questionIds
     * @return array|bool|null
     */
    public function getQuestionsByIds($questionIds) {
        if(empty($questionIds) || !is_array($questionIds)) {
            return false;
        }
        $questionIds = implode(',', $questionIds);
        $fields = array(
            'id',
            'content',
            'type',
        );
        $condition = array(
            'id in' => '(' . $questionIds . ')',
        );
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }

    /**
     * 根据问题list
     * @param int $page
     * @param int $pageSize
     * @return array|null
     */
    public function getQuestionList($page = 1, $pageSize = 10) {
        $fields = array(
            'id',
            'content',
            'type',
        );
        $start = ($page-1)*$pageSize;
        $append = array(
            'limit ' . $start . ', ' . $pageSize,
        );
        $res = $this->_db->select($fields, $this->_table, null, $append);
        return $res;
    }

    /**
     * 获取题库中剩余问题
     * @param $questionnaireId
     * @param int $page
     * @param int $pageSize
     * @return array|bool|mysqli_result
     */
    public function getNoExitsLibQuestionList($questionnaireId, $page = 1, $pageSize = 10) {
        if(empty($questionnaireId)) {
            return false;
        }
        $titleQuestionTable = 'que_title_question';
        $start = ($page-1)*$pageSize;
        $sql = 'SELECT b.`id`, b.`content`, b.`type` FROM ' . $titleQuestionTable . ' a RIGHT JOIN ';
        $sql .= $this->_table . ' b ON a.`question_id` = b.`id` WhERE a.`questionnaire_id` != ' . $questionnaireId . ' OR a.`questionnaire_id` IS NULL';
        $sql .= ' LIMIT ' . $start . ', ' . $pageSize;
        $res = $this->_db->query($sql);
        return $res;
    }

    /**
     * 更新题目
     * @param $questionId
     * @param $fields
     * @return bool|mysqli_result|null|string
     */
    public function updateQuestionById($questionId, $fields) {
        if(empty($questionId) || empty($fields)) {
            return false;
        }
        $condition = array(
            'id =' => $questionId,
        );
        $res = $this->_db->update($fields, $this->_table, $condition);
        if($res === true && $this->_db->affectedNum == 0) {
            return 'there is no row that is affected';
        }
        return $res;
    }

    /**
     * 获取题库问题数量
     * @return int
     */
    public function getAllQuestionsNum() {
        $num = 0;
        $sql = 'SELECT count(*) num FROM ' . $this->_table;
        $res = $this->_db->query($sql);
        if(empty($res)) {
            return $num;
        }
        $num = $res[0]['num'];
        if(empty($num)) {
            $num = 0;
        }
        return $num;
    }

    public function execute() {
    }
}