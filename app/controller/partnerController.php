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
        //检测用户是否存在
    }

    /*
     * 获取伙伴列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页伙伴数量
     * */
    public function list(){
        [$offset, $pageSize, $page, $data] = $this->pagination('partnerPagesize');
        $loginOpenid = $_SESSION['openid'];
        $type = $_REQUEST['type'];

        $sql2 = "
        select * from partner where user_id=$loginOpenid and type=$type limit $offset,$pageSize";
        
        $stmt = $this->fastQuery($sql2);

        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return Response::json(true,350,'查询伙伴成功',$data);
    }

   

    /*
     * 添加伙伴
     * */
    public function create(){
        $data = $_REQUEST['data'] ?? [];

        //允许外面传入的字段
        $allowFields = []; 
        
        // 固定值, 补充或覆盖到 $data 中
        $fixed = [
            'user_id' => $this->userId,
        ];

        [$fields, $values, $data] = $this->dataForCreate($data, $allowFields, $fixed);

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
     * 获取商品列表,分页
     * */
    public function delete(){
        $pk = $_REQUEST['id'] ?? 0;

        $data = [
            'deleted_at' => time(),
            'pstatus' => 4,
        ];

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

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