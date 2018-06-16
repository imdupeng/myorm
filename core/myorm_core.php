<?php
/**
 * Created by imdupeng
 * Date: 2018/5/27
 * Time: 12:23
 */
namespace core;
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
                $ctrl->$action();
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

}