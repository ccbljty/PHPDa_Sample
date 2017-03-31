<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/4
 * Time: 20:30
 */
class Service_Evaluation extends Library_Interface_Service{

    private $_indexScoreIds;
    private $_indexScore;
    private $_negativeQuestions;
    function __construct() {
        $this->_indexScoreIds = array(
            'score_1_index_id' => array(
                '11', '12' , '13',
                '19', '20' , '21',
            ),
            'score_0-8_index_id' => array(
                '14', '15' , '16', '17',
            ),
            'score_m-4_index_id' => array(
                '8', '9' , '10',
            ),
            'score_2p-5_index_id' => array(
                '18',
            ),
        );

        $this->_indexScore = array(
            'positive' => array(
                'A' => 1,
                'B' => 2,
                'C' => 3,
                'D' => 4,
                'E' => 5,
            ),
            'negative' => array(
                'A' => 5,
                'B' => 4,
                'C' => 3,
                'D' => 2,
                'E' => 1,
            ),
        );

        $this->_negativeQuestions = array(
            54,59,61,64,
        );

    }

    /**
     * 获取问卷2标准答案（85道题）
     * @return array
     */
    private function getStandardAnswer() {
        $ret = Library_Conf::getConf('answer');
        return $ret;
    }

    /**
     * 获取指标及题目
     * @return array|null
     */
    private function getIndexAll() {
        $ListObj = new Service_List();
        $showQuestions = 1;
        $res = $ListObj->getIndexes($showQuestions);
        return $res;
    }

    /**
     * 计算评分，评分算法(新格式)
     * @param $arr
     * @return array
     */
    public function calculateScoreByAnswer($arr) {
        ksort($arr);
        $standAnswer = $this->getStandardAnswer();
        $standAnswer = $standAnswer['questionnaire_2'];
        $indexQuestions = $this->getIndexAll();
        $scores = array();
        $scores['total'] = 0;
        foreach ($indexQuestions as $one) {
            $indexIdOne = 'index_id_' . $one['id'];
            $scores[$indexIdOne] = 0;
            foreach($one['index2s'] as $item) {
                $tmpScore = 0;
                $answer = Library_Util::arrayOrderByKeys($arr, $item['question_ids']);
                $counter = Library_Util::countArrayKeyVal($answer, $standAnswer);
                $indexId = $item['id'];
                // 每题1分
                if(in_array($indexId, $this->_indexScoreIds['score_1_index_id'])) {
                    $tmpScore = $counter;
                    // 每题0.8分
                }elseif(in_array($indexId, $this->_indexScoreIds['score_0-8_index_id'])) {
                    $tmpScore = 0.8 * $counter;
                    // 加权得分
                }elseif(in_array($indexId, $this->_indexScoreIds['score_m-4_index_id']) || in_array($indexId, $this->_indexScoreIds['score_2p-5_index_id'])) {
                    $interArr = Library_Util::arrayOrderByKeys($answer, $this->_negativeQuestions);
                    $diffArr = array_diff_key($answer, $interArr);

                    // 负面题目
                    if(!is_array($interArr) || empty($interArr)) {
                        $tmpScore += 0;
                    } else {
                        $valCountInter = array_count_values($interArr);
                        foreach($valCountInter as $valKey => $tmpCount) {
                            $valKey = strtoupper($valKey);
                            if(!isset($this->_indexScore['negative'][$valKey])) {
                                $tmpVar = 0;
                            } else {
                                $tmpVar = $this->_indexScore['negative'][$valKey] * $tmpCount;
                            }
                            $tmpScore += $tmpVar;
                        }
                    }
                    if(isset($valKey)) {
                        unset($valKey);
                    }
                    if(isset($tmpCount)) {
                        unset($tmpCount);
                    }
                    // 正面题目
                    if(!is_array($diffArr) || empty($diffArr)) {
                        $tmpScore += 0;
                    } else {
                        $valCountDiff = array_count_values($diffArr);
                        foreach($valCountDiff as $valKey => $tmpCount) {
                            $valKey = strtoupper($valKey);
                            // 过滤错误
                            if(!isset($this->_indexScore['positive'][$valKey])) {
                                $tmpVar = 0;
                            } else {
                                $tmpVar = $this->_indexScore['positive'][$valKey] * $tmpCount;
                            }
                            $tmpScore += $tmpVar;
                        }
                    }
                    $tmpScore_m_4 = round($tmpScore/4,2);
                    $tmpScore_2p_5 = round($tmpScore*2/5,2);
                    $tmpScore = in_array($indexId, $this->_indexScoreIds['score_m-4_index_id']) ? $tmpScore_m_4 : $tmpScore_2p_5;
                }
                $scores[$indexIdOne] += $tmpScore;
                $scores['total'] += $tmpScore;
                $indexIdTwo = 'index_id_' . $item['id'];
                $scores[$indexIdTwo] = $tmpScore;
            }
        }
        return $scores;
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
        $answers = array_slice($answers['1'],0,50);
        $scores = $this->getScoresMultiByAnswers($answers);
        return $scores;
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
     * 获取个人评价
     * @param $stuId
     * @param $testTimes
     * @return array|bool
     */
    public function getIndividualEvaluation($stuId, $testTimes) {
        if(empty($stuId)) {
            return false;
        }
        $daoObj = new Dao_Answers();
        $res = $daoObj->getIndividualAnswer($stuId, $testTimes);
        if(empty($res)) {
            return array();
        }
        $prefix = 'test_times_';
        $answer = array();
        foreach($res as $row) {
            $key = $prefix . $row['test_times'];
            $row['answer'] = strtoupper($row['answer']);
            $row['answer'] = json_decode($row['answer'], true);
            if(!isset($answer[$key])) {
                $answer[$key] = array();
            }
            // 合并两份问卷答案
            $answer[$key] = Library_Util::arrayKeyMerge($answer[$key], $row['answer']);
        }
        $ret = array();
        $indexes = $this->getIndexAll();
        // 获取每次测试成绩
        foreach($answer as $key => $one) {
            $ret[$key] = $indexes;
            $score = $this->calculateScoreByAnswer($one);
            $score = $this->formatByIndividual($score);
            foreach($ret[$key] as & $index) {
                $index['score'] = $score[$index['id']];
                unset($index['index2s'], $index['weight'], $index['id']);
            }
            array_unshift($ret[$key], array(
                'content' => '信息素养评估得分',
                'score' => $score['total'],
            ));
        }
        return $ret;
    }

    /**
     * 格式化成绩
     * @param $score
     * @return array
     */
    private function formatByIndividual($score) {
        $ret = array();
        if(empty($score) || !is_array($score)) {
            return $ret;
        }
        foreach($score as $key => $one) {
            if($key != 'total') {
                $key = substr($key,9);
            }
            $ret[$key] = $one;
        }
        return $ret;
    }

    /**
     * 获取学生评价成绩
     * @param $eduDepartId
     * @param $schoolId
     * @param $testTimes
     * @return array|bool|nullE
     */
    private function getEvaluationByEduDepartIdSchoolId($eduDepartId, $schoolId, $testTimes) {
        $evaluationObj = new Dao_Evaluation();
        $rows = $evaluationObj->getEvaluationByEduDepartIdSchoolId($eduDepartId, $schoolId, $testTimes);
        return $rows;
    }

    /**
     * 筛选部分学校数据
     * @param $rows
     * @param $schoolIds
     * @param $key
     * @return array
     */
    private function getPartSchoolRows($rows, $schoolIds, $key) {
        $arr = array();
        $rows = Library_Util::arrayGroup($rows, $key);
        $schoolArr = explode(',',  $schoolIds);
        foreach($rows as $key => $one) {
            if(in_array($key, $schoolArr)) {
                $arr = array_merge($arr, $one);
            }
        }
        return $arr;
    }

    /**
     * 筛选部分区县教育管理部门数据
     * @param $rows
     * @param $eduDepartId
     * @param $key
     * @return array
     */
    private function getPartDepartRows($rows, $eduDepartId, $key) {
        $arr = array();
        $rows = Library_Util::arrayGroup($rows, $key);
        $departArr = explode(',',  $eduDepartId);
        unset($key);
        foreach($rows as $key => $one) {
            if(in_array($key, $departArr)) {
                $arr = array_merge($arr, $one);
            }
        }
        return $arr;
    }

    /**
     * 根据筛选条件分组
     * @param $rows
     * @param $groupCondition
     * @return array
     */
    private function dealEvaluationByGroup($rows, $groupCondition) {
        $ret = array();
        if(empty($groupCondition) || empty($rows)) {
            return $ret;
        }
        if(isset($groupCondition['edu_depart_id']) && $groupCondition['edu_depart_id'] == 0) {
            $groupCondition['edu_depart_id'] = intval($groupCondition['edu_depart_id']);
        }
        if(isset($groupCondition['school_id']) && $groupCondition['school_id'] == 0 ) {
            $groupCondition['school_id'] = intval($groupCondition['school_id']);
        }
        // 按所有教育管理部门分类
        if(isset($groupCondition['edu_depart_id']) && $groupCondition['edu_depart_id'] === 0 && !isset($groupCondition['school_id'])) {
            $rows = Library_Util::arrayGroup($rows, 'edu_depart_id');
        }
        // 选择部分区县教育管理部门
        elseif(isset($groupCondition['edu_depart_id']) && $groupCondition['edu_depart_id'] !== 0 && !isset($groupCondition['school_id'])) {
            $rows = Library_Util::arrayGroup($rows, 'edu_depart_id');
            $schoolArr = explode(',',  $groupCondition['edu_depart_id']);
            foreach($rows as $key => $one) {
                if(!in_array($key, $schoolArr)) {
                    unset($rows[$key]);
                }
            }
        }
        // 县区选择
        elseif(isset($groupCondition['edu_depart_id']) && $groupCondition['edu_depart_id'] !== 0){
            $rows = $this->getPartDepartRows($rows, $groupCondition['edu_depart_id'], 'edu_depart_id');
        }

        // 把id换为教育管理部门名
        if(isset($groupCondition['edu_depart_id']) && !isset($groupCondition['school_id'])) {
            $departInfo = $this->getCountyInfoByParentId($groupCondition['edu_primary_depart_id']);
            foreach($rows as $departId => $row) {
                $rows[$departInfo[$departId]['name']] = $row;
                unset($rows[$departId]);
            }
        }

        // 按所有学校分类
        if(isset($groupCondition['school_id']) && $groupCondition['school_id'] === 0 && !isset($groupCondition['grade'])) {
            $rows = Library_Util::arrayGroup($rows, 'school_id');
        }
        // 选择部分学校
        elseif(isset($groupCondition['school_id']) && $groupCondition['school_id'] !== 0 && !isset($groupCondition['grade'])) {
            $rows = Library_Util::arrayGroup($rows, 'school_id');
            $schoolArr = explode(',',  $groupCondition['school_id']);
            foreach($rows as $key => $one) {
                if(!in_array($key, $schoolArr)) {
                    unset($rows[$key]);
                }
            }
        }
        // 学校选择
        elseif(isset($groupCondition['school_id']) && $groupCondition['school_id'] !== 0) {
            $rows = $this->getPartSchoolRows($rows, $groupCondition['school_id'], 'school_id');
        }

        // 把id换为学校名
        if(isset($groupCondition['school_id']) && !isset($groupCondition['grade'])) {
            $schoolInfo = $this->getAllSchoolInfo();
            foreach($rows as $schoolId => $row) {
                $rows[ $schoolInfo[$schoolId]['name']] = $row;
                unset($rows[$schoolId]);
            }
        }

        // 按所有年级分类
        if(isset($groupCondition['grade']) && $groupCondition['grade'] == 'all') {
            $rows = Library_Util::arrayGroup($rows, 'grade');
        }
        //  按所选年级分类
        elseif (isset($groupCondition['grade']) && $groupCondition['grade'] != 'all'){
            $rows = Library_Util::arrayGroup($rows, 'grade');
            $gradeArr = explode(',',  $groupCondition['grade']);
            foreach($rows as $key => $one) {
                if(!in_array($key, $gradeArr)) {
                    unset($rows[$key]);
                }
            }
        }

        // 显示班级
        if(isset($groupCondition['class'])){
            $arr = array();
            foreach($rows as $one) {
                $arr = array_merge($arr, $one);
            }
            $rows = Library_Util::arrayGroup($arr, 'class');
            if($groupCondition['class'] != 'all') {
                $classArr = explode(',',  $groupCondition['class']);
                foreach($rows as $key => $one) {
                    if(!in_array($key, $classArr)) {
                        unset($rows[$key]);
                    }
                }
            }
        }

        // 按性别来取数据, 数个格式待定
        if(isset($groupCondition['order_by_gender']) && $groupCondition['order_by_gender'] == 'yes') {
            $source = $rows;
            $rows = array();
            foreach($source as $key => $one) {
                $rows[$key] = Library_Util::arrayGroup($one, 'gender');
            }
        }
        return $rows;
    }

    /**
     * 评分计算核心算法
     * @param $rows
     * @param $type
     * @param int $primaryIndexId
     * @param string $orderByGender
     * @return array
     */
    private function calculateGroupScore($rows, $type, $primaryIndexId = 0 , $orderByGender = 'no') {
        $data = array();
        $indexes = $this->getIndexes();
        $indexes = Library_Util::arrayIndex($indexes, 'id');
        if(empty($indexes)) {
            return $data;
        }
        $data['keys'] = array_keys($rows);
        if($orderByGender == 'yes') {
            foreach($rows as $rowArr) {
                foreach($rowArr as $gender => $arrGender) {
                    $scores = Library_Util::arrayColumn($arrGender, 'score');
                    foreach($scores as & $score) {
                        $score = json_decode($score, true);
                    }
                    $counter = count($scores);
                    $gender .= '生';
                    if($type == 'overview') {
                        $totalScores = Library_Util::arrayColumn($scores, 'total');
                        $totalAverage = round(array_sum($totalScores) / $counter, 2);
                        $data['data']['total']['name'] = '信息素养评估得分';
                        $data['data']['total']['scores'][$gender][] = $totalAverage;
                    }elseif($type == 'part' && !empty($primaryIndexId)) {
                        $secondaryIndexes = $indexes[$primaryIndexId]['index2s'];
                    }
                    $tmpIndexes = $type == 'overview' ? $indexes : $secondaryIndexes;
                    foreach($tmpIndexes as $content) {
                        $indexId = $content['id'];
                        $indexScores = Library_Util::arrayColumn($scores, 'index_id_' . $indexId);
                        $average = round(array_sum($indexScores) / $counter, 2);
                        $data['data'][$indexId]['name'] = $content['content'];
                        $data['data'][$indexId]['scores'][$gender][] = $average;
                    }
                }
            }
        } else {
            foreach($rows as $key => $rowArr) {
                $scores = Library_Util::arrayColumn($rowArr, 'score');
                $counter = count($scores);
                foreach($scores as & $score) {
                    $score = json_decode($score, true);
                }
                if($type == 'overview') {
                    $totalScores = Library_Util::arrayColumn($scores, 'total');
                    $totalAverage = round(array_sum($totalScores) / $counter, 2);
                    $data['data']['total']['name'] = '信息素养评估得分';
                    $data['data']['total']['scores'][] = $totalAverage;
                }elseif($type == 'part' && !empty($primaryIndexId)) {
                    $secondaryIndexes = $indexes[$primaryIndexId]['index2s'];
                }
                $tmpIndexes = $type == 'overview' ? $indexes : $secondaryIndexes;
                foreach($tmpIndexes as $content) {
                    $indexId = $content['id'];
                    $indexScores = Library_Util::arrayColumn($scores, 'index_id_' . $indexId);
                    $average = round(array_sum($indexScores) / $counter, 2);
                    $data['data'][$indexId]['name'] = $content['content'];
                    $data['data'][$indexId]['scores'][] = $average;
                }
            }
        }
        $data['data'] = array_values($data['data']);
        unset($score);
        return $data;
    }

    /**
     * 获取学校info
     * @return array|null
     */
    private function getAllSchoolInfo() {
        $schoolDao = new Dao_School();
        $schoolInfo = $schoolDao->getAllSchoolInfo();
        $schoolInfo = Library_Util::arrayIndex($schoolInfo, 'id');
        return $schoolInfo;
    }
    /**
     * 获取指标
     * @return array|null
     */
    private function getIndexes() {
        $indexObj = new Dao_Index();
        $res = $indexObj->getAllIndexes();
        return $res;
    }
    /**
     *  获得学校测评成绩
     * @param $params
     * @return array
     */
    public function getSchoolEvaluation($params) {
        $individuals = $this->getEvaluationByEduDepartIdSchoolId($params['edu_depart_id'], $params['school_id'], $params['test_times'] );
        $groupCondition = array(
            'evaluation_type' => 'school',
            'grade' => $params['grade'],
            'type' => $params['type'],
            'order_by_gender' => $params['order_by_gender'],
        );
        if(isset($params['class'])) {
            $groupCondition['class'] = $params['class'];
        }
        if(isset($params['primary_index_id'])) {
            $groupCondition['primary_index_id'] = $params['primary_index_id'];
        }
        $rows = $this->dealEvaluationByGroup($individuals, $groupCondition);
        $rows = $this->calculateGroupScore($rows,$groupCondition['type'], $groupCondition['primary_index_id'], $groupCondition['order_by_gender']);
        return $rows;
    }

    /**
     *  获得县区测评成绩
     * @param $params
     * @return array
     */
    public function getEduDepartEvaluation($params) {
        $individuals = $this->getEvaluationByEduDepartIdSchoolId($params['edu_depart_id'], null, $params['test_times'] );
        $groupCondition = array(
            'evaluation_type' => 'county',
            'school_id' => $params['school_id'],
            'type' => $params['type'],
            'order_by_gender' => $params['order_by_gender'],
        );
        if(isset($params['class'])) {
            $groupCondition['class'] = $params['class'];
        }
        if(isset($params['grade'])) {
            $groupCondition['grade'] = $params['grade'];
        }
        if(isset($params['primary_index_id'])) {
            $groupCondition['primary_index_id'] = $params['primary_index_id'];
        }
        $rows = $this->dealEvaluationByGroup($individuals, $groupCondition);
        $rows = $this->calculateGroupScore($rows,$groupCondition['type'], $groupCondition['primary_index_id'], $groupCondition['order_by_gender']);
        return $rows;
    }

    /**
     * 根据父教育管理部门Id获取县区教育管理部门
     * @param $parentId
     * @return array|null
     */
    private function getCountyInfoByParentId($parentId) {
        $daoDepart = new Dao_Department();
        $departs = $daoDepart->getCountyInfoByParentId($parentId);
        $departs = Library_Util::arrayIndex($departs, 'id');
        return $departs;
    }

    /**
     *  获得区域测评成绩
     * @param $params
     * @return array
     */
    public function getEduPrimaryDepartEvaluation($params) {
        $departInfo = $this->getCountyInfoByParentId($params['edu_primary_depart_id']);
        $individuals = array();
        foreach($departInfo as $departId => $one) {
            $arrRes = $this->getEvaluationByEduDepartIdSchoolId($departId, null, $params['test_times'] );
            $individuals = array_merge($individuals, $arrRes);
        }
        $groupCondition = array(
            'evaluation_type' => 'area',
            'edu_primary_depart_id' => $params['edu_primary_depart_id'],
            'edu_depart_id' => $params['edu_depart_id'],
            'type' => $params['type'],
            'order_by_gender' => $params['order_by_gender'],
        );
        if(isset($params['class'])) {
            $groupCondition['class'] = $params['class'];
        }
        if(isset($params['grade'])) {
            $groupCondition['grade'] = $params['grade'];
        }
        if(isset($params['school_id'])) {
            $groupCondition['school_id'] = $params['school_id'];
        }
        if(isset($params['primary_index_id'])) {
            $groupCondition['primary_index_id'] = $params['primary_index_id'];
        }

        $rows = $this->dealEvaluationByGroup($individuals, $groupCondition);
        $rows = $this->calculateGroupScore($rows,$groupCondition['type'], $groupCondition['primary_index_id'], $groupCondition['order_by_gender']);
        return $rows;
    }

    public function execute($params)
    {
        // TODO: Implement execute() method.
        $type = $params['operation'];
        unset($params['operation']);
        if(!isset($params['test_times'])) {
            $params['test_times'] = null;
        }
        // 添加缓存
        $key = '';
        foreach($params as $param) {
            $key .= $param;
        }
        $key = $type . '_' . md5($key);
        $redis = new Library_Redis();
        $ret = $redis->get($key);
        $ret = json_decode(gzuncompress($ret), true);
        if(empty($ret)) {
            switch($type) {
                case 'individual' :
                    $ret = $this->getIndividualEvaluation($params['stu_id'], $params['test_times']);
                    break;
                case 'school' :
                    $ret = $this->getSchoolEvaluation($params);
                    break;
                case 'edu_depart' :
                    $ret = $this->getEduDepartEvaluation($params);
                    break;
                case 'edu_primary_depart' :
                    $ret = $this->getEduPrimaryDepartEvaluation($params);
                    break;
                default :
                    $ret = false;
            }
            $redis->setex($key, REDIS_TTL, gzcompress(json_encode($ret)));
        }
        return $ret;
    }
}