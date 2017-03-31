<?php
/**
 * Created by PhpStorm.
 * desc: 数据库tool
 * User: bobo
 * Date: 17-1-15
 * Time: 下午5:38
 */
class Library_Db {
    // sql常量
    const FIELDS = 0;
    const TABLES = 1;
    const CONDITIONS = 2;
    const APPEND = 3;
    const SET_FIELDS = 4;
    const PRE_OPERATION = 5;
    // 数据库链接句柄
    private $_db;
    // sql语句
    private $_sql;
    //　获取操作影响数
    public $affectedNum;
    // 获取insert_id
    public $insertId;
    function __construct($defaultDb = 'db') {
        $this->_db = $this->getConnection($defaultDb);
        $this->_db->set_charset('utf8');
        $this->affectedNum = 0;
        $this->insertId = 0;
    }

    /**
     * 校验连接字段
     * @return array
     */
    private function getCheckedKeys() {
        return array(
            'host',
            'port',
            'user',
            'passwd',
            'default_db',
            'charset',
        );
    }

    /**
     *　连接数据库
     * @return bool|mysqli
     */
    private function getConnection($defaultDb) {
        $falseMsg = false;
        $conf = Library_Conf::getConf($defaultDb);
        $randKey = array_rand($conf);
        $logPrefix = "db_conf_key $randKey " ;
        $conf = $conf[$randKey];
        $checkedParams = $this->getCheckedKeys();
        $intersectRet = array_intersect(array_keys($conf), $checkedParams);
        if(count($intersectRet) != count($checkedParams)) {
            Library_Log::warning($logPrefix . 'connect params are not apt for command');
            return $falseMsg;
        }
        $mysqli = mysqli_init();
        if (!$mysqli) {
            Library_Log::warning($logPrefix . 'mysqli_init failed');
            return $falseMsg;
        }

        // 设置MySQL链接选项
        /*
        if (!$mysqli->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
            Library_Log::warning('Setting MYSQLI_INIT_COMMAND failed');
        }

        if (!$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
            Library_Log::warning('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
        }
        */
        if (!$mysqli->real_connect($conf['host'], $conf['user'], $conf['passwd'], $conf['default_db'], $conf['port'])) {
            Library_Log::warning($logPrefix . 'Connect Error (' . mysqli_connect_errno() . ') '
                . mysqli_connect_error());
            return $falseMsg;
        }
        $mysqli->set_charset($conf['charset']);
        Library_Log::trace($logPrefix . 'Connect success !');
        return $mysqli;
    }

    /**
     * 过滤特殊字符并格式化
     * @param $str
     * @return null|string
     */
    private function filterFormat($str) {
        $type = Library_Util::getType($str);
        $str = Library_Util::trimTool(strtolower($str));
        $pattern = '/(select)|(update)|(delete)|(drop)|(grant)/';
        if(preg_match($pattern, $str)) {
            Library_Log::warning('sql is illegal');
            return null;
        }
        Library_Util::setType($str, $type);
        return $str;
    }

    /**
     * 字符串化
     * @param $arrList
     * @param int $type
     * @param string $delimiter
     * @return null|string
     */
    private function makeString($arrList, $type = self::FIELDS, $delimiter = ', ') {

        if((!is_array($arrList) || empty($arrList)) && $type !== self::TABLES) {
            return null;
        }

        if(!empty($arrList) && is_string($arrList) && $type === self::TABLES) {
            $table = $this->filterFormat($arrList);
            $table = '`' . $table . '`';
            return $table;
        }

        if($type == self::CONDITIONS) {
            foreach ($arrList as $key => $field) {
                $field = $this->filterFormat($field);
                $type = Library_Util::getType($field);
                $field = $this->_db->real_escape_string($field);
                Library_Util::setType($field, $type);
                if ($field === null) {
                    return null;
                }
                if(is_string($field) && strpos(strtolower($key), ' in') === false) {
                    $arrList[$key] = $key . ' \'' . $field . '\' ';
                } else {
                    $arrList[$key] = $key  . ' ' . $field . ' ';
                }
            }
        }elseif ($type == self::APPEND) {
            foreach ($arrList as $key => $field) {
                $arrList[$key] = $this->filterFormat($field);
            }
        }elseif ($type == self::SET_FIELDS) {
            foreach ($arrList as $key => $field) {
                $field = $this->filterFormat($field);
                $field = $this->_db->real_escape_string($field);
                $arrList[$key] = ' `' . trim($key) . '` = \'' . $field . '\'';
            }
        } else {
            foreach ($arrList as $key => $field) {
                $field = $this->filterFormat($field);
                if ($field === null) {
                    return null;
                }
                if($type == self::PRE_OPERATION) {
                    $arrList[$key] = $field;
                }else {
                    $arrList[$key] = '`' . $field . '`';
                }
            }
        }
        return implode($delimiter, $arrList);
    }

    /**
     * select语句
     * @param $fields
     * @param $tables
     * @param null $conditions
     * @param null $appends
     * @param null $preOperation
     * @return array|null
     */
    public function select($fields, $tables, $conditions = null, $appends = null, $preOperation = null) {
        if(empty($fields) || empty($tables)) {
            Library_Log::warning(__FILE__ . __LINE__ . ' fields or tables are empty');
            return null;
        }
        $this->_sql = 'SELECT ';
        $strPreOperation = $this->makeString($preOperation, self::PRE_OPERATION, ' ');
        if(!empty($strPreOperation) && is_string($strPreOperation)) {
            $this->_sql .= $strPreOperation . ' ';
        }
        $strFields = $this->makeString($fields, self::FIELDS, ', ');
        if(!empty($strFields) && is_string($strFields)) {
            $this->_sql .= $strFields;
        }
        $strTables = $this->makeString($tables, self::TABLES, ', ');
        if(!empty($strTables) && is_string($strTables)) {
            $this->_sql .= ' FROM ' . $strTables;
        }

        $strConditions = $this->makeString($conditions, self::CONDITIONS, ' AND ');
        if(!empty($strConditions) && is_string($strConditions)) {
            $this->_sql .=  ' WHERE '. $strConditions;
        }

        $strAppends = $this->makeString($appends, self::APPEND, ' ');
        if(!empty($strAppends) && is_string($strAppends)) {
            $this->_sql .=  ' '. $strAppends;
        }
        Library_Log::trace($this->_sql);
        $mysqlResult = $this->_db->query($this->_sql);
        if (!$mysqlResult) {
            Library_Log::warning('mysql errno: ' . $this->_db->errno . ', mysql error :' . $this->_db->error);
        }
        $arr = array();
        while($row = $mysqlResult->fetch_assoc()) {
            $arr[] = $row;
        }
        $mysqlResult->close();
        return $arr;
    }

    /**
     * 更新语句
     * @param $fields
     * @param $table
     * @param null $conditions
     * @return bool|mysqli_result|null
     */
    public function update($fields, $table, $conditions = null) {
        if(empty($fields) || empty($table)) {
            Library_Log::warning(__FILE__ . __LINE__ . ' fields or tables are empty');
            return null;
        }
        $this->_sql = 'UPDATE ';
        $strTable = $this->makeString($table, self::TABLES, ', ');
        if(!empty($strTable) && is_string($strTable)) {
            $this->_sql .=  $strTable;
        }
        $strFields = $this->makeString($fields, self::SET_FIELDS, ', ');
        if(!empty($strFields) && is_string($strFields)) {
            $this->_sql .= ' SET ' . $strFields;
        }

        $strConditions = $this->makeString($conditions, self::CONDITIONS, ' and ');
        if(!empty($strConditions) && is_string($strConditions)) {
            $this->_sql .=  ' WHERE '. $strConditions;
        }
        Library_Log::trace($this->_sql);
        $mysqlResult = $this->_db->query($this->_sql);
        if($mysqlResult) {
            $this->affectedNum = $this->_db->affected_rows;
        } else{
            Library_Log::warning('mysql errno: ' . $this->_db->errno . ', mysql error :' . $this->_db->error);
        }
        return $mysqlResult;
    }

    /**
     * delete语句
     * @param $table
     * @param null $conditions
     * @return bool|mysqli_result|null
     */
    public function delete($table, $conditions = null) {
        if(empty($table)) {
            Library_Log::warning(__FILE__ . __LINE__ . ' fields or tables are empty');
            return null;
        }
        $this->_sql = 'DELETE FROM ';
        $strTables = $this->makeString($table, self::TABLES, ', ');
        if(!empty($strTables) && is_string($strTables)) {
            $this->_sql .= $strTables;
        }
        $strConditions = $this->makeString($conditions, self::CONDITIONS, ' and ');
        if(!empty($strConditions) && is_string($strConditions)) {
            $this->_sql .=  ' WHERE '. $strConditions;
        }
        Library_Log::trace($this->_sql);
        $mysqlResult = $this->_db->query($this->_sql);
        if($mysqlResult) {
            $this->affectedNum = $this->_db->affected_rows;
        } else{
            Library_Log::warning('mysql errno: ' . $this->_db->errno . ', mysql error :' . $this->_db->error);
        }
        return $mysqlResult;
    }

    /**
     *　执行自定义查询语句
     * @param $sql
     * @return array|bool|mysqli_result
     */
    public function query($sql) {
        if(empty($sql) || !is_string($sql)) {
            Library_Log::warning(__FILE__ . __LINE__ . ' sql is empty');
            return false;
        }
        $this->_sql = $sql;
        Library_Log::trace($this->_sql);
        $mysqlResult = $this->_db->query($this->_sql);
        if(!$mysqlResult) {
            Library_Log::warning('mysql errno: ' . $this->_db->errno . ', mysql error :' . $this->_db->error);
            return false;
        }
        if(stripos($this->_sql, 'delete') !== false || stripos($this->_sql, 'update') !== false) {
            $this->affectedNum = $this->_db->affected_rows;
        } else if(stripos($this->_sql, 'insert') !== false) {
            $this->insertId = $this->_db->insert_id;
        } else if(stripos($this->_sql, 'select') !== false) {
            $arr = array();
            while($row = $mysqlResult->fetch_assoc()) {
                $arr[] = $row;
            }
            $mysqlResult->close();
            return $arr;
        }
        return $mysqlResult;
    }

    /**
     * insert方法
     * @param $fields
     * @param $table
     * @param $rows
     * @return bool|mysqli_result|null
     */
    public function insert($fields, $table, $rows) {
        if(empty($fields) || empty($table) || empty($rows) || !is_array($rows)) {
            Library_Log::warning(__FILE__ . __LINE__ . ' fields or tables or rows are empty');
            return null;
        }
        $this->_sql = 'INSERT INTO ';
        $strTables = $this->makeString($table, self::TABLES, ', ');
        if(!empty($strTables) && is_string($strTables)) {
            $this->_sql .= $strTables . '(';
        }
        $strFields = $this->makeString($fields, self::FIELDS, ', ');
        if(!empty($strFields) && is_string($strFields)) {
            $this->_sql .= $strFields . ') VALUES';
        }

        foreach ($rows as $rowKey => $row) {
            foreach ($row as $fieldKey => $field) {
                $field = $this->filterFormat($field);
                if(Library_Util::getType($field) == 'string') {
                    $field = "'{$field}'";
                }
                $rows[$rowKey][$fieldKey] = $field;
            }
        }
        if(isset($key)) {
            unset($key);
        }
        if(isset($row)) {
            unset($row);
        }
        foreach ($rows as $key => $row) {
            $rows[$key] = '(' . implode(',', $row). ')';
        }
        $this->_sql .= implode(',', $rows);

        Library_Log::trace($this->_sql);
        $mysqlResult = $this->_db->query($this->_sql);
        if($mysqlResult) {
            $this->insertId = $this->_db->insert_id;
        } else{
            Library_Log::warning('mysql errno: ' . $this->_db->errno . ', mysql error :' . $this->_db->error);
        }
        return $mysqlResult;
    }

    /**
     * 调用mysqli的方法
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        Library_Log::trace(__FILE__ . $name);
        return call_user_func_array(array($this->_db, $name), $arguments);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
//        $this->_db->close();
//        Library_Log::trace('mysql connection is closed!');
    }

}