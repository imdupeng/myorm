<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;

use app\Myclass\Response;
use core\lib\config;

class productController extends \core\myorm_core
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
     * 获取商品列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示商品数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/product/list
     * */
    public function list()
    {
        list($offset, $pageSize, $page, $data) = $this->pagination('productPagesize');

        $fields = implode(', ', [
            'goods.id',
            'goods.name',
            'goods.description',
            'goods.vendor_id',
            'goods.vendor_name',
            'goods.purchase_price',
            'goods.wholesale_price',
            'goods.retail_price'
        ]);
        $openid = $_SESSION['openid'];

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            list($filter1, $paramName, $search) = $this->fulltextSearch(['goods.name', 'goods.description'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;

            //添加搜索记录
            $sql3 = "insert into search_history (openid,keywords,created_at) values (?,?,?)";
            $param3= [
                1 => $openid,
                2 => $keywords,
                3 => time()
            ];
            $stmt = $this->fastQuery($sql3, $param3);
        }

        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';
        $sql2 = "
            select $fields, image.path as image from goods 
              left join goods_image on goods_image.goods_id = goods.id
              left join image on goods_image.image_id = image.id
             where pstatus=2
               $filterString
            group by goods.id
            order by orderby desc
            limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2, $param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true, 350, '查询商品成功', $data);
    }


    /*
     * 获取商品列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示商品数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/product/mylist
     * */
    public function mylist()
    {
        list($offset, $pageSize, $page, $data) = $this->pagination('productPagesize');

        $fields = implode(', ', [
            'goods.id',
            'goods.name',
            'goods.description',
            'goods.vendor_id',
            'goods.vendor_name',
            'goods.purchase_price',
            'goods.wholesale_price',
            'goods.retail_price'
        ]);
        $openid = $_SESSION['openid'];

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            list($filter1, $paramName, $search) = $this->fulltextSearch(['goods.name', 'goods.description'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;

            //添加搜索记录
            $sql3 = "insert into search_history (openid,keywords,created_at) values (?,?,?)";
            $param3= [
                1 => $openid,
                2 => $keywords,
                3 => time()
            ];
            $stmt = $this->fastQuery($sql3, $param3);
        }

        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';
        $sql2 = "
            select $fields, image.path as image from goods 
              left join goods_image on goods_image.goods_id = goods.id
              left join image on goods_image.image_id = image.id
             where openid='".$openid."' 
               and pstatus=2
               $filterString
            group by goods.id
            order by orderby desc
            limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2, $param);
        $data['list'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return Response::json(true, 350, '查询商品成功', $data);
    }

    /*
     * 获取商品详情
     * @param int $id 商品id
     * http://118.126.112.43:8080/index.php/product/view
     * */
    public function view()
    {
        $fields = implode(', ', [
            'goods.id',
            'goods.name',
            'goods.description',
            'goods.vendor_id',
            'goods.vendor_name',
            'goods.purchase_price',
            'goods.wholesale_price',
            'goods.retail_price'
        ]);

        $param  = [];
        $pk = (string)($_REQUEST['id'] ?? '');
        $openid = $_SESSION['openid'];
        $sql2 = "
            select $fields from goods
             where openid='".$openid."' 
               and pstatus=2
        ";
        
        $stmt = $this->fastQuery($sql2, $param);

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // 图片
        if ($data) {
            $data['images'] = $this->fetchImages($pk);
        }

        return Response::json(true, 350, '查询商品成功', $data);
    }

    /*
     * 获取商品图片
     * @param int $id 商品id
     * http://118.126.112.43:8080/index.php/product/images
     * */
    public function images()
    {
        $goods_id = (int)($_REQUEST['goods_id'] ?? 0);
        $data = $this->fetchImages($goods_id);

        return Response::json(true, 350, '查询图片成功', $data);
    }

    protected function fetchImages(int $goodsId)
    {
        $sql2 = "
        select image.id, image.path as url
         from image 
         left join goods_image on goods_image.image_id = image.id
        where goods_image.goods_id = :goods_id
        ";
        $param = ['goods_id' => $goodsId];
        
        $stmt = $this->fastQuery($sql2, $param);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    /*
     * 商品创建
     * */
    public function create()
    {
        $Rdata = (array)($_REQUEST ?? []);

        //允许外面传入的字段
        $allowFields = ['name','description','vendor_id','purchase_price','wholesale_price','retail_price','orderby'];
        
        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            'openid' => $openid,
            'pstatus' => 2,
            'last_bill_at' => null,
            'deleted_at' => null,
        ];

        list($fields, $values, $data) = $this->dataForCreate($Rdata, $allowFields, $fixed);

        try {
            $sql = "
            insert into goods ($fields) values ($values)
            ";
            list($effected, $lastId) = $this->fastInsert($sql, $data);

            // 处理图片
            if ($lastId && isset($data['images'])) {
                $this->createImageList($lastId, (array)$data['images']);
            }

            if ($effected) {
                return Response::json(true, 350, '商品创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }

    /*
     * 商品更新
     * */
    public function update()
    {
        $data = (array)($_REQUEST ?? []);
        $pk = (int)($_REQUEST['id'] ?? 0);
        $pdata = [];
        if (!empty($data['category_id'])) {
            $pdata['category_id'] = $data['category_id'];
        }
        if (!empty($data['name'])) {
            $pdata['name'] = $data['name'];
        }
        if (!empty($data['description'])) {
            $pdata['description'] = $data['description'];
        }
        if (!empty($data['vendor_id'])) {
            $pdata['vendor_id'] = $data['vendor_id'];
        }
        if (!empty($data['vendor_name'])) {
            $pdata['vendor_name'] = $data['vendor_name'];
        }
        if (!empty($data['purchase_price'])) {
            $pdata['purchase_price'] = $data['purchase_price'];
        }
        if (!empty($data['wholesale_price'])) {
            $pdata['wholesale_price'] = $data['wholesale_price'];
        }
        if (!empty($data['retail_price'])) {
            $pdata['retail_price'] = $data['retail_price'];
        }
        if (!empty($data['pstatus'])) {
            $pdata['pstatus'] = $data['pstatus'];
        }
        if (!empty($data['orderby'])) {
            $pdata['orderby'] = $data['orderby'];
        }

        $allowFields = ['category_id','name','description','vendor_id','vendor_name','purchase_price','wholesale_price','retail_price','pstatus','orderby']; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($pdata, $allowFields);
        $openid = $_SESSION['openid'];

        try {
            $sql = "
            update goods
               set $fields
             where id = :id 
               and openid = :openid;
            ";

            // 条件上的参数,注意不要与字段名重复
            $params1 = [
                'id' => $pk,
                'openid' => $openid,
            ];
            $params = array_merge($params1, $pdata);
            $effected = $this->fastUpdate($sql, $data, $params);

            // 处理图片
            if ($pk && isset($data['images']) && !empty($data['images'])) {
                $this->upgradeImageList($pk, $data['images']);
            }

            return Response::json(true, 350, '商品更新成功', $pk);
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
        list($fields, $data) = $this->dataForUpdate($data, $allowFields);
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
