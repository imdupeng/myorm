<?php
/**
 * Created by imdupeng
 * Date: 2018/6/5
 * Time: 21:40
 */

namespace app\controller;

use core\lib\config;
use app\Myclass\Response;

class userController extends \core\myorm_core
{

    public function __construct()
    {
        //检测是否登录
    }

    /*
     * 获取微信open_id
     * */
    public function get_openid($code = '')
    {
        if ($code== '') {
            $code = $_REQUEST['code'];//wx.login得到的code
        }
        $appConfig = config::allconfig('weixin');//读取微信配置文件
        $appid = $appConfig['appid'];
        $appsecret = $appConfig['appsecret'];
        $json = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$appsecret.'&js_code='.$code.'&grant_type=authorization_code';
        header("Content-Type: application/json");
        $data = file_get_contents($json);
        //$data = '{"session_key":"ZZK4m9oqtVlKtEz87SVURQ==","openid":"o5N5M5fJua3IkxE5V82kVZ1Tcs3I"}';
        
        header("code:$code");
        header("openid:{$data}");

        $data = json_decode($data, true);
        // file_put_contents('temp/open_id.log', $data, FILE_APPEND);
		
        if(empty($data['openid'])){
			return null;
        }
        return array($data['openid'], $data);
    }


    /*
     * 上传头像
     * */
    public function uploadimage()
    {
        parent::startSession();
        if (empty($_SESSION['openid'])) {
            $status = false;
            $code = 257;
            $message = '未登录，请登录！';
            $data = [];
            return Response::json($status, $code, $message, $data);
        }
        //var_dump($_FILES["file"]);exit;
        //array(5) { ["name"]=> string(17) "56e79ea2e1418.jpg" ["type"]=> string(10) "image/jpeg" ["tmp_name"]=> string(43) "C:\Users\asus\AppData\Local\Temp\phpD07.tmp" ["error"]=> int(0) ["size"]=> int(454445) }
        //判断上传的文件是否出错,是的话，返回错误
        if ($_FILES["file"]["error"]) {
            echo $_FILES["file"]["error"];
            return Response::json(false, 251, '文件上传失败', 0);
//            return [false, 251, '文件上传失败', 0];
        } else {
            //没有出错
            //加限制条件
            //判断上传文件类型为png或jpg且大小不超过1024000B
            if (($_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg") && $_FILES["file"]["size"] < 10240000) {
                //防止文件名重复
                $name = $_FILES["file"]["name"];
                $filename = "./static/images/" . $_FILES["file"]["name"];
                //转码，把utf-8转成gb2312,返回转换后的字符串， 或者在失败时返回 FALSE。
//                $filename = iconv("UTF-8", "gb2312", $filename);
                //检查文件或目录是否存在
                if (file_exists($filename)) {
                    echo "该文件已存在";
                } else {
                    //保存文件,   move_uploaded_file 将上传的文件移动到新位置
                    move_uploaded_file($_FILES["file"]["tmp_name"], $filename);//将临时地址移动到指定地址
                    //写入image表
                    $pdo = new \core\lib\model();
                    $stmt = $pdo->prepare("insert into image(path,file_name) values (:filename,:name)");
					$stmt->execute(array('filename'=>$filename,'name'=>$name));
                    $addId = $pdo->lastInsertId();
					$data = ['imgid'=>$addId];
                    return Response::json(true, 250, '文件上传成功', $data);
//                    return [true, 250, '文件上传成功', $addId];
                }
            } else {
                return Response::json(false, 252, '文件类型不对', 0);
//                return [false, 252, '文件类型不对', 0];
            }
        }
    }

    /*
     * user/add_user
     * 更新用户资料
     * */
    public function update_user()
    {
        parent::startSession();
        if (empty($_SESSION['openid'])) {
            $status = false;
            $code = 257;
            $message = '未登录，请登录！';
            $data = [];
            return Response::json($status, $code, $message, $data);
        }
        $data = $_REQUEST;
        $avalon = $this->uploadimage($_REQUEST['avalon']);
        $pdo = new \core\lib\model();
        $stmt = $pdo->prepare("update user set(avalon,name,phone,disable)values(:avalon,:name,:phone,:disable) where openid=:open_id");
        $stmt->execute([
			'avalon'=>$data['avalon'],
			'name'=>$data['name'],
			'phone'=>$data['phone'],
			'disable'=>$data['disable'],
			'open_id'=>$_SESSION['open_id']
		]);
//            $addId = $stmt->lastInsertId();
        $count = $stmt->rowCount();//受影响行数
//        echo 'prepare方法影响行数：'.$count;
        if ($addId) {
            $status = true;
            $code = '250';
            $message = '更新用户成功！';
            $data = $count;
        } else {
            $status = true;
            $code = '251';
            $message = '更新用户失败！';
            $data = [];
        }
        return Response::json($status, $code, $message, $data);
    }

    /*
     * 判断用户是否存在
     * */
    public function is_user_exist($openid = '')
    {
        $pdo = new \core\lib\model();
        $stmt = $pdo->prepare("select id from user where open_id = :open_id");
        $stmt->execute(array('open_id'=>$openid));
        $row_count = $stmt->fetchALL();
        if ($row_count) {
            return true;
        } else {
            return false;
        }
    }


    /*
     * user/user_login
     * 微信用户登录
     * 获取token：md5($openid+$time)
     * 
     * */
    public function user_login()
    {
        if (!empty($_SESSION['openid'])) {
            //已经登录
            $status = true;
            $code = '200';
            $message = '已经登录过了！';
            $data = ['openid' => $openid];
        } else {
            $data = $_POST;
			if(!is_array($data)){
				$data = json_decode($data);
			}
            if (!empty($data['code'])) {
                $code = $data['code'];//wx.login得到的code
                list($openid, $wxMsg) = $this->get_openid($code);
                if ($openid) {
                    $is_user_exist = $this->is_user_exist($openid);
                    if ($is_user_exist) {//用户存在，返回用户openid
                        
                        parent::startSession();
                        $_SESSION["openid"] = $openid;
                        $status = true;
                        $code = '200';
                        $message = '登录成功！';
                        $data = ['openid' => $openid];
                    } else {//用户不存在，添加用户，返回用户id
					//echo $openid;exit;
                        $pdo = new \core\lib\model;
                        $stmt = $pdo->prepare("insert into user(open_id) values (:open_id)");
                        $stmt->execute(array('open_id'=>$openid));
                        $row_count = $stmt->rowCount();
                        if ($row_count) {

                            parent::startSession();
                            $_SESSION['openid'] = $openid;
                            $status = true;
                            $code = '251';
                            $message = '新用户注册成功！';
                            $data = ['openid' => $openid];
                        } else {
                            $status = false;
                            $code = '259';
                            $message = '新用户注册失败！';
                            $data = ['openid' => $openid];
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
        return Response::json($status, $code, $message, $data);
    }

    //注销登录
    public function logout()
    {
        unset($_SESSION['openid']);
        $status = true;
        $code = '250';
        $message = '注销成功！';
        $data = [];
        Response::json($status, $code, $message, $data);
    }
}

