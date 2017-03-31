<?php
/**
 * Created by PhpStorm.
 * desc: 配置文件工具
 * User: bobo
 * Date: 17-1-15
 * Time: 下午3:07
 */
class Library_Conf {

    /**
     * 加载配置文件并格式化
     * @param $path
     * @return array
     */
    static private function getFileContent($path) {
        $content = file($path);
        $conf = array();
        if(empty($content)) {
            return $conf;
        }
        foreach ($content as $item) {
            $item = preg_replace('/\s*/', '', $item);
            if(empty($item) || strpos($item, '#') !== false) {
                continue;
            }
            $newItem =  Library_Util::trimTool($item);
            if(preg_match('/\[/', $item)) {
                $name = $newItem;
            } else {
                $arr = explode(':', $newItem);
                if(isset($name) && !empty($name)) {
                    $conf[$name][$arr[0]] = $arr[1];
                } else {
                    $conf[$arr[0]] = $arr[1];
                }
            }
        }
        return $conf;
    }

    /**
     * 根据配置名获取配置信息
     * @param $confName
     * @return array
     */
    static public function getConf($confName) {
        $path = CONF_DIR . $confName . '.conf';
        return self::getFileContent($path);
    }
}