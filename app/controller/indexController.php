<?php
/**
 * Created by imdupeng.cn
 * Date: 2018/6/5
 * Time: 21:40
 */
namespace app\controller;
class indexController extends \core\myorm_core {

    /*
     * index/index
     *
     * */
    public function index(){
        $stmt = new \core\lib\model();
        $sql = 'select * from user';
        $res = $stmt->query($sql);
        $data = $res->fetchAll();
        print_r($data);
//        $router = new \core\lib\route;
//        print_r($router->contro);

//        $this->assign('data',$data);
//        $this->display('index.html');

    }
}

