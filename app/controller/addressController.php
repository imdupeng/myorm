<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;
use app\Myclass\Response;
use core\lib\config;

class addressController extends \core\myorm_core{

    public function __construct()
    {
        //检测是否登录
        if(!empty($_POST['PHPSESSID'])){
            session_id($_POST['PHPSESSID']);
            session_start();
        }
        if (empty($_SESSION['openid'])) {
            $status = false;
            $code = 257;
            $message = '未登录，请登录！';
            $data = [];
            return Response::json($status, $code, $message, $data);
        }
    }



    /*
     * 添加伙伴地址
     * */
    public function create(){
        $data = $_REQUEST['data'] ?? [];

        //判断传参中partner_id是否是当前登录user_id的伙伴
        $partner_id = $data['partner_id'];
        $user_id = $this->userId;
        $pdo = new \core\lib\model;
        $sql1 = "select id from partner where id=$partner_id and user_id=$user_id";
        $stmt = $pdo->query($sql1);
        $row = $stmt->rowCount();
        if (empty($row)){
            return Response::json(false, 352, '非法操作，该伙伴不属于您！', 0);
        }

        //partner_id的收货地址可以存在多个

        //允许外面传入的字段
        $allowFields = [];
        
        // 固定值, 补充或覆盖到 $data 中
        $fixed = [
            'user_id' => $this->userId,
        ];

        $data3 = [$fields, $values, $data] = $this->dataForCreate($data, $allowFields, $fixed);

        try {
            $sql = "
            insert into partner ($fields) values ($values)
            ";
            [$effected, $lastId] = $this->fastInsert($sql, $data);

            if ($effected) {
                return Response::json(true, 350, '伙伴地址创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 更新伙伴信息
     * */
    public function update(){
        $data = $_REQUEST['data'] ?? [];
        $pk = $_REQUEST['id'] ?? 0;
        $partnerid = $_REQUEST['partner_id'] ?? 0;

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update address
               set $fields
             where id = :id 
               and user_id = :user_id;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'partner_id' => $partnerid,
            ];
            
            $effected = $this->fastUpdate($sql, $data, $params);

            return Response::json(true, 350, '伙伴地址更新成功', $pk);

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 删除伙伴地址
     * deleted_at记录值存在，则为已删除数据
     * */
    public function delete(){
        $pk = $_REQUEST['id'] ?? 0;
        $partnerid = $_REQUEST['partner_id'] ?? 0;

        $data = [
            'deleted_at' => time(),
        ];

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update address
               set $fields
             where id = :id 
               and partner_id = :partner_id;
            ";
            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'partner_id' => $partnerid
            ];

            $effected = $this->fastUpdate($sql, $data, $params);
            if ($effected) {
                return Response::json(true, 350, '伙伴地址删除成功', $pk);
            } else {
                return Response::error(true, 351, '伙伴地址删除失败', $pk);
            }

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

}