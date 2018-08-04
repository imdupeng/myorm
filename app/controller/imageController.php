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
        parent::startSession();
        if (empty($_SESSION['openid'])) {
            $status = false;
            $code = 257;
            $message = '未登录，请登录！';
            $data = [];
            return Response::json($status, $code, $message, $data);
        }
    }


    public function upload(){
        if ((($_FILES["file"]["type"] == "image/gif")
                || ($_FILES["file"]["type"] == "image/jpeg")
                || ($_FILES["file"]["type"] == "image/pjpeg")
                || ($_FILES["file"]["type"] == "image/png")
            )
            && ($_FILES["file"]["size"] < 20000*1024))
        {
            if ($_FILES["file"]["error"] > 0)
            {
                return Response::json(false, 351, 'Invalid file', 0);
            }
            else
            {

                if (file_exists("images/" . $_FILES["file"]["name"]))
                {
                    return Response::json(false, 352, $_FILES["file"]["name"].' already exists. ', 0);
                }
                else
                {
                    move_uploaded_file($_FILES["file"]["tmp_name"],
                        "images/" . $_FILES["file"]["name"]);
                    return Response::json(true, 350, 'upload success!', "images/" . $_FILES["file"]["name"]);
                    $pdo = new \core\lib\model;
                    $sql1 = "insert into image(id,path,file_name,hash) values()";
                    $stmt = $pdo->query($sql1);
                    $row = $stmt->rowCount();
                }
            }
        }
        else
        {
            return Response::json(false, 351, 'Invalid file', 0);
        }
    }

    /*
     * 获取商品列表,分页
     * */
    public function modify(){
        $data = $_REQUEST['data'] ?? [];
//        $data = $_REQUEST ?? [];//方便get提交测试
        $pk = $_REQUEST['id'] ?? 0;

        $allowFields = []; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update goods
               set $fields
             where id = :id 
               and user_id = :user_id;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'user_id' => $this->userId,
            ];
            
            $effected = $this->fastUpdate($sql, $data, $params);

            return Response::json(true, 350, '商品更新成功', $pk);

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 删除商品,修改商品状态为4
     * */
    public function del(){
        $pk = $_REQUEST['id'] ?? 0;

        $data = [
            'deleted_at' => time(),
            'pstatus' => 4,
        ];

        $allowFields = []; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update goods
               set $fields
             where id = :id 
               and user_id = :user_id;
            ";
            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'user_id' => $this->userId,
            ];

            $effected = $this->fastUpdate($sql, $data, $params);
            if ($effected) {
                return Response::json(true, 350, '商品删除成功', $pk);
            } else {
                return Response::error(true, 351, '商品删除失败', $pk);
            }

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

}