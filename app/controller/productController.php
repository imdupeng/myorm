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
    public function list(){
        [$offset, $pageSize, $page, $data] = $this->pagination();

        $sql = "
        select * from goods 
        where user_id={$this->userId} 
          and pstatus=2
        order by orderby desc 
        limit $offset, $pageSize";
        
        $stmt = $this->fastQuery($sql);

        $data['products'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return Response::json(true,350,'查询商品成功',$data);
    }

    public function test()
    {
        // 'id'
        $_REQUEST['data'] = [
            'user_id' => 1,
            'name' => 'demo123',
            'purchase_price' => 1,
            'wholesale_price' =>2,
            'retail_price' => 3,
            'pstatus' => 2
        ];
        // $this->create();


        // 'id'
        $_REQUEST['data'] = [
            'user_id' => 1,
            'name' => 'demo123-1234',
            'purchase_price' => 10,
            'wholesale_price' =>20,
            'retail_price' => 30,
            'pstatus' => 20
            ];
        $_REQUEST['id'] = 6;
        // $this->update();
    

        $this->delete();
    }

    /*
     * 获取商品列表,分页
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
            insert into goods ($fields) values ($values)
            ";
            [$effected, $lastId] = $this->fastInsert($sql, $data);

            if ($effected) {
                return Response::json(true, 350, '商品创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 获取商品列表,分页
     * */
    public function update(){
        $data = $_REQUEST['data'] ?? [];
        $pk = $_REQUEST['id'] ?? 0;

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

            return Response::json(true, 350, '商品更新成功', $pk);

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