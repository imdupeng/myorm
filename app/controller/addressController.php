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
     * 获取零售客户收货地址列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页地址数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/address/list
     * */
    public function list(){
        list($offset, $pageSize, $page, $data) = $this->pagination('senderPagesize');
        $partner_id = $_REQUEST['partner_id'];
        $fields = implode(', ', [
            'id',
            'name',
            'phone',
            'address',
            'sheng',
            'shi',
            'xian',
        ]);

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            [$filter1, $paramName, $search] = $this->fulltextSearch(['name', 'phone'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;
        }
        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';

        $sql2 = "select $fields from address where partner_id='".$partner_id."' $filterString limit $offset,$pageSize";
        $stmt = $this->fastQuery($sql2,$param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true,350,'查询伙伴成功',$data);
    }



    /*
     * 添加伙伴地址
     * http://118.126.112.43:8080/index.php/address/create
     * */
    public function create(){
        $Rdata = (array)($_REQUEST ?? []);
        //允许外面传入的字段
        $allowFields = ['partner_id','name','phone','address','sheng','shi','xian','status' => 2,];
        
        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            'openid' => $openid,
            'status' => 2,
        ];

        $data3 = [$fields, $values, $data] = $this->dataForCreate($Rdata, $allowFields, $fixed);

        try {
            $sql = "
            insert into address ($fields) values ($values)
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
     * 更新伙伴地址
     * http://118.126.112.43:8080/index.php/address/update
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
        if (!empty($data['address'])){
            $pdata['address'] = $data['address'];
        }
        if (!empty($data['sheng'])){
            $pdata['sheng'] = $data['sheng'];
        }
        if (!empty($data['shi'])){
            $pdata['shi'] = $data['shi'];
        }
        if (!empty($data['xian'])){
            $pdata['xian'] = $data['xian'];
        }
        if (!empty($data['status'])){
            $pdata['status'] = $data['status'];
        }

        $allowFields = ['name','phone','status','address','sheng','shi','xian']; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);

        $Openod = $_SESSION['openid'];

        try {
            $sql = "
            update address
               set $fields
             where id = :id 
               and openid = :openid;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'openid' => $Openod,
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

    /*
     * 通过id查看伙伴地址
     * http://118.126.112.43:8080/index.php/address/view
     * */
    public function view()
    {
        $fields = implode(', ', [
            'id',
            'name',
            'phone',
            'address',
            'sheng',
            'shi',
            'xian',
            'status',
        ]);

        $param  = [];
        $pk = (string)($_REQUEST['id'] ?? '');
        $openid = $_SESSION['openid'];
        $sql2 = "
            select $fields from address
             where openid='".$openid."' 
               and id=$pk
        ";

        $stmt = $this->fastQuery($sql2, $param);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return Response::json(true, 350, '查询伙伴地址成功', $data);
    }

}