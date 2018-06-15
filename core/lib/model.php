<?php
/**
 * Created by imdupeng
 * Date: 2018/6/5
 * Time: 22:24
 */
namespace core\lib;
use core\lib\config;
class model extends \PDO{
    public function __construct()
    {
        $database = config::allconfig('database');
        try{
            parent::__construct($database['DSN'], $database['USERNAME'], $database['PASSWD']);
        }catch (\PDOException $e){
            p('数据库连接失败:'.$e->getMessage());
        }
    }
}