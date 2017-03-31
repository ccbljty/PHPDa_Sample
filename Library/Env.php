<?php
/**
 * Created by PhpStorm.
 * desc: 环境变量
 * User: bobo
 * Date: 17-1-13
 * Time: 上午11:02
 */
class Library_Env{
    /**
     * 获取全局变量$_GET
     * @param bool $isIncludeQt
     * @return mixed
     */
    static public function getGetParams($isIncludeQt = false) {
        $params = $_GET;
        if(isset($params['qt']) && !$isIncludeQt) {
            unset($params['qt']);
        }
        return $params;
    }

    /**
     * 获取全局变量$_POST
     * @return mixed
     */
    static public function getPostParams() {
        return $_POST;
    }

    /**
     * 获取全局变量$_POST,$_GET
     * @return array
     */
    static public function getAllParams() {
        $gets = self::getGetParams();
        $posts = self::getPostParams();
        $ret = array_merge($posts, $gets);
        return $ret;
    }

    /**
     * 获取全局变量$_SERVER
     * @return mixed
     */
    static public function getServerParams() {
        return $_SERVER;
    }

    /**
     * 清空session
     * @return bool
     */
    static public function clearSession() {
         $_SESSION = array();
        return true;
    }

    /**
     * 销毁session
     * @return bool
     */
    static public function destroySession() {
        session_destroy();
        return true;
    }

    /**
     * 销毁session的cookie
     * @return bool
     */
    static public function clearSessionCookie() {
        $params = session_get_cookie_params();
        self::setCookie(session_name(),'expire',time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        return true;
    }

    /**
     * 获取全局变量$_SESSION
     * @return mixed
     */
    static public function getSessionParams() {
        return $_SESSION;
    }

    /**
     * 根据key获取session
     * @param $key
     * @return mixed
     */
    static public function getSessionByKey($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * 设置session
     * @param $name
     * @param $val
     * @return bool
     */
    static public function setSession($name, $val) {
        if(empty($name) || !isset($val)) {
            return false;
        }
        $_SESSION[$name] = $val;
        return true;
    }

    /**
     * 获取全局变量$_COOKIE
     * @return mixed
     */
    static public function getCookieParams() {
        return $_COOKIE;
    }

    /**
     * 设置cookie
     * @param $name
     * @param $val
     * @param int $expire
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return bool
     */
    static public function setCookie($name, $val, $expire = 0, $path = null, $domain = null, $secure = false, $httpOnly = false) {
        if(empty($name) || empty($val)) {
            return false;
        }
        return setcookie($name, $val, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * 获取qt
     * @return string
     */
    static public function getQt(){
        $isIncludeQt = true;
        $params = self::getGetParams($isIncludeQt);
        $defaultQt = 'default';
        $qt = isset($params['qt']) ? $params['qt'] : $defaultQt;
        return $qt;
    }

    /**
     * 获取Action名字
     * @return mixed
     */
    static public function getAction() {
        $qt = self::getQt();
        $routes = Controller_Main::$qts;
        return $routes[$qt];
    }
}