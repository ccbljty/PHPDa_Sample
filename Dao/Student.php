<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Student extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_table = 'eval_student';
    }

    /**
     * 添加专家
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoStudentByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        return $res;
    }

    /**
     * 获取所有学生信息
     * @return array|null
     */
    public function getAllStudentInfo() {
        $fields = array(
            'account',
            'gender',
            'school_id',
            'grade',
            'class',
        );
        $ret = $this->_db->select($fields, $this->_table);
        return $ret;
    }

    /**
     * 根据账号获取学生信息
     * @param $account
     * @return array|null
     */
    public function getStudentInfoByAccount($account) {
        if(empty($account)) {
            return array();
        }
        $fields = array(
            'account',
            'passwd',
            'name',
        );
        $condition['account ='] = $account;
        $ret = $this->_db->select($fields, $this->_table, $condition);
        return $ret;
    }

    public function execute() {

    }
}