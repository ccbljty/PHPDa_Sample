<?php
/**
 * 答卷
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 13:53
 */
class Action_Evaluation extends Library_Interface_Action{

    private $_individualFields;
    private $_indexQuestionFields;
    private $_schoolFields;
    private $_eduDepartFields;
    public function __construct() {
        $this->_individualFields = array(
            'stu_id',
        );

        $this->_indexQuestionFields = array(
            'question_id',
            'index_id',
            'create_time',
        );

        $this->_schoolFields = array(
            'edu_depart_id', // 教育管理部门Id
            'school_id', // 学校id
            'order_by_gender', // 按性别查看, yes是 no不是
            'grade', // 年级，','分割
//            class, // 班级，','分割
            'type',  // 查看类型 overview一级指标查看  part二级指标查看
//            'primary_index_id', // 一级指标id
        );

        $this->_eduDepartFields = array(
            'edu_depart_id', // 教育管理部门Id
            'school_id', // 学校id, 0 表示所有学校
            'order_by_gender', // 按性别查看, yes是 no不是
//            'grade', // 年级，','分割
//            class, // 班级，','分割
            'type',  // 查看类型 overview一级指标查看  part二级指标查看
//            'primary_index_id', // 一级指标id
        );

        $this->_eduPrimaryDepartFields = array(
            'edu_primary_depart_id', // 父教育管理部门Id
            'edu_depart_id', // 教育管理部门Id, 0表示所有学校
//            'school_id', // 学校id, 0 表示所有学校
            'order_by_gender', // 按性别查看, yes是 no不是
//            'grade', // 年级，','分割
//            class, // 班级，','分割
            'type',  // 查看类型 overview一级指标查看  part二级指标查看
//            'primary_index_id', // 一级指标id
        );
    }

    /**
     * 参数检查
     * @param $params
     * @return bool
     */
    public function checkParams($params)
    {
        // TODO: Implement checkParams() method.
        $fields = $this->getFieldsVariableByType($params['operation']);
        if($fields === false) {
            return false;
        }
        foreach ($this->$fields as $field) {
            if(!isset($params[$field])) {
                return false;
            }
        }
        // 查看类型校验
        if($params['operation'] != 'individual' && $params['type'] != 'overview' && empty($params['primary_index_id'])) {
            return false;
        }
        return true;
    }

    /**
     * 获取field名
     * @param $type
     * @return bool|string
     */
    private function getFieldsVariableByType($type) {
        if(empty($type)) {
            return false;
        }
        switch($type) {
            case 'individual' :
                $prefix = '_individual';
                break;
            case 'school' :
                $prefix = '_school';
                break;
            case 'edu_depart' :
                $prefix = '_eduDepart';
                break;
            case 'edu_primary_depart' :
                $prefix = '_eduPrimaryDepart';
                break;
            case 'add_question_index' :
                $prefix = '_indexQuestion';
                break;
            default :
                return false;
        }
        $fields = $prefix . 'Fields';
        return $fields;
    }


    /**
     * 入口
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        $params = Library_Env::getAllParams();
        // 学校评价参数初始化
//        if($params['operation'] == 'school') {
//            $params['school_id'] = Library_Env::getSessionByKey('school_id');
//            $params['edu_depart_id'] = Library_Env::getSessionByKey('edu_depart_id');
//        }
        // 县区评价参数初始化
//        if($params['operation'] == 'edu_depart'){
//            $params['edu_depart_id'] = Library_Env::getSessionByKey('edu_depart_id');
//        }
        // 区域评价参数初始化
//        if($params['operation'] == 'edu_primary_depart') {
//            $params['edu_primary_depart_id'] = Library_Env::getSessionByKey('edu_depart_id');
//        }

        if(isset($params['test_times']) ) {
            $testTimes = $params['test_times'];
        }
        if(isset($params['school_id']) ) {
            $schoolId = $params['school_id'];
        }
        if(isset($params['grade']) ) {
            $grade = $params['grade'];
        }
        if(isset($params['class']) ) {
            $class = $params['class'];
        }
        if(isset($params['primary_index_id']) ) {
            $primaryIndexId = $params['primary_index_id'];
        }
        $checkStatus = $this->checkParams($params);
        if(!$checkStatus) {
            $this->putParamsError();
        }
        $type = $params['operation'];
        $service = new Service_Evaluation();
        $fields = $this->getFieldsVariableByType($type);
        $params = Library_Util::arrayOrderByKeys($params,$this->$fields);
        if(isset($testTimes)) {
            $params['test_times'] = $testTimes;
        }
        if(isset($class)) {
            $params['class'] = $class;
        }
        if(isset($grade)) {
            $params['grade'] = $grade;
        }
        if(isset($schoolId)) {
            $params['school_id'] = $schoolId;
        }
        if(isset($primaryIndexId)) {
            $params['primary_index_id'] = $primaryIndexId;
        }
        $params['operation'] = $type;
        $res = $service->execute($params);
        $msg = 'fail';
        if($res && is_bool($res)) {
            $msg = 'success';
        } else if(!is_bool($res)) {
            $msg = $res;
        }
        $this->put($msg);
    }

}