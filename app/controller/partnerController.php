<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;
use app\Myclass\Response;
use core\lib\config;

class partnerController extends \core\myorm_core{

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
     * 获取伙伴列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页伙伴数量
     * @param int $type 伙伴类型 2客户3代理商4厂商5已删除
     * http://myorm.com/index.php/partner/list/type/2/page/1/pagesize/5
     * */
    public function list(){
        [$offset, $pageSize, $page, $data] = $this->pagination('partnerPagesize');
//        $loginOpenid = $_SESSION['openid'];
        $user_id = $this->userId ;
        $type = $_REQUEST['type'];
        $sql2 = "
        select * from partner where user_id=$user_id and type=$type limit $offset,$pageSize";
        $stmt = $this->fastQuery($sql2);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true,350,'查询伙伴成功',$data);
    }

   

    /*
     * 添加伙伴
     * */
    public function create(){
        $data = $_REQUEST['data'] ?? [];
//        $data = $_REQUEST ?? [];//方便get提交测试

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
                return Response::json(true, 350, '伙伴创建成功', $lastId);
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

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update partner
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

            return Response::json(true, 350, '伙伴更新成功', $pk);

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 删除伙伴
     * */
    public function delete(){
        $pk = $_REQUEST['id'] ?? 0;

        $data = [
            'deleted_at' => time(),
            'type' => 5,
        ];

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

        try {
            $sql = "
            update partner
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
                return Response::json(true, 350, '伙伴删除成功', $pk);
            } else {
                return Response::error(false, 351, '伙伴删除失败', $pk);
            }

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

}