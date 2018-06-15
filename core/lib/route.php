<?php
/**
 * Created by imdupeng
 * Date: 2018/5/27
 * Time: 12:38
 */
namespace core\lib;
use core\lib\config;
class route
{
    public $contro = '';
    public $action = '';
    public function __construct()
    {
        //xxx.com/index(控制器)/index（方法）
        /*
         * 1、隐藏index.php  //暂时不管
         * 2、获取url中的参数部分
         * 3、返回对应的控制器和方法
         * */
//        print_r($_SERVER['REQUEST_URI']);exit;
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='/' && $_SERVER['REQUEST_URI']!='/index.php' && $_SERVER['REQUEST_URI']!='/index.php/')
        {
            //带参数,且参数不为空
            $real_url = substr($_SERVER['REQUEST_URI'],11);//'/index.php/controller/action'解析出'/controller/action'这样的参数
            $patharr = explode('/',$real_url);//字符串controller/action转换为数组[ [0] => index,[1] => index];
            //还要判断是请求的否控制器和方法都存在，例如请求的是'/index.php/controller'没有action
            if (isset($patharr[0])){
                $this->contro = $patharr[0];
            }
            if (isset($patharr[1]) && !empty($patharr[1])){
                $this->action = $patharr[1];
            }else{
                $this->action = config::get('contro','route');
            }
            //把url的多余部分，转化为get属性和值
            //先把url的controller和action去掉
            unset($patharr[0]);
            unset($patharr[1]);
            $count_get = count($patharr);
            //unset后，$patharr数组是从2开始的
            $i = 2;
            while($i<=$count_get){
                if ($patharr[$i+1] !='' && $patharr[$i+1]!=null){
                    $_REQUEST[$patharr[$i]] = $patharr[$i+1];
                }
                $i += 2;
            }
        }else{
            //不带参数
            $this->contro = config::get('contro','route');
            $this->action = config::get('action','route');
//            echo config::get('contro','route');exit;
        }

    }
}