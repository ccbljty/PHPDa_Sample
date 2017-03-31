<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * desc: 日志
 * Date: 17-1-13
 * Time: 下午9:37
 */

class Library_Log {
    /**
     * 格式化日志格式
     * @param $log
     * @return string
     */
    static private function formatLog($log) {
        if(!preg_match('/\\n/', $log)) {
            $log .= "\n";
        }
        $formatArr = array(
            'req_id' => REQ_ID,
            'date' =>  date('Y-m-d H:i:s', time()),
            'qt' => Library_Env::getQt(),
            'msg' => $log,
        );
        $str = '';
        foreach ($formatArr as $key => $item) {
            $str .= $key . ':' . $item . ',';
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    /**
     * 普通日志
     * @param $log
     */
    static public function trace($log) {
        $log = self::formatLog($log);
        file_put_contents(TRACE_PATH , $log, FILE_APPEND);
    }

    /**
     * 警告类日志
     * @param $log
     */
    static public function warning($log) {
        $log = self::formatLog($log);
        file_put_contents(WARNING_PATH , $log, FILE_APPEND);
    }
}