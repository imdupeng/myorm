<?php
/**
 * Created by imdupeng
 * Date: 2018/6/5
 * Time: 22:24
 */
namespace core\lib;
use core\lib\config;
use PDO;

class model extends \PDO{
    public function __construct()
    {
        $database = config::allconfig('database');

        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
            // PDO::ATTR_EMULATE_PREPARES => false, // 防止将所有数据类型都转换成字符型 http://stackoverflow.com/questions/1197005/how-to-get-numeric-types-from-mysql-using-pdo
            // PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,SQL_MODE="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION,NO_AUTO_CREATE_USER"',

            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8,SQL_MODE="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"',
            PDO::ATTR_EMULATE_PREPARES   => true, //由于MySQL不支持同名参数多处使用，需PDO自行prepare
            PDO::ATTR_STRINGIFY_FETCHES  => false,

        );
        try {
            parent::__construct($database['DSN'], $database['USERNAME'], $database['PASSWD'], $options);
        } catch (\PDOException $e) {
            // 重新抛出异常 避免网络连接失败等原因连接失败时暴露数据库密码等敏感信息
            throw new \Exception("PDO 连接数据库失败：" . $e->getMessage());
        }
    }
}
