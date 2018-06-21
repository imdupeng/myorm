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

    protected function fastQuery($sql, $params = [])
    {
        $pdo = new \core\lib\model;
        if(empty($params)) {
            return $pdo->query($sql);
        }
        $stmt = $pdo->prepare($sql);
        foreach($params as $n => $value) {
            $pdo->bindValue($n, $value);
        }
        return $stmt->execute();
    }


    protected function dataForCreate($data, $allowFields=[], $fixed=[])
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

    protected function dataForUpdate($data, $allowFields=[])
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

    protected function fastInsert($sql, $data)
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


    protected function fastUpdate($sql, $data, $param = [])
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

}