<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:29
 */
class Dao_Evaluation extends Library_Interface_Dao{
    private $_db ;
    private $_table;
    function __construct()
    {
        $this->_db = new Library_Db();
        $this->_table = 'eval_evaluation';
    }

    /**
     * 添加评价
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insertIntoEvaluationByMulti($fields, $rows) {
        if(!is_array($fields) || !is_array($rows) || empty($fields) || empty($rows)) {
            return false;
        }
        $res = $this->_db->insert($fields, $this->_table, $rows);
        if($res === true && $this->_db->insertId > 0) {
            $res = true;
        }else{
            Library_Log::warning('insert failed');
            $res = false;
        }
        unset($rows, $fields);
        return $res;
    }

    /**
     * 获取学生评价成绩
     * @param $eduDepartId
     * @param $schoolId
     * @param $testTimes
     * @return array|null
     */
    public function getEvaluationByEduDepartIdSchoolId($eduDepartId, $schoolId = null, $testTimes = null){
        $fields = array(
            'score',
            'gender',
            'test_times',
            'edu_depart_id',
            'school_id',
            'grade',
            'class',
            'sno',
        );
        $condition = null;
        if(isset($eduDepartId)) {
            $condition['edu_depart_id ='] = $eduDepartId;
        }
        if(null !== $schoolId) {
            $condition['school_id ='] = $schoolId;
        }
        if(null !== $testTimes) {
            $condition['test_times ='] = $testTimes;
        }
        $res = $this->_db->select($fields, $this->_table, $condition);
        return $res;
    }

    /**
     * 获取导航信息
     * @param $eduDepartId
     * @param null $schoolId
     * @param int $testTimes
     * @return array|null
     */
     public function getNavigateByEduDepartIdSchoolId($eduDepartId, $schoolId = null, $testTimes = 1){
        $fields = array(
            'edu_depart_id',
            'school_id',
            'grade',
            'class',
        );
        $condition = null;
        if(isset($eduDepartId)) {
            $condition['edu_depart_id ='] = $eduDepartId;
        }
        if(null !== $schoolId) {
            $condition['school_id ='] = $schoolId;
        }
         $preOperation = array(
             'distinct'
         );
        $res = $this->_db->select($fields, $this->_table, $condition, null, $preOperation);
        return $res;
    }



    public function execute() {
    }
}