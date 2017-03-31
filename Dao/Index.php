<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Index extends Library_Interface_Dao{

    private $_db ;
    private $_indexTable;
    private $_indexQuestionTable;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_indexTable = 'eval_index';
        $this->_indexQuestionTable = 'eval_question_index';
    }


    /**
     * 添加指标
     * @param $fields
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addIndex($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_indexTable, $rows);
        if($res == true && $this->_db->insertId > 0) {
            $res = true;
        } else{
            $res = false;
        }
        return $res;
    }

    /**
     * 获取所有的指标
     * @return array|null
     */
    public function getAllIndexes() {
        $fields = array(
            'id',
            'content',
            'weight',
            'parent_id',
        );
        $resArr = $this->_db->select($fields, $this->_indexTable);
        $arr = array();
        foreach($resArr as $row) {
            if($row['parent_id'] == 0) {
                $arr[$row['id']] = array(
                    'id' => $row['id'],
                    'content' => $row['content'],
                    'weight' => $row['weight'],
                    'index2s' => array(),
                );
            } else{
                $arr[$row['parent_id']]['index2s'][] = array(
                    'id' => $row['id'],
                    'content' => $row['content'],
                    'weight' => $row['weight'],
                );
            }
        }
        $arr = array_values($arr);
        return empty($arr) ? array() : $arr;
    }


    /**
     * 为问卷添加指标
     * @param $fields
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addQuestionIndex($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_indexQuestionTable, $rows);
        if($res == true && $this->_db->insertId > 0) {
            $res = true;
        } else{
            $res = false;
        }
        return $res;
    }

    /**
     * 获取所有问题指标
     * @return array|null
     */
    public function getAllIndexQuestions() {
        $fields = array(
            'question_id',
            'index_id',
        );
        $ret = $this->_db->select($fields, $this->_indexQuestionTable);
        return empty($ret) ? array() : $ret;
    }

    public function execute() {
    }
}