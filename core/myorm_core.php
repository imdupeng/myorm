<?php
/**
 * Created by imdupeng
 * Date: 2018/5/27
 * Time: 12:23
 */
namespace core;

use core\lib\config;

class myorm_core{
    public static $classMap = array();//防止重复引入类
    public $assign;

    /*
     * 启动框架索要调用的方法
     * */
    static public function run()
    {
        $route = new \core\lib\route();
        $contrlClass = $route->contro;
        $action =$route->action;
//        print_r($action);exit;
        $contrlFile = APP.'/controller/'.$contrlClass.'Controller.php';
        $controllClass = MODULE.'\controller\\'.$contrlClass.'Controller';

        if(is_file($contrlFile)){

            require_once $contrlFile;

            $ctrl = new $controllClass();
            if (method_exists($ctrl,$action)){
                $ctrl->init();
                $ctrl->{$action}();
            }else{
                die('not fund action:'.$action);
            }
        }else{
            die('not fund controller:'.$contrlClass);
        }

    }
    /*
     * 自动加载类库
     * */
    static public function load($class)
    {
        //引入类
        //new \core\route();
        //$class = '\core\route';
        //目的是：转换成为MYORM.'/core/route.php';
        if (isset($classMap[$class])){
            return true;
        }else{
            $class2 = str_replace('\\','/',$class);
            if (is_file(MYORM.$class2.'.php')){
                include_once MYORM.$class2.'.php';
                self::$classMap[$class] = $class2;
            }else{
                return false;
            }
        }
    }

    public function assign($name,$value){
        $this->assign[$name] = $value;

    }

    public function display($file){
        $file = APP.'/views/'.$file;
        if (is_file($file)){
            extract($this->assign);
            include_once $file;
        }
    }



    protected function init()
    {
        $this->userId = 1;
    }
    
    protected function pagination($what='productPagesize')
    {

        $defaultPageSize = config::get($what,'weixin');
        $page = (int)($_REQUEST['page'] ?? 1);
        $pageSize = (int)($_REQUEST['pagesize'] ?? $defaultPageSize);
        if($page <= 1) {
            $page = 1;
        }
        if($pageSize <= 1) {
            $pageSize = 1;
        }
        if($pageSize > 100) {
            $pageSize = 100;
        }

        $offset = ($page-1)*$pageSize;
        $data = [
            'page'=> $page,
            'pagesize'=> $pageSize
        ];
        return [$offset, $pageSize, $page, $data];
    }

    protected function fulltextSearch(array $fields, string $keywords, string $paramName = 'keywords')
    {
        if (FULLTEXT_SUPPORT) {
            $match = implode(',', $fields);
            $filter = 'MATCH ($match) AGAINST (:{$paramName} IN BOOLEAN MODE)';
            $param = $keywords;
        } else {
            foreach ($fields as $field) {
                $filter[] = '{$field} like :{$paramName}';
            }
            $filter = "(" . implode(' or ', $filter) . ")";
            $param = '%' . $keywords . '%';
        }
        return [$filter, $paramName, $param];
    }

    protected function fastQuery(string $sql, array $params = [])
    {
        $pdo = new \core\lib\model;
        if(empty($params)) {
            return $pdo->query($sql);
        }
        $stmt = $pdo->prepare($sql);
        foreach($params as $n => $value) {
            $stmt->bindValue($n, $value);
        }
        return $stmt->execute();
    }


    protected function dataForCreate(array $data, array $allowFields=[], array $fixed=[])
    {
        if ($allowFields) {//如果允许值存在，则取出交集
            $data = array_intersect_key($data, array_fill_keys($allowFields, 1));
        }
        if($fixed) {
            $data = array_merge($data, $fixed);
        }

        $fields = array_keys($data);//返回键名数组
        $values = array_fill(0, count($fields), '?');

        $data = array_values($data);//返回值的数组
        array_unshift($data, null); // 下标 0 是被忽略的;
        unset($data[0]);

        return [implode(',', $fields), implode(',', $values), $data];
    }

    protected function dataForUpdate(array $data, array $allowFields=[])
    {
        if ($allowFields) {
            $data = array_intersect_key($data, array_fill_keys($allowFields, 1));
        }

        $fields = array_keys($data);
        foreach($fields as $key => $field) {
            $fields[$key] = "$field = :$field";
        }

        return [implode(', ', $fields), $data];
    }

    protected function fastInsert(string $sql, array $data)
    {
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare($sql);
        foreach($data as $n => $value) {
            $stmt->bindValue($n, $value);
        }
        $effected = $stmt->execute();
        $lastId   = $effected ? $pdo->lastInsertId() : null;
        return [$effected, $lastId];
    }

    public function bulkInsert(string $tableName, array $rows, $ignoreDuplicate = false)
    {
        if (!is_array($rows) || !is_array(current($rows))) {
            throw new Exception("param rows must be a 2D array");
        }

        $row = current($rows);
        if (empty($row)) {
            throw new Exception("param rows must be a 2D array");
        }

        $fields = array_keys($row);
        $binding = [];
        foreach($fields as $field) {
            $binding[] = ":" . $field;
        }

        // MSSQL: 利用索引选项 CREATE UNIQUE INDEX AK_Index ON #Test (C1) WITH (IGNORE_DUP_KEY = ON);
        // postgres 9.5:  insert into citys values('Hanzhoug'),('shenzhen') on conflict do nothing
        $ignore = $ignoreDuplicate ? 'IGNORE' : '';

        $sql = "INSERT $ignore INTO /*prefix*/$tableName ("
         . implode(", ", $fields) . ") VALUES ("
         . implode(', ', $binding)
         . ")";
         
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare($sql);
        foreach ($rows as $row) {
            foreach ($fields as $field) {
                $stmt->bindValue(":{$field}", $row[$field] ?? null);
            }
            $effected += $stmt->execute();
        }
        $stmt   = null;

        return $effected;
    }

    // 更新关联子表的数据
    public function columnOf($table, $columnName)
    {
        $data = [];
        foreach($table as $row) {
            $data[] = $row[$columnName];
        }
        return $data;
    }

    protected function fastUpdate(string $sql, array $data, array $param = [])
    {
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare($sql);
        foreach($data as $n => $value) {
            $stmt->bindValue($n, $value);
        }
        foreach($param as $n => $value) {
            $stmt->bindValue($n, $value);
        }
        $effected = $stmt->execute();
        return $effected;
    }

    protected function combineFields(array $table, string $field, array $subTable, string $field2)
    {
        $keyMap = [];
        foreach($subTable as $row) {
            $key = $row[$field2];
            unset($row[$field2]);
            $keyMap[$key] = $row;
        }

        foreach($table as $idx => $row) {
            $key = $row[$field];
            if (isset($keyMap[$key])) {
                $keyMap[$key] =  array_merge($keyMap[$key], $row);
            } else {
            }
        }
        return array_values($keyMap);
    }

    protected function combineList(array $table, string $field, array $subTable, string $field2, $asName = 'xx')
    {
        $keyMap = [];
        foreach($subTable as $row) {
            $key = $row[$field2];
            unset($row[$field2]);
            if (isset($keyMap[$key])) {
                $keyMap[$key][] = $row;
            } else {
                $keyMap[$key] = [$row];
            }
        }

        foreach($table as $key => $row) {
            if (isset($keyMap[$key])) {
                $table[$key][$asName] = $keyMap[$key];
            }
        }
        return array_values($table);
    }

}