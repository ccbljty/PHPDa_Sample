<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Title extends Library_Interface_Dao{

    private $_db ;
    private $_queTitleTable ;
    private $_queTitleQuestionTable ;
    function __construct()
    {
        $this->_db = new Library_Db('questionnaires_db');
        $this->_queTitleTable = 'que_title';
        $this->_queTitleQuestionTable = 'que_title_question';
    }

    /**
     * 添加问卷标题
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoTitleByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_queTitleTable, $rows);
        return $res;
    }

    /**
     * 组卷
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoQuestionnaireByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_queTitleQuestionTable, $rows);
        return $res;
    }

    /**
     *  问卷中删除题目
     * @param $questionnaireId
     * @param $questionIds
     * @return bool|mysqli_result|null|string
     */
    public function deleteQuestionnaireQuestionsById($questionnaireId, $questionIds) {
        if(empty($questionnaireId) || empty($questionIds)) {
            return false;
        }
        $questionIds = implode(',', $questionIds);
        $condition = array(
            'questionnaire_id = ' => $questionnaireId,
            'question_id in ' => '(' . $questionIds . ')',
        );
        $res = $this->_db->delete($this->_queTitleQuestionTable, $condition);
        if($res === true && $this->_db->affectedNum == 0) {
            return 'there is no row that is affected';
        }
        return $res;
    }

    /**
     * 获取问卷title
     * @param $questionnaireId
     * @return array|null
     */
    public function getTitleById($questionnaireId) {
        $fields = array(
            'id',
            'title',
        );
        $condition = array(
            'id =' => $questionnaireId,
        );
        $res = $this->_db->select($fields, $this->_queTitleTable, $condition);
        return $res;
    }

    /**
     * 获取问卷id及问题id
     * @param $questionnaireId
     * @return array|null
     */
    public function getQuestionnaireQuestionIdsById($questionnaireId) {
        $fields = array(
            'questionnaire_id',
            'question_id',
        );
        $condition = array(
            'questionnaire_id =' => $questionnaireId,
        );
        $res = $this->_db->select($fields, $this->_queTitleQuestionTable, $condition);
        return $res;
    }

    /**
     * 问卷list
     * @param int $page
     * @param int $pageSize
     * @return array|null
     */
    public function getQuestionnaireList($page = 1, $pageSize = 10) {
        $fields = array(
            'id',
            'title',
        );
        $start = ($page-1)*$pageSize;
        $append = array(
            'limit ' . $start . ', ' . $pageSize,
        );
        $res = $this->_db->select($fields, $this->_queTitleTable, null, $append);
        return $res;
    }

    /**
     * 更新问卷标题
     * @param $questionnaireId
     * @param $title
     * @return bool|mysqli_result|null
     */
    public function updateQuestionnaireTitleById($questionnaireId, $title) {
        if(empty($questionnaireId) || empty($title)) {
            return false;
        }
        $fields = array(
            'title' => $title,
        );
        $condition = array(
            'id =' => $questionnaireId,
        );
        $res = $this->_db->update($fields, $this->_queTitleTable, $condition);
        if($res === true && $this->_db->affectedNum == 0) {
            return 'there is no row that is affected';
        }
        return $res;
    }


    /**
     * 获取问卷下面的问题数
     * @param $questionnaireId
     * @return int
     */
    public function getQuestionsNumByid($questionnaireId) {
        $num = 0;
        if(empty($questionnaireId)) {
            return $num;
        }
        $sql = 'SELECT count(*) num FROM ' . $this->_queTitleQuestionTable . ' WHERE questionnaire_id = ' . $questionnaireId;
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

    /**
     * 获取问卷数量
     * @return int
     */
    public function getAllQuestionnaireNum() {
        $num = 0;
        $sql = 'SELECT count(*) num FROM ' . $this->_queTitleTable;
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