<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:58
 */
class Service_OpenAccount extends Library_Interface_Service{

    /**
     * 添加专家
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addProfessorByMulti($rows) {
        $professor = new Dao_Professor();
        $ret = $professor->insertIntoAdminByMulti($rows);
        return $ret;
    }

    /**
     * 添加教育管理部门
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function addDepartmentByMulti($rows) {
        $professor = new Dao_Department();
        $ret = $professor->insertIntoDepartmentByMulti($rows);
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

        if($params['type'] == 'admin') {
            $rows[0] = array(
                $params['account'],
                $params['passwd'],
                $params['name'],
                1,
            );
            $res = $this->addProfessorByMulti($rows);
        }
        if($params['type'] == 'department') {
            $rows[0] = array(
                $params['account'],
                $params['passwd'],
                $params['name'],
                $params['province'],
                $params['city'],
                $params['county'],
                $params['parent_id'],
                date('Y-m-d H:i:s', time()),
            );
            $res = $this->addDepartmentByMulti($rows);
        }
        return empty($res) ? false : true;
    }

}