<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 16:58
 */
class Service_Login extends Library_Interface_Service{

    private $_loginSuccess;
    private $_accountError;
    private $_passError;
    private $_logoutMsg;

    function __construct() {
        $this->_loginSuccess = 'login success';
        $this->_accountError = 'account error';
        $this->_passError = 'pass error';
        $this->_logoutMsg = 'logout success';
    }

    /**
     * 验证学生登陆
     * @param $account
     * @param $pass
     * @param $loginType
     * @return mixed
     */
    private function validateStudentLogin($account, $pass, $loginType) {
        $studentDao = new Dao_Student();
        $info = $studentDao->getStudentInfoByAccount($account);
        if(empty($info) || empty($info[0])) {
            return  $this->_accountError;
        }
        if($pass == $info[0]['passwd']) {
            Library_Env::setSession('account', $account);
            Library_Env::setSession('login_type', $loginType);
            Library_Env::setSession('name', $info[0]['name']);
            return $this->_loginSuccess;
        }else {
            return $this->_passError;
        }
    }

    /**
     * 验证学校登陆
     * @param $account
     * @param $pass
     * @param $loginType
     * @return string
     */
    private function validateSchoolLogin($account, $pass, $loginType) {
        $schoolDao = new Dao_School();
        $info = $schoolDao->getSchoolInfoByAccount($account);
        if(empty($info) || empty($info[0])) {
            return  $this->_accountError;
        }
        if($pass == $info[0]['passwd']) {
            Library_Env::setSession('account', $account);
            Library_Env::setSession('login_type', $loginType);
            Library_Env::setSession('name', $info[0]['name']);
            Library_Env::setSession('school_id', $info[0]['id']);
            Library_Env::setSession('edu_depart_id', $info[0]['edu_depart_id']);
            return $this->_loginSuccess;
        }else {
            return $this->_passError;
        }
    }

    /**
     * 教育管理部门登陆
     * @param $account
     * @param $pass
     * @param $loginType
     * @return string
     */
    private function validateEduDepartLogin($account, $pass, $loginType) {
        $departmentDao = new Dao_Department();
        $info = $departmentDao->getDepartmentInfoByAccount($account);
        if(empty($info) || empty($info[0])) {
            return  $this->_accountError;
        }
        if($pass == $info[0]['passwd']) {
            Library_Env::setSession('account', $account);
            Library_Env::setSession('login_type', $loginType);
            Library_Env::setSession('name', $info[0]['name']);
            Library_Env::setSession('edu_depart_id', $info[0]['id']);
            $isPrimaryEduDepart = $info[0]['parent_id'] == 0 ? 'yes' : 'no';
            Library_Env::setSession('is_primary_edu_depart', $isPrimaryEduDepart);
            if($isPrimaryEduDepart == 'no') {
                Library_Env::setSession('primary_edu_depart_id', $info[0]['parent_id']);
            }
            return $this->_loginSuccess;
        }else {
            return $this->_passError;
        }
    }

    /**
     * 管理员登陆
     * @param $account
     * @param $pass
     * @param $loginType
     * @return string
     */
    private function validateAdminLogin($account, $pass, $loginType) {
        $professorDao = new Dao_Professor();
        $info = $professorDao->getProfessorInfoByAccount($account);
        if(empty($info) || empty($info[0])) {
            return  $this->_accountError;
        }
        if($pass == $info[0]['passwd']) {
            Library_Env::setSession('account', $account);
            Library_Env::setSession('login_type', $loginType);
            Library_Env::setSession('name', $info[0]['name']);
            Library_Env::setSession('role', $info[0]['level']);
            return $this->_loginSuccess;
        }else {
            return $this->_passError;
        }
    }

    /**
     * 验证登陆状态
     * @return array|string
     */
    private function validateLoginStatus() {
        $status = array(
            'status' => 'offline'
        );
        $account = Library_Env::getSessionBykey('account');
        if(!empty($account)) {
            $status = array(
                'status' => 'online',
                'login_type' => Library_Env::getSessionBykey('login_type'),
                'account' => $account,
                'name' => Library_Env::getSessionBykey('name'),
            );
            $schoolId = Library_Env::getSessionBykey('school_id');
            $eduDepartId = Library_Env::getSessionBykey('edu_depart_id');
            $isPrimaryEduDepart = Library_Env::getSessionBykey('is_primary_edu_depart');
            $primaryEduDepartId = Library_Env::getSessionBykey('primary_edu_depart_id');
            $role = Library_Env::getSessionBykey('role');
            if(!empty($schoolId)) {
                $status['school_id'] = $schoolId;
            }
            if(!empty($eduDepartId)) {
                $status['edu_depart_id'] = $eduDepartId;
            }
            if(!empty($isPrimaryEduDepart)) {
                $status['is_primary_edu_depart'] = $isPrimaryEduDepart;
            }
            if(!empty($primaryEduDepartId)) {
                $status['primary_edu_depart_id'] = $primaryEduDepartId;
            }
            if(isset($role)) {
                $status['role'] = $role;
            }
        }
        return $status;
    }

    /**
     * 验证登陆状态
     * @return array|string
     */
    private function logout() {
        Library_Env::clearSession();
        Library_Env::destroySession();
        Library_Env::clearSessionCookie();
        return $this->_logoutMsg;
    }

    /**
     * 执行入口
     * @param $params
     * @return bool
     */
    public function execute($params) {
        $status = 'login fail';
        // 过滤字符
        foreach($params as $key => $one) {
            $params[$key] = trim($one);
        }
        // 登陆
        if($params['operation'] == 'login') {
            // 初始化登陆状态
            Library_Env::clearSession();
            switch($params['login_type']) {
                case 'student' :
                    $status = $this->validateStudentLogin($params['account'], $params['pass'], $params['login_type']);
                    break;
                case 'school' :
                    $status = $this->validateSchoolLogin($params['account'], $params['pass'], $params['login_type']);
                    break;
                case 'edu_depart' :
                    $status = $this->validateEduDepartLogin($params['account'], $params['pass'], $params['login_type']);
                    break;
                case 'admin' :
                    $status = $this->validateAdminLogin($params['account'], $params['pass'], $params['login_type']);
                    break;
                default :
                    break;
            }
        }
        // 校验登陆
        elseif($params['operation'] == 'validate') {
            $status = $this->validateLoginStatus();
        }
        // 注销登陆
        elseif($params['operation'] == 'logout') {
            $status = $this->logout();
        }
        return $status;
    }

}