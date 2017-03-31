<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_School extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_table = 'eval_school';
    }

    /**
     * 添加学校
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoSchoolByMulti($fields, $rows) {
        $res = $this->_db->insert($fields, $this->_table, $rows);
        return $res;
    }

    /**
     * 获取学校信息
     * @param $ids
     * @return array|null
     */
    public function getSchoolInfoByIds($ids = null) {
        $fields = array(
            'id',
            'edu_depart_id',
        );
        $condition = null;
        if(!empty($ids) && is_array($ids)) {
            $condition = array(
                'id in ' => '(' . implode(',', $ids). ')',
            );
        }
        $ret = $this->_db->select($fields, $this->_table, $condition);
        return $ret;
    }

     /**
     * 获取学校信息
     * @return array|null
     */
    public function getAllSchoolInfo() {
        $fields = array(
            'id',
            'name',
        );
        $ret = $this->_db->select($fields, $this->_table);
        return $ret;
    }

    /**
     * 根据指定账户获取学校信息
     * @param $account
     * @return array|null
     */
    public function getSchoolInfoByAccount($account) {
        if(empty($account)) {
            return array();
        }
        $fields = array(
            'id',
            'account',
            'passwd',
            'name',
            'edu_depart_id',
        );
        $condition['account ='] = $account;
        $ret = $this->_db->select($fields, $this->_table, $condition);
        return $ret;
    }

    public function execute() {

    }
}