<?php
/**
 * Created by imdupeng.cn
 * Date: 2018/6/5
 * Time: 21:40
 */
namespace app\controller;
class indexController extends \core\myorm_core {
    public function index(){
        $model = new \core\lib\model();
        $sql = 'select user,host from user';
        $res = $model->query($sql);
        $data = $res->fetchAll();
//        print_r($_GET);
//        $router = new \core\lib\route;
//        print_r($router->contro);

        $this->assign('data',$data);
        $this->display('index.html');

    }
}

