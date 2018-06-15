<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;
use app\Myclass\Response;
use core\lib\config;

class productController extends \core\myorm_core{

    public function __construct()
    {
        //检测用户是否存在
    }

    /*
     * 获取商品列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示商品数量
     * */
    public function productList(){
        $defaultPageSize = config::get(productPagesize,weixin);
        $page = isset($_REQUEST['page'])?$_REQUEST['page']:1;
        $pageSize = isset($_REQUEST['pagesize'])?$_REQUEST['pagesize']:$defaultPageSize;//从weixin配置文件获取默认每页展示商品数
        if (!is_numeric($page) || !is_numeric($pageSize)){
            return Response::json(false,'350','数据不合法');
        }
        //查询数据
        $offset = ($page-1)*$pageSize;
        $sql = "select * from goods where pstatus=2 order by orderby desc limit ".$offset.",".$pageSize;
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data['page']=$page;
        $data['pagesize']=$pageSize;
        $data['products'] = $stmt->fetchAll($pdo::FETCH_ASSOC);
        return Response::json(true,350,'查询商品列表成功',$data);
    }

}