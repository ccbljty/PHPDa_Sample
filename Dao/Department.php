<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Department extends Library_Interface_Dao{

    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_table = 'eval_department';
    }

    /**
     * 添加专家
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoDepartmentByMulti($rows) {
        $fields = array(
            'account',
            'passwd',
            'name',
            'province',
            'city',
            'county',
            'parent_id',
            'create_time',
        );
        $res = $this->_db->insert($fields, $this->_table, $rows);
        return $res;
    }

    /**
     * 根据parentId获取区县教育管理部门信息
     * @param $parentId
     * @return array|null
     */
    public function getCountyInfoByParentId($parentId){
        if(empty($parentId)) {
            return array();
        }
        $fields = array(
            'id',
            'name',
        );
        $condition['parent_id ='] = $parentId;
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }

    /**
 * 根据账户获取教育管理部门信息
 * @param $account
 * @return array|null
 */
    public function getDepartmentInfoByAccount($account){
        if(empty($account)) {
            return array();
        }
        $fields = array(
            'id',
            'account',
            'passwd',
            'name',
            'parent_id',
        );
        $condition['account ='] = $account;
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }


    public function execute() {

    }
}