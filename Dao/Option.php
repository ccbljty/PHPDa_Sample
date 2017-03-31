<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Option extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db('questionnaires_db');
        $this->_table = 'que_option';
    }

    /**
     * 添加问卷标题
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoOptionByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        return $res;
    }

    /**
     * 根据问题id获取选项
     * @param $questionIds
     * @return array|bool|null
     */
    public function getOptionsByQuestionIds($questionIds) {
        if(empty($questionIds) || !is_array($questionIds)) {
            return false;
        }
        $questionIds = implode(',', $questionIds);
        $fields = array(
            'id',
            'content',
            'question_id',
            'order',
        );
        $condition = array(
            'question_id in' => '(' . $questionIds . ')',
        );
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }

    /**
     * 更新选项
     * @param $optionId
     * @param $fields
     * @return bool|mysqli_result|null|string
     */
    public function updateOptionById($optionId, $fields) {
        if(empty($optionId) || empty($fields)) {
            return false;
        }
        $condition = array(
            'id =' => $optionId,
        );
        $res = $this->_db->update($fields, $this->_table, $condition);
        if($res === true && $this->_db->affectedNum == 0) {
            return 'there is no row that is affected';
        }
        return $res;
    }

    /**
     * 删除选项
     * @param $optionIds
     * @return bool|mysqli_result|null|string
     */
    public function deleteOptionByOptionIds($optionIds) {
        if(empty($optionIds)) {
            return false;
        }
        $condition = array(
            'id IN' => '(' . $optionIds . ')',
        );
        $res = $this->_db->delete($this->_table, $condition);
        if($res === true && $this->_db->affectedNum == 0) {
            $res = 'there is no row that is affected';
        } elseif($this->_db->affectedNum > 0) {
            $res = true;
        }else{
            $res = false;
        }
        return $res;
    }


    public function execute() {
    }
}