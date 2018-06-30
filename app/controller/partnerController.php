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
        $openid = $_SESSION['openid'];
        $type = $_REQUEST['type'];
        $status = $_REQUEST['status'];

        $fields = implode(', ', [
            'id',
            'type',
            'wechat',
            'name',
            'phone',
            'note',
            'status',
        ]);
        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            [$filter1, $paramName, $search] = $this->fulltextSearch(['name', 'phone','wechat'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;
        }
        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';

        $sql2 = "select $fields from partner where openid='".$openid."' 
               and status='".$status."' and type='".$type."'
               $filterString 
             limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2,$param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true,350,'查询伙伴成功',$data);
    }

   

    /*
     * 添加伙伴
     * http://118.126.112.43:8080/index.php/partner/create
     * */
    public function create(){
        $Rdata = (array)($_REQUEST ?? []);
        //允许外面传入的字段
        $allowFields = ['openid','type','wechat','name','phone','note','status'];

        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            'openid' => $openid,
            'status' => 2,
        ];

        $data3 = [$fields, $values, $data] = $this->dataForCreate($Rdata, $allowFields, $fixed);

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
     * http://118.126.112.43:8080/index.php/partner/update
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
        if (!empty($data['type'])){
            $pdata['type'] = $data['type'];
        }
        if (!empty($data['wechat'])){
            $pdata['wechat'] = $data['wechat'];
        }
        if (!empty($data['note'])){
            $pdata['note'] = $data['note'];
        }


        $allowFields = ['name','phone','status','type','wechat','note']; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);
        $Openid = $_SESSION['openid'];

        try {
            $sql = "
            update partner
               set $fields
             where id = :id 
               and openid = '".$Openid."'
            ";

            // 条件上的参数,注意不要与字段名重复
            $params1 = [
                'id' => $pk,
            ];
            $params = array_merge($params1,$pdata);
            
            $effected = $this->fastUpdate($sql, $data, $params);

            return Response::json(true, 350, '伙伴更新成功', $pk);

        } catch(Exception $e) {
            return Response::exception(351, $e);
        }

    }

    /*
     * 删除伙伴
     * http://118.126.112.43:8080/index.php/partner/delete
     * */
    public function delete(){
        $pk = $_REQUEST['id'] ?? 0;

        $data = [
            'deleted_at' => time(),
            'status' => 4,
        ];

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);
        $Openid = $_SESSION['openid'];

        try {
            $sql = "
            update partner
               set $fields
             where id = :id 
               and openid = '".$Openid."'
            ";
            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
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

    /*
     * 通过id查看伙伴
     * http://118.126.112.43:8080/index.php/partner/view
     * */
    public function view()
    {
        $fields = implode(', ', [
            'id',
            'name',
            'phone',
            'type',
            'wechat',
            'note',
            'status',
        ]);

        $param  = [];
        $pk = (string)($_REQUEST['id'] ?? '');
        $openid = $_SESSION['openid'];
        $sql2 = "
            select $fields from partner
             where openid='".$openid."' 
               and id=$pk
        ";

        $stmt = $this->fastQuery($sql2, $param);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return Response::json(true, 350, '查询伙伴地址成功', $data);
    }

}