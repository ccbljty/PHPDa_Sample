<?php
/**
 * Created by PhpStorm.
 * User: bobo
 * desc: Action基类
 * Date: 17-1-13
 * Time: 下午2:41
 */
abstract class Library_Interface_Action{
    private $_defaultContent;
    protected $_params;
    function __construct() {
        $this->_defaultContent = 'here is no content';
    }

    /**
     * 格式化数据为json格式并输出
     * @param $content
     */
    private function putJson($content) {
        header('Content-Type:text/javascript;charset=utf-8');
        echo json_encode($content);
    }

    /**
     * 格式化数据为text格式并输出
     * @param $content
     */
    private function putText($content) {
        header('Content-Type:text/javascript;charset=utf-8');
        echo json_encode($content);
    }

    /**
     * 输出参数错误信息
     */
    protected function putParamsError() {
        $retMsg = array(
            'err_no' => 1000,
            'err_msg' => 'params are illegal',
        );
        Library_Log::warning($retMsg['err_msg']);
        $this->putJson($retMsg);
        exit;
    }

    /**
     * 程序输出
     * @param $content
     */
    protected function put($content) {
        $content = empty($content) ? $this->_defaultContent : $content ;
        Library_Log::trace('query info : ' . json_encode($this->_params));
        $retMsg['err_no'] = 0;
        $retMsg['data'] = $content;
        $this->putJson($retMsg);
    }

    /**
     * 参数检查
     * @return bool
     */
    abstract protected function checkParams($params);

    /**
     * 程序执行
     */
    abstract protected function execute();

}