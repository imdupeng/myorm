<?php
/**
 * Created by imdupeng.cn
 * Date: 2018/5/27
 * Time: 8:40
 * 入口文件
 * 1、定义常量
 * 2、加载函数库
 * 3、启动框架
 */

/*
 * 一、判断操作系统，并获取分隔符
 * */
if (PATH_SEPARATOR==':'){//LINU系统分隔符是：
    define('OS','Linux');
    define('SLASH','/');
}else{
    define('OS','windows');
    define('SLASH','\\');
}

/*
 * 二、定义目录
 * */
//定义当前框架所在目录
define('MYORM',__DIR__.SLASH);
//定义框架核心文件所在目录
define('CORE',MYORM.'core');
//定义项目文件所在目录
define('APP',MYORM.'app');
define('MODULE','app');

/**
 *三、定义是否开启调试模式
 */
define('DEBUG',true);
if (DEBUG){
    ini_set('display_errors','On');
}else{
    ini_set('display_errors','Off');
}

require_once CORE.'/config/const.php';//引入配置常量

/*
 * 四、加载函数库
 */
require_once CORE.'/common/myorm_functions.php';//引入公共函数
require_once CORE.'/myorm_core.php';//加载框架核心文件
spl_autoload_register('\core\myorm_core::load');//封装整个项目的include和require
\core\myorm_core::run(); //启动框架
