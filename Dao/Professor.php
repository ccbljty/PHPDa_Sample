<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Professor extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_table = 'eval_admin';
    }

    /**
     * 添加专家
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoAdminByMulti($rows) {
        $fields = array(
            'account',
            'passwd',
            'name',
            'level',
        );
        $res = $this->_db->insert($fields, $this->_table, $rows);
        return $res;
    }

    /**
     * 根据账户获取专家管理员信息
     * @param $account
     * @return array|null
     */
    public function getProfessorInfoByAccount($account){
        if(empty($account)) {
            return array();
        }
        $fields = array(
            'account',
            'passwd',
            'name',
            'level',
        );
        $condition['account ='] = $account;
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }

    public function execute() {

    }
}