<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;
use app\Myclass\Response;
use core\lib\config;

class senderController extends \core\myorm_core{

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


    /*
     * 获取发货人列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页发货人数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/sender/list
     * */
    public function list(){
        list($offset, $pageSize, $page, $data) = $this->pagination('senderPagesize');
        $openid = $_SESSION['openid'];
        $fields = implode(', ', [
            'id',
            'name',
            'phone',
            'deleted_at',
            'status',
        ]);

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            list($filter1, $paramName, $search) = $this->fulltextSearch(['name', 'phone'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;
        }
        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';

        $sql2 = "select $fields from sender where openid=:_openid and status=2 $filterString limit $offset,$pageSize";
        $param  = [
            '_openid' => $openid
        ];
        $stmt = $this->fastQuery($sql2,$param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true,350,'查询伙伴成功',$data);
    }

    /*
     * 查看发货人详情
     * */
    public function view()
    {
        $fields = implode(', ', [
            'id',
            'name',
            'phone',
            'status',
        ]);

        $param  = [];
        $pk = (string)($_REQUEST['id'] ?? '');
        $openid = $_SESSION['openid'];
        $param = [
            '_openid' => $openid,
            '_pk' => $pk,
        ];
        $sql2 = "
            select $fields from sender
             where openid=:_openid
               and id=:_pk
        ";

        $stmt = $this->fastQuery($sql2, $param);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return Response::json(true, 350, '查询发货人成功', $data);
    }

   

    /*
     * 添加发货人
     * http://118.126.112.43:8080/index.php/sender/create
     * */
    public function create(){
        $Rdata = (array)($_REQUEST ?? []);
        //允许外面传入的字段
        $allowFields = ['name','phone','status'];
        
        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            '_openid' => $openid,
            'status' => 2,
        ];

        $data3 = $this->dataForCreate($Rdata, $allowFields, $fixed);
        list($fields, $values, $data) = $data3;
        
        try {
            $sql = "
            insert into sender ($fields) values ($values)
            ";
            list($effected, $lastId) = $this->fastInsert($sql, $data);

            if ($effected) {
                return Response::json(true, 350, '发货人创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 更新发货人信息
     * http://118.126.112.43:8080/index.php/sender/update
     * */
    public function update(){
        $data = (array)($_REQUEST ?? []);
        $pk = (int)($_REQUEST['id'] ?? 0);

        $pdata = [];
        if (!empty($data['name'])){
            $pdata['name'] = $data['name'];
        }
        if (!empty($data['phone'])){
            $pdata['phone'] = $data['phone'];
        }
        if (!empty($data['status'])){
            $pdata['status'] = $data['status'];
        }

        $allowFields = ['name','phone','status']; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($data, $allowFields);
        $openid = $_SESSION['openid'];
        try {
            $sql = "
            update sender
               set $fields
             where id = :id 
               and openid = :_openid
            ";

            // 条件上的参数,注意不要与字段名重复
            $params1 = [
                'id' => $pk,
                '_openid' => $openid,
            ];
            $params = array_merge($params1,$pdata);

            $effected = $this->fastUpdate($sql, $data, $params);

            return Response::json(true, 350, '发货人信息更新成功', $pk);

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 删除发货人信息
     * http://118.126.112.43:8080/index.php/sender/delete
     * */
    public function del(){
        $pk = $_REQUEST['id'] ?? 0;

        $data = [
            'deleted_at' => time(),
            'status' => 4,
        ];

        $allowFields = []; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($data, $allowFields);
        $openid = $_SESSION['openid'];
        try {
            $sql = "
            update sender
               set $fields
             where id = :id 
               and openid = :_openid
            ";
            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                '_openid' => $openid,
            ];

            $effected = $this->fastUpdate($sql, $data, $params);
            if ($effected) {
                return Response::json(true, 350, '发货人删除成功', $pk);
            } else {
                return Response::error(false, 351, '发货人删除失败', $pk);
            }

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

}