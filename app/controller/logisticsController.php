<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;

use app\Myclass\Response;
use core\lib\config;

class logisticsController extends \core\myorm_core
{
    public function __construct()
    {
        //检测是否登录
        if (!empty($_POST['PHPSESSID'])) {
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
     * 获取运单列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示商品数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/logistics/list
     * */
    public function list()
    {
        [$offset, $pageSize, $page, $data] = $this->pagination('productPagesize');

        $fields = implode(', ', [
            'id',
            'first_order_id',
            'status',
            'number',
            'company_id',
            'image_id',
            'send_time',
            'create_at',
            'info'
        ]);
        $openid = $_SESSION['openid'];

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            [$filter1, $paramName, $search] = $this->fulltextSearch(['number'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;
        }

        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';
        $sql2 = "
            select $fields from logistics_bill 
             where user_id='".$openid."' 
               $filterString
            limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2, $param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true, 350, '查询物流运单成功', $data);
    }


    /*
     * 获取商品详情
     * @param int $id 商品id
     * http://118.126.112.43:8080/index.php/logistics/view
     * */
    public function view()
    {
        $number = $_REQUEST['number'];
        $fields = implode(', ', [
            'id',
            'first_order_id',
            'status',
            'number',
            'company_id',
            'image_id',
            'send_time',
            'create_at',
            'info'
        ]);

        $param  = [];
        $openid = $_SESSION['openid'];
        $sql2 = "
            select * from logistics_bill
             where user_id='".$openid."' 
               and number='".$number."' 
        ";
        
        $stmt = $this->fastQuery($sql2, $param);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // 图片
        if ($data) {
            $data['images'] = $this->fetchImages($pk);
        }

        return Response::json(true, 350, '查询订单物流运单成功', $data);
    }



    /*
     * 运单创建
     * http://118.126.112.43:8080/index.php/logistics/create
     * */
    public function create()
    {
        $Rdata = (array)($_REQUEST ?? []);

        //允许外面传入的字段
        $allowFields = ['first_order_id','status','number','company_id','image_id','send_time','info'];
        
        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            'user_id' => $openid,
            'create_at' => time(),
        ];

        [$fields, $values, $data] = $this->dataForCreate($Rdata, $allowFields, $fixed);

        try {
            $sql = "
            insert into logistics_bill ($fields) values ($values)
            ";
            [$effected, $lastId] = $this->fastInsert($sql, $data);

            // 处理图片
            if ($lastId && isset($data['images'])) {
                $this->createImageList($lastId, (array)$data['images']);
            }

            if ($effected) {
                return Response::json(true, 350, '运单创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }

    /*
     * 运单更新
     * http://118.126.112.43:8080/index.php/logistics/create
     * */
    public function update()
    {
        $data = (array)($_REQUEST ?? []);
        $pk = (int)($_REQUEST['id'] ?? 0);
        $pdata = [];
        if (!empty($data['first_order_id'])) {
            $pdata['first_order_id'] = $data['first_order_id'];
        }
        if (!empty($data['status'])) {
            $pdata['status'] = $data['status'];
        }
        if (!empty($data['number'])) {
            $pdata['number'] = $data['number'];
        }
        if (!empty($data['company_id'])) {
            $pdata['company_id'] = $data['company_id'];
        }
        if (!empty($data['image_id'])) {
            $pdata['image_id'] = $data['image_id'];
        }
        if (!empty($data['send_time'])) {
            $pdata['send_time'] = $data['send_time'];
        }
        if (!empty($data['create_at'])) {
            $pdata['create_at'] = $data['create_at'];
        }
        if (!empty($data['info'])) {
            $pdata['info'] = $data['info'];
        }

        $allowFields = ['first_order_id','status','number','company_id','image_id','send_time','create_at','info']; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($pdata, $allowFields);
        $openid = $_SESSION['openid'];

        try {
            $sql = "
            update goods
               set $fields
             where id = :id 
               and user_id = :openid;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params1 = [
                'id' => $pk,
                'openid' => $openid,
            ];
            $params = array_merge($params1, $pdata);
            $effected = $this->fastUpdate($sql, $data, $params);

            // 处理图片
            if ($pk && isset($data['image_id']) && !empty($data['image_id'])) {
                $this->upgradeImageList($pk, $data['image_id']);
            }

            return Response::json(true, 350, '运单更新成功', $pk);
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }

    protected function createImageList(int $pk, array $images)
    {
        // 处理图片
        if ($pk && !empty($images)) {
            $rows= [];
            foreach ($images as $image) {
                $rows[] = [
                    'goods_id' => $pk,
                    'image_id' => $image
                ];
            }
            $this->bulkInsert('goods_image', $rows);
        }
    }

    protected function upgradeImageList(int $pk, array $images)
    {
        $sql = "
            select image_id
                from goods_image
            where goods_id = :goods_id
            ";
        $params = [
            'goods_id' => $pk,
        ];
        $stmt = $this->fastQuery($sql, $param);
        $existsImages = $this->columnOf($stmt->fetchAll(\PDO::FETCH_ASSOC));
        $deleteImages = array_diff($existsImages, $images);
        $newImages = array_diff($images, $existsImages);

        if ($newImages) {
            $rows= [];
            foreach ($newImages as $image) {
                $rows[] = [
                    'goods_id' => (int)$lastId,
                    'image_id' => (int)$image
                ];
            }

            $this->bulkInsert('goods_image', $rows);
        }

        if ($deleteImages) {
            $deleteImages = implode(',', $deleteImages);
            $sql = "
                delete goods_image
                where goods_id = $pk
                    and image_id in (:$deleteImages)
            ";
            $stmt = $this->fastQuery($sql);
        }
    }

    /*
     * 删除商品,修改商品状态为4
     * http://118.126.112.43:8080/index.php/product/delete
     * */
    public function delete()
    {
        $pk = (int)($_REQUEST['id'] ?? 0);

        $data = [
            'deleted_at' => time(),
            'pstatus' => 4,
        ];

        $allowFields = []; //允许外面传入的字段
        [$fields, $data] = $this->dataForUpdate($data, $allowFields);
        $openid = $_SESSION['openid'];
        try {
            $sql = "
            update goods
               set $fields
             where id = :id 
               and openid = :openid;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params = [
                'id' => $pk,
                'openid' => $openid,
            ];

            $effected = $this->fastUpdate($sql, $data, $params);
            if ($effected) {
                // 暂不删除图片

                return Response::json(true, 350, '商品删除成功', $pk);
            } else {
                return Response::error(false, 351, '商品删除失败', $pk);
            }
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }


    /*
     * 历史搜索记录
     * */
    public function search_history()
    {
        $openid = $_SESSION['openid'];
        $sql2 = "select keywords from search_history where openid='".$openid."' order by created_at desc";
        $param= [];
        $stmt = $this->fastQuery($sql2, $param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true, 350, '查询商品成功', $data);
    }
}
