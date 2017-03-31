<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:58
 */
class Service_Register extends Library_Interface_Service{

    /**
     * 添加学生
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addStudentByMulti($fields, $rows) {
        $professor = new Dao_Student();
        $ret = $professor->insertIntoStudentByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 添加学校
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addSchoolByMulti($fields, $rows) {
        $professor = new Dao_School();
        $ret = $professor->insertIntoSchoolByMulti($fields, $rows);
        return $ret;
    }

    /**
     * 执行入口
     * @param $params
     * @return bool
     */
    public function execute($params)
    {
        // TODO: Implement execute() method.
        $type = $params['type'];
        unset($params['type']);
        $fields = array_keys($params);
        if($type == 'student') {
            $rows = array($params);
            $res = $this->addStudentByMulti($fields, $rows);
        }
        if($type == 'school') {
            $rows = array($params);
            $res = $this->addSchoolByMulti($fields, $rows);
        }
        return empty($res) ? false : true;
    }

}