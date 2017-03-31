<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/17
 * Time: 17:58
 */
require_once ( '../Library/Init.php');

class GenerateScores {

    private $_evalObj;
    function __construct() {
        $this->_evalObj = new Service_Evaluation();
    }

    /**
     * 获取所有答案
     * @param $testTime
     * @return array
     */
    public function getAllAnswers($testTime = 0) {
        $answersObj = new Dao_Answers();
        $page = 1;
        $pageSize = 500;
        $groupKey = 'test_times';
        do {
            $tmpArr = $answersObj->getAllAnswers($page, $pageSize, $testTime);
            if(empty($tmpArr)) {
                break;
            }
            $tmpArr = Library_Util::arrayGroup($tmpArr, $groupKey);
            if(!isset($answers)) {
                $answers = $tmpArr;
            } else {
                foreach($tmpArr as $key => $val) {
                    $answers[$key] = array_merge($answers[$key], $val);
                }
            }
            $page++;
        } while(true);
        $answers = $this->formatAnswers($answers);
        return $answers;
    }

    /**
     * 批量计算评分
     * @param $answers
     * @return array
     */
    private function getScoresMultiByAnswers($answers) {
        $ret = array();
        if(empty($answers)) {
            return $ret;
        }
        $groupAnswers = array_chunk($answers, 100, true);
        unset($answers);
        foreach($groupAnswers as $groupOne) {
            foreach($groupOne as $sno => $answer) {
                $ret[$sno] = $this->calculateScoreByAnswer($answer);
            }
        }
        return $ret;
    }

    /**
     * 获取评分
     * @param $answer
     * @return mixed
     */
    private function calculateScoreByAnswer($answer) {
        $ret = $this->_evalObj->calculateScoreByAnswer($answer);
        return $ret;
    }

    /**
     * 格式化答案
     * @param $answers
     * @return array
     */
    private function formatAnswers($answers) {
        $ret = array();
        if(empty($answers)) {
            return $ret;
        }
        $groupKeySno = 'sno';
        $columnKeyAnswer = 'answer';
        foreach($answers as $testTime => $answer) {
            $ret[$testTime] = Library_Util::arrayGroup($answer, $groupKeySno);
        }

        unset($testTime, $answer);
        foreach($ret as $testTime => $answer) {
            foreach($answer as $sno => $one) {
                $one = Library_Util::arrayColumn($one, $columnKeyAnswer);
                $ret[$testTime][$sno] = array();
                foreach($one as $answerStr) {
                    $answerJson = json_decode($answerStr, true);
                    $ret[$testTime][$sno] = Library_Util::arrayKeyMerge($ret[$testTime][$sno], $answerJson);
                }
                // 过滤掉答案不完整的
                if(count($ret[$testTime][$sno]) < 149) {
                    unset($ret[$testTime][$sno]);
                }
            }
        }
        return $ret;
    }

    /**
     * 获取所有学生信息
     * @return array|null
     */
    private function getStuInfos() {
        $stuObj = new Dao_Student();
        $students = $stuObj->getAllStudentInfo();
        $groupKey = 'account';
        $students = Library_Util::arrayIndex($students, $groupKey);
        return $students;
    }

    /**
     * 获取学校信息
     * @return array|null
     */
    private function getSchoolInfos() {
        $departObj = new Dao_School();
        $ids = null;
        $schools = $departObj->getSchoolInfoByIds($ids);
        $groupKey = 'id';
        $schools = Library_Util::arrayIndex($schools, $groupKey);
        return $schools;
    }
    /**
     * 批量入库
     * @param $rows
     * @return bool|mysqli_result|null
     */
    private function putScoresIntoDbMulti($rows) {
         if(empty($rows) || !is_array($rows)) {
            return false;
         }
        $evalObj = new Dao_Evaluation();
        $fields = array(
            'score',
            'gender',
            'type',
            'test_times',
            'edu_depart_id',
            'school_id',
            'grade',
            'class',
            'sno',
            'create_time',
        );
        $rowsGroup = array_chunk($rows, 100);
        foreach($rowsGroup as $groupItem) {
            $res = $evalObj->insertIntoEvaluationByMulti($fields, $groupItem);
        }
        return $res;
    }

    public function run() {
        $students = $this->getStuInfos();
        $schools = $this->getSchoolInfos();
        $rows = array();
        $answers = $this->getAllAnswers();
        $createTime = date('Y-m-d H:i:s', time());
        foreach($answers as $testTimes => $answer) {
            $scores = $this->getScoresMultiByAnswers($answer);
            foreach($scores as $sno => $one) {
                $rows[] = array(
                    'score' => json_encode($one),
                    'gender' => $students[$sno]['gender'],
                    'type' => 'student',
                    'test_times' => $testTimes,
                    'edu_depart_id' => $schools[$students[$sno]['school_id']]['edu_depart_id'],
                    'school_id' => $students[$sno]['school_id'],
                    'grade' => $students[$sno]['grade'],
                    'class' => $students[$sno]['class'],
                    'sno' => $sno,
                    'create_time' => $createTime,
                );
            }
        }
        $ret = $this->putScoresIntoDbMulti($rows);
        echo $ret;
    }
}

$start = time();
$obj = new GenerateScores();
$obj->run();
$end = time();
$time = $end - $start;
Library_Log::trace(__FILE__ . ' consumed ' . $time . ' s');
