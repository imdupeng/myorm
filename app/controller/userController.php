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
     * 添加微信用户
     * */
    public function add_user(){
        $data = $_POST;
        $openid = $_GET['open_id'];

        $pdo = new \core\lib\model();
        $stmt = $pdo->prepare("insert into user(avalon,name,open_id,phone,disable)values(?,?,?,?,?)");
        $stmt->bindValue(1, $data['avalon']);
        $stmt->bindValue(2, $data['name']);
        $stmt->bindValue(3, $openid);
        $stmt->bindValue(4, $data['phone']);
        $stmt->bindValue(5, $data['disable']);
        $stmt->execute();
        $addId = $stmt->lastInsertId();
//        $count = $stmt->rowCount();//受影响行数
//        echo 'prepare方法影响行数：'.$count;
        if ($addId){
            $status = true;
            $code = '201';
            $message = '添加用户成功！';
            $data = $addId;
        }else{
            $status = true;
            $code = '252';
            $message = '添加用户失败！';
            $data = null;
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
     * */
    public function user_login(){
        $data = $_POST;
        $code = $data['code'];//wx.login得到的code
        if (!empty($code)){
            $openid = get_openid($code);
            if ($openid){
                $is_user_exist = is_user_exist($openid);
                if ($is_user_exist){//用户存在，登录
                    $status = true;
                    $code = '200';
                    $message = '登录成功！';
                    $data = '';
                }else{//用户不存在，跳转注册
                    $status = false;
                    $code = '201';
                    $message = '该用户未注册,跳转到注册！';
                    $data = ['url'=>'index.php/user/register/openid/'.$openid];
                }

            }else{
                $status = false;
                $code = '250';
                $message = '获取微信open_id错误！';
                $data = '';
            }
        }else{
            $status = false;
            $code = '251';
            $message = '未传入wx.login的code！';
            $data = '';
        }
        return Response::json($status,$code,$message,$data);
    }
}

