<?php
/**
 * Created by PhpStorm.
 * desc: 小工具
 * User: bobo
 * Date: 17-1-15
 * Time: 下午5:20
 */

class Library_Util{
    /**
     * 字符裁剪
     * @param $str
     * @return string
     */
    static public function trimTool($str) {
        return trim($str,  " \t\n\r\0\x0B[]`");
    }

    /**
     * 获取指定变量类型
     * @param $var
     * @return string
     */
    static public function getType($var)
    {
        if (is_array($var)) return "array";
        if (is_bool($var)) return "boolean";
        if (is_float($var)) return "float";
        if (is_int($var)) return "int";
        if (is_null($var)) return "null";
        if (is_object($var)) return "object";
        if (is_resource($var)) return "resource";
        if (is_string($var)) return "string";
        return "unknown type";
    }

    /**
     * 类型转换
     * @param $mixed
     * @param $type
     */
    static public function setType(& $mixed, $type) {
        settype($mixed, $type);
    }

    /**
     * 以关键字数组排序数组
     * @param $targetArr
     * @param $keys
     * @return array
     */
    static public function arrayOrderByKeys($targetArr, $keys) {
        $tmpArr = array();
        if(empty($keys) || !is_array($keys) || empty($targetArr) || !is_array($targetArr)) {
            return $tmpArr;
        }
        foreach($keys as $key) {
            if(!isset($targetArr[$key])) {
                continue;
            }
            $tmpArr[$key] = $targetArr[$key];
        }
        return $tmpArr;
    }

    /**
     * 取指定列
     * @param $arrayInput
     * @param $key
     * @return array
     */
    static public function arrayColumn($arrayInput, $key) {
        $arr = array();
        if(!isset($arrayInput) || empty($arrayInput)) {
            return $arr;
        }
        foreach($arrayInput as $row) {
            if(isset($row[$key]) && !empty($row[$key])) {
                $arr[] = $row[$key];
            }
        }
        return $arr;
    }

    /**
     * 指定键为索引键
     * @param $arrayInput
     * @param $key
     * @return array
     */
    static public function arrayAssocKeys($arrayInput, $key) {
        $arr = array();
        if(empty($arrayInput) || !is_array($arrayInput)) {
            return $arr;
        }
        foreach($arrayInput as $row) {
            $arr[$key] = $row;
        }
        return $arr;
    }

    /**
     * 数组分组
     * @param $arrayInput
     * @param $key
     * @return array
     */
    static public function arrayGroup($arrayInput, $key) {
        $arr = array();
        if(empty($arrayInput) || empty($key)) {
            return $arr;
        }
        foreach($arrayInput as $row) {
            $arr[$row[$key]][] = $row;
        }
        return $arr;
    }

    /**
     * 按键值顺序merge数组
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    static public function arrayKeyMerge($arr1 = array(), $arr2 = array()) {
        if(empty($arr2)) {
            return $arr1;
        }
        foreach($arr2 as $key => $val) {
            $arr1[$key] = $val;
        }
        return $arr1;
    }

    /**
     * 统计键值都相同的元素个数
     * @param array $arr1
     * @param array $arr2
     * @return int
     */
    static public function countArrayKeyVal($arr1 = array(), $arr2 = array()) {
        $counter = 0;
        if(empty($arr2) || empty($arr1) || !is_array($arr1) || !is_array($arr2)) {
            return $counter;
        }
        foreach($arr1 as $key => $val) {
            if(!isset($arr2[$key])) {
                continue;
            }
            if($arr2[$key] == $val) {
                $counter++;
            }
        }
        return $counter;
    }

    /**
     * 制定键做索引
     * @param array $arr
     * @param $key
     * @return array
     */
    static public function arrayIndex($arr = array(), $key) {
        $ret = array();
        if(empty($arr) || empty($key) || !is_array($arr)) {
            return $ret;
        }
        foreach($arr as $val) {
            $ret[$val[$key]] = $val;
        }
        return $ret;
    }

}