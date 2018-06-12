<?php
/**
 * Created by imdupeng.cn
 * Date: 2018/6/5
 * Time: 23:26
 */
namespace core\lib;

class config{
    static public $conf = array();
    static public function get($name,$file){
        /*
         * 1、判断配置文件是否存在
         * 2、判断配置是否存在
         * 3、缓存配置
         * */
        if (isset(self::$conf[$file])){//判断是否已经加载该配置，已经加载直接返回，否则加载。
            return self::$conf[$file];
        }else{//加载配置文件
            $confFile = MYORM.'/core/config/'.$file.'.php';
            if (is_file($confFile)){
                $conf = include_once $confFile;
                if (isset($conf[$name])){
                    self::$conf[$file] = $conf;
                    return $conf[$name];

                }else{
                    throw new \Exception('not the config name:'.$name);
                }
            }else{
                throw new \Exception('not found the config file:'.$file);
            }
        }
    }

    static public function allconfig($file){
        if (isset(self::$conf[$file])){//判断是否已经加载该配置，已经加载直接返回，否则加载。
            return self::$conf[$file];
        }else{//加载配置文件
            $confFile = MYORM.'/core/config/'.$file.'.php';
            if (is_file($confFile)){
                $conf = include_once $confFile;
                    self::$conf[$file] = $conf;
                    return $conf;
            }else{
                throw new \Exception('not found the config file:'.$file);
            }
        }
    }
}