<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:58
 */
class Service_Navigate extends Library_Interface_Service{

    /**
     * 根据schoolId获取学校信息
     * @param $schoolId
     * @return array|bool|null
     */
    private function getSchoolInfoBySchoolId($schoolId) {
        $schoolInfo = array();
        if(empty($schoolId)) {
            return $schoolInfo;
        }
        $schoolDao = new Dao_School();
        $ids = array($schoolId);
        $schoolInfo = $schoolDao->getSchoolInfoByIds($ids);
        $schoolInfo = $schoolInfo[0];
        return $schoolInfo;
    }

    /**
     * 获取学校导航信息
     * @param $schoolId
     * @return array
     */
    public function getNavigateBySchoolId($schoolId) {
        $navigate = array();
        if(empty($schoolId)) {
            return $navigate;
        }
        $schoolInfo = $this->getSchoolInfoBySchoolId($schoolId);
        $eduDepartId = $schoolInfo['edu_depart_id'];
        $evaluationDao = new Dao_Evaluation();
        $res = $evaluationDao->getNavigateByEduDepartIdSchoolId($eduDepartId, $schoolId);
        $navigate['grade'] = array_values(array_unique(Library_Util::arrayColumn($res, 'grade')));
        $navigate['class'] = array_values(array_unique(Library_Util::arrayColumn($res, 'class')));
        array_unshift($navigate['grade'], 'all');
        array_unshift($navigate['class'], 'all');
        return $navigate;
    }

    /**
     * 教育管理部门导航
     * @param $eduDepartId
     * @return array
     */
    public function getNavigateByEduPartId($eduDepartId) {
        $navigate = array();
        if(empty($eduDepartId)) {
            return $navigate;
        }
        $departDao = new Dao_Department();
        $departRes = $departDao->getCountyInfoByParentId($eduDepartId);
        // 区域级
        if(!empty($departRes)) {
            $navigate['edu_depart'][] = array('edu_depart_id' => 0, 'edu_depart_name' => 'all');
            foreach($departRes as $one) {
                $navigate['edu_depart'][] = array(
                    'edu_depart_id' => $one['id'],
                    'edu_depart_name' => $one['name'],
                );
            }
            unset($one);
        }
        $schoolDao = new Dao_School();
        $allSchoolInfo = $schoolDao->getAllSchoolInfo();
        $allSchoolInfo = Library_Util::arrayIndex($allSchoolInfo, 'id');
        $evaluationDao = new Dao_Evaluation();
        // 区域级
        if(!empty($departRes)) {
            $res = array();
            foreach($departRes as $one) {
                $tmpRes = $evaluationDao->getNavigateByEduDepartIdSchoolId($one['id']);
                $res = array_merge($res, $tmpRes);
            }
        }
        // 县区级
        else{
            $res = $evaluationDao->getNavigateByEduDepartIdSchoolId($eduDepartId);
        }
        $navigate['school'] = array_values(array_unique(Library_Util::arrayColumn($res, 'school_id')));
        $navigate['grade'] = array_values(array_unique(Library_Util::arrayColumn($res, 'grade')));
        $navigate['class'] = array_values(array_unique(Library_Util::arrayColumn($res, 'class')));
        foreach($navigate['school'] as $key => $one) {
            $navigate['school'][$key] = array(
                'school_id' => $one,
                'school_name' => $allSchoolInfo[$one]['name'],
            );
        }
        array_unshift($navigate['school'], array('school_id' => 0, 'school_name' => 'all'));
        array_unshift($navigate['grade'], 'all');
        array_unshift($navigate['class'], 'all');
        return $navigate;
    }

    /**
     * 执行入口
     * @param $params
     * @return bool
     */
    public function execute($params) {
        $navigate = array();
        // 添加缓存
        $redis = new Library_Redis();
        $key = 'navigate_' . $params['operation'];
        if($params['operation'] == 'school') {
            $key .= '_' . $params['school_id'];
            $navigate = $redis->get($key);
            $navigate = json_decode(gzuncompress($navigate), true);
            if(empty($navigate)) {
                $navigate = $this->getNavigateBySchoolId($params['school_id']);
                $redis->setex($key, REDIS_TTL, gzcompress(json_encode($navigate)));
            }
        }elseif($params['operation'] == 'edu_depart') {
            $key .= '_' . $params['edu_depart_id'];
            $navigate = $redis->get($key);
            $navigate = json_decode(gzuncompress($navigate), true);
            if(empty($navigate)) {
                $navigate = $this->getNavigateByEduPartId($params['edu_depart_id']);
                $redis->setex($key, REDIS_TTL, gzcompress(json_encode($navigate)));
            }
        }
        return $navigate;
    }

}