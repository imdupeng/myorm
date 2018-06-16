<?php
/**
 * Created by imdupeng
 * Date: 2018/6/5
 * Time: 21:40
 */
namespace app\controller;
use core\lib\config;
use app\Myclass\Response;

class userController extends \core\myorm_core {

    public function __construct()
    {
        //检测用户是否存在
    }

    /*
     * 获取微信open_id
     * */
    public function get_openid($code=''){
//        $code = $_GET['code'];//wx.login得到的code
        $appConfig = config::allconfig('weixin');//读取微信配置文件
        $appid = $appConfig['appid'];
        $appsecret = $appConfig['appsecret'];
        $json = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$appsecret.'&js_code='.$code.'&grant_type=authorization_code';
        header("Content-Type: application/json");
        $data = file_get_contents($json);
        $data = json_decode($data,true);
        $openid = $data['openid'];
        return $openid;
    }

    /*
     * user/add_user
     * 更新用户资料
     * */
    public function update_user(){
        if (empty($_SESSION['open_id'])) {
            $status = false;
            $code = '255';
            $message = '未登录，请登录！';
            $data = [];
            return response()->json($status,$code,$message,$data);
        }

            $data = $_REQUEST;
            $pdo = new \core\lib\model();
            $stmt = $pdo->prepare("update user set(avalon,name,phone,disable)values(?,?,?,?) where openid=?");
            $stmt->bindValue(1, $data['avalon']);
            $stmt->bindValue(2, $data['name']);
            $stmt->bindValue(5, $openid);
            $stmt->bindValue(3, $data['phone']);
            $stmt->bindValue(4, $data['disable']);
            $stmt->execute();
            $addId = $stmt->lastInsertId();
//        $count = $stmt->rowCount();//受影响行数
//        echo 'prepare方法影响行数：'.$count;
            if ($addId) {
                $status = true;
                $code = '201';
                $message = '更新用户成功！';
                $data = $addId;
            } else {
                $status = true;
                $code = '252';
                $message = '更新用户失败！';
                $data = [];
            }
        
        return Response::json($status,$code,$message,$data);
    }

    /*
     * 判断用户是否存在
     * */
    public function is_user_exist($openid=''){
        $pdo = new \core\lib\model();
        $stmt = $pdo->prepare("select id from user where open_id=?");
        $stmt->bindValue(1, $openid);
        $stmt->execute();
        $row_count = $stmt->rowCount();
        if ($row_count){
            return true;
        }else{
            return false;
        }
    }


    /*
     * user/user_login
     * 微信用户登录
     * 获取token：md5($openid+$time)
     * 
     * */
    public function user_login(){
        if ($_SESSION['openid']) {
            //已经登录
            $status = true;
            $code = '200';
            $message = '已经登录过了！';
            $data = ['openid'=>$openid];
        }else{
            $data = $_REQUEST;
            $code = $data['code'];//wx.login得到的code
            if (!empty($code)) {
                $openid = get_openid($code);
                if ($openid) {
                    $is_user_exist = is_user_exist($openid);
                    if ($is_user_exist) {//用户存在，返回用户openid
                        session_start();
                        session('openid', $openid);
                        $status = true;
                        $code = '200';
                        $message = '登录成功！';
                        $data = ['openid'=>$openid];
                    } else {//用户不存在，添加用户，返回用户id
                        $pdo = new \core\lib\model;
                        $stmt = $pdo->prepare("insert into user(open_id) values ?");
                        $stmt->bindValue(1, $openid);
                        $stmt->execute();
                        $row_count = $stmt->rowCount();
                        if ($row_count) {
                            session_start();
                            session('openid', $openid);
                            $status = true;
                            $code = '250';
                            $message = '新用户注册成功！';
                            $data = ['openid'=>$openid];
                        } else {
                            $status = false;
                            $code = '259';
                            $message = '新用户注册失败！';
                            $data = ['openid'=>$openid];
                        }
                    }
                } else {
                    $status = false;
                    $code = '250';
                    $message = '获取微信open_id错误！';
                    $data = [];
                }
            } else {
                $status = false;
                $code = '251';
                $message = '未传入wx.login的code！';
                $data = [];
            }
        }
        return Response::json($status,$code,$message,$data);
    }

    //注销登录
    public function logout(){
        unset($_SESSION['openid']);
                $status = true;
                $code = '250';
                $message = '注销成功！';
                $data = [];
                Response::json($status, $code, $message, $data);
        
    }
}

