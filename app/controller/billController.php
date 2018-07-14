<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;

use app\Myclass\Response;
use core\lib\config;

class billController extends \core\myorm_core
{
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

    //通过openid获取用户姓名
    public function getNameByOpenid($openid){
        if (empty($openid)){
            return false;
        }
        $sql = "select name from partner where openid='".$openid."'";
        $stmt = $this->fastQuery($sql);
        $name = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $name;
    }

    /*
     * 获取订单列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示订单数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/bill/list
     * */
    public function list()
    {
        list($offset, $pageSize, $page, $data) = $this->pagination('billPagesize');

        $fields = implode(', ', [
            'bill.id',
            'bill.order_no',
            'user.name',//bill.creator_open_id=user.open_id
            'bill.po_from_open_id',//待处理
            'bill.po_from_partner_id',//待处理
            'bill.sale_to_open_id',//待处理
            'bill.sale_to_partner_id',//待处理
            'bill.address_info_id',//处理合并地址
            'bill.sender_info_id',//处理合并发货人信息
            'bill.first_bill_id',
            'bill.last_bill_id',
            'bill.goods_id',
            'bill.goods_desc',
            'bill.goods_title',
            'bill.number',
            'bill.purchas_price',
            'bill.description',
            'bill.sale_price',
            'bill.creator_status',//创建人处理状态 1未发送 2已发送
            'bill.logistics_status',//物流状态 1未发运 2已发运
            'bill.logistics_number',
            'image.path',//已处理 bill.logistics_image_id = image.id
            'bill.receiver_status',
            'bill.year',
            'bill.created_at',
            'bill.send_time'
        ]);
        $openid = $_SESSION['openid'];
        if (!empty($_REQUEST['creator_status'])){
            $creator_status = 'and creator_status = '.$_REQUEST['creator_status'];
        }else{
            $creator_status = '';
        }
        if (!empty($_REQUEST['bill_type'])){
            $bill_type = 'and bill_type = '.$_REQUEST['bill_type'];
        }else{
            $bill_type = '';
        }

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            list($filter1, $paramName, $search) = $this->fulltextSearch(['goods.name', 'goods.description'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;

            //添加搜索记录
            $sql3 = "insert into search_history (openid,keywords,created_at) values ($openid,$keywords,time())";
            $param3= [];
            $stmt = $this->fastQuery($sql3, $param3);
        }

        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';
        $sql2 = "
            select $fields from bill 
              left join image on bill.logistics_image_id = image.id
              left join user on bill.creator_open_id=user.open_id
             where creator_open_id ='".$openid."' 
                $creator_status 
                $bill_type
               $filterString
            limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2, $param);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //添加供货商和买家名字
        foreach ($list as $kk=>$vv){
            $list[$kk]['salername'] = $this->getNameByOpenid($vv['po_from_open_id']);
            $list[$kk]['buyername'] = $this->getNameByOpenid($vv['sale_to_open_id']);
        }

        $data['list'] = $list;
        return Response::json(true, 350, '查询订单成功', $data);
    }


    /*
     * 获取代理商订单订单列表,分页
     * @param int $page 第几页
     * @param int $pagesize 每页展示订单数量
     * @param string $keywords  搜索的关键词
     * http://118.126.112.43:8080/index.php/bill/pobilllist
     * */
    public function pobilllist()
    {
        list($offset, $pageSize, $page, $data) = $this->pagination('billPagesize');

        $fields = implode(', ', [
            'bill.id',
            'bill.order_no',
            'user.name',//bill.creator_open_id=user.open_id
            'bill.po_from_open_id',//待处理
            'bill.po_from_partner_id',//待处理
            'bill.sale_to_open_id',//待处理
            'bill.sale_to_partner_id',//待处理
            'bill.address_info_id',//处理合并地址
            'bill.sender_info_id',//处理合并发货人信息
            'bill.first_bill_id',
            'bill.last_bill_id',
            'bill.goods_id',
            'bill.goods_desc',
            'bill.goods_title',
            'bill.number',
            'bill.purchas_price',
            'bill.description',
            'bill.sale_price',
            'bill.creator_status',//创建人处理状态 1未发送 2已发送
            'bill.logistics_status',//物流状态 1未发运 2已发运
            'bill.logistics_number',
            'image.path',//已处理 bill.logistics_image_id = image.id
            'bill.receiver_status',
            'bill.year',
            'bill.created_at',
            'bill.send_time'
        ]);
        $openid = $_SESSION['openid'];
        if (!empty($_REQUEST['creator_status'])){
            $creator_status = 'and creator_status = '.$_REQUEST['creator_status'];
        }else{
            $creator_status = '';
        }
        if (!empty($_REQUEST['bill_type'])){
            $bill_type = 'and bill_type = '.$_REQUEST['bill_type'];
        }else{
            $bill_type = '';
        }

        $filters = [];
        $param  = [];
        $keywords = (string)($_REQUEST['keywords'] ?? '');
        if ($keywords) {
            list($filter1, $paramName, $search) = $this->fulltextSearch(['goods.name', 'goods.description'], $keywords, 'keywords');
            $filters[] = $filter1;
            $param[$paramName] = $search;

            //添加搜索记录
            $sql3 = "insert into search_history (openid,keywords,created_at) values ($openid,$keywords,time())";
            $param3= [];
            $stmt = $this->fastQuery($sql3, $param3);
        }

        $filterString = $filters ? 'and ' . implode(' AND ', $filters) : '';
        $sql2 = "
            select $fields from bill 
              left join image on bill.logistics_image_id = image.id
              left join user on bill.creator_open_id=user.open_id
             where po_from_open_id ='".$openid."' 
                $creator_status 
                $bill_type
               $filterString
            limit $offset, $pageSize
        ";
        $stmt = $this->fastQuery($sql2, $param);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($list as $kk=>$vv){
            $list[$kk]['salername'] = $this->getNameByOpenid($vv['po_from_open_id']);
            $list[$kk]['buyername'] = $this->getNameByOpenid($vv['sale_to_open_id']);
        }
        $data['list'] = $list;

        return Response::json(true, 350, '查询订单成功', $data);
    }

    /*
     * 获取订单详情
     * @param int $id 订单id
     * http://118.126.112.43:8080/index.php/bill/view
     * */
    public function view()
    {
        if (empty($_REQUEST['order_no'])){
            return Response::json(false, 359, '缺少订单id！', []);
        }else{
            $order_no = $_REQUEST['order_no'];
        }
        $fields = implode(', ', [
            'bill.id',
            'bill.order_no',
            'user.name',//bill.creator_open_id=user.open_id
            'bill.po_from_open_id',//待处理
            'bill.po_from_partner_id',//待处理
            'bill.sale_to_open_id',//待处理
            'bill.sale_to_partner_id',//待处理
            'bill.address_info_id',//处理合并地址
            'bill.sender_info_id',//处理合并发货人信息
            'bill.first_bill_id',
            'bill.last_bill_id',
            'bill.goods_id',
            'bill.goods_desc',
            'bill.goods_title',
            'bill.number',
            'bill.purchas_price',
            'bill.description',
            'bill.sale_price',
            'bill.creator_status',//创建人处理状态 1未发送 2已发送
            'bill.logistics_status',//物流状态 1未发运 2已发运
            'bill.logistics_number',
            'image.path',//已处理 bill.logistics_image_id = image.id
            'bill.receiver_status',
            'bill.year',
            'bill.created_at',
            'bill.send_time'
        ]);

        $param  = [];
        $openid = $_SESSION['openid'];
        $sql2 = "
            select $fields from bill 
              left join image on bill.logistics_image_id = image.id
              left join user on bill.creator_open_id=user.open_id
             where creator_open_id='".$openid."' 
               and order_no=".$order_no;
        
        $stmt = $this->fastQuery($sql2, $param);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        return Response::json(true, 350, '查询订单成功', $data);
    }

    /*
     * 获取订单图片
     * @param int $id 订单id
     * http://118.126.112.43:8080/index.php/bill/images
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

    //获取商品图片
    public function getImageidByGoodsid($goods_id){
        if (empty($goods_id)){
            return false;
        }
        $sql = "select path from goods_image left join image on goods_image.image_id=image.id where goods_id=$goods_id";
        $stmt = $this->fastQuery($sql);
        $data = $stmt->fetchALL(\PDO::FETCH_ASSOC);
        return $data[0];
    }



    /*
     * 订单创建
     * http://118.126.112.43:8080/index.php/bill/create
     * isSave:1选中0未选中
        save_name、save_phone、save_address
        sender_name、sender_phone
     * */
    public function create()
    {
        $Rdata = (array)($_REQUEST ?? []);
//        $Rdata['goods_image'] = $this->getImageidByGoodsid($Rdata['goods_id']);

        $Rdata['isSave'];//是否勾选保存地址
        if ($Rdata['isSave'] && $Rdata['sale_to_open_id']){//保存地址
            $custormID = $Rdata['sale_to_open_id'];
            $sql23 = "select * from address where partner_id='".$custormID."'";
            $stmt23 = $this->fastQuery($sql23);
            $Cdata = $stmt23->fetchALL(\PDO::FETCH_ASSOC);
            if ($Cdata != null && $Cdata != ''){//客户地址存在，update更新地址
                $sql1 = "update address set name='".$Rdata['save_name']."',phone='".$Rdata['save_phone']."',address='".$Rdata['save_address']."' where partner_id='".$custormID."'";
                $stmt1 = $this->fastQuery($sql1);
//                $Cdata = $stmt1->fetchALL(\PDO::FETCH_ASSOC);
                $address_info_id = $Cdata[0]['id'];
            }else{//客户地址不存在，insert
                $pdo = new \core\lib\model;
                $stmt2 = $pdo->prepare("insert into address (name,phone,address,partner_id,status) values(:name,:phone,:address,:partner_id,:status)");
                $stmt2->bindValue(':name', $Rdata['save_name']);
                $stmt2->bindValue(':phone', $Rdata['save_phone']);
                $stmt2->bindValue(':address', $Rdata['save_address']);
                $stmt2->bindValue(':partner_id', $custormID);
                $stmt2->bindValue(':status', 2);
                $effected = $stmt2->execute();
                $address_info_id   = $effected ? $pdo->lastInsertId() : null;
            }
        }


        //允许外面传入的字段
        $allowFields = ['po_from_partner_id','po_from_open_id','sale_to_open_id','sale_to_partner_id','address_info_id','sender_info_id','first_bill_id',
            'last_bill_id','goods_id','goods_name','goods_desc','goods_title','number','purchas_price','description',
            'sale_price','creator_status','logistics_status','logistics_number','logistics_image_id','receiver_status','year',
            'send_time','goods_image','bill_type'
            ];
        $thetime = time();
        $no1= date('ym',$thetime);
        // 固定值, 补充或覆盖到 $data 中
        $openid = $_SESSION['openid'];
        $fixed = [
            'creator_open_id' => $openid,
            'created_at'=>$thetime,
        ];

        list($fields, $values, $data) = $this->dataForCreate($Rdata, $allowFields, $fixed);

        try {
            $sql = "
            insert into bill ($fields) values ($values)
            ";
            list($effected, $lastId) = $this->fastInsert($sql, $data);

            // 处理图片
//            if ($lastId && isset($data['images'])) {
//                $this->createImageList($lastId, (array)$data['images']);
//            }

            if ($effected) {

                //判断id长度
                $idlenth = strlen($lastId);
                if ($idlenth>=8){
                    $real_order_no = $no1.$lastId;
                }else{
                    $temp_num = 100000000;
                    $new_num = $lastId + $temp_num;
                    $real_order_no = $no1.substr($new_num,1,8); //即截取掉最前面的“1”
                }
                $sql4 = "update bill set order_no = ? where id = ?";
                $pdo2 = new \core\lib\model;
                $stmt2 = $pdo2->prepare($sql4);
                $stmt2->bindValue(1, $real_order_no);
                $stmt2->bindValue(2, $lastId);
                $effected = $stmt2->execute();


                return Response::json(true, 350, '订单创建成功', $lastId);
            } else {
                return Response::json(false, 351, '未知错误', 0);
            }
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }

    /*
     * 订单更新
     * http://118.126.112.43:8080/index.php/bill/update
     * */
    public function update()
    {

        if (empty($_REQUEST['order_no'])){
            return Response::json(false, 359, '订单编号不正确', []);
        }
        $data = (array)($_REQUEST ?? []);
        $Order_no = $_REQUEST['order_no'];

        $pdata = [];
        if (!empty($data['po_from_open_id'])){
            $pdata['po_from_open_id'] = $data['po_from_open_id'];
        }
        if (!empty($data['po_from_partner_id'])){
            $pdata['po_from_partner_id'] = $data['po_from_partner_id'];
        }
        if (!empty($data['address_info_id'])){
            $pdata['address_info_id'] = $data['address_info_id'];
        }
        if (!empty($data['sender_info_id'])){
            $pdata['sender_info_id'] = $data['sender_info_id'];
        }
        if (!empty($data['first_bill_id'])){
            $pdata['first_bill_id'] = $data['first_bill_id'];
        }
        if (!empty($data['last_bill_id'])){
            $pdata['last_bill_id'] = $data['last_bill_id'];
        }
        if (!empty($data['description'])){
            $pdata['description'] = $data['description'];
        }
        if (!empty($data['creator_status'])){
            $pdata['creator_status'] = $data['creator_status'];
        }
        if (!empty($data['logistics_status'])){
            $pdata['logistics_status'] = $data['logistics_status'];
        }
        if (!empty($data['logistics_number'])){
            $pdata['logistics_number'] = $data['logistics_number'];
        }
        if (!empty($data['logistics_image_id'])){
            $pdata['logistics_image_id'] = $data['logistics_image_id'];
        }
        if (!empty($data['receiver_status'])){
            $pdata['receiver_status'] = $data['receiver_status'];
        }
        if (!empty($data['send_time'])){
            $pdata['send_time'] = $data['send_time'];
        }

        $allowFields = ['po_from_open_id','po_from_partner_id','address_info_id','sender_info_id','first_bill_id',
            'last_bill_id','description',
            'creator_status','logistics_status','logistics_number','logistics_image_id','receiver_status','send_time']; //允许外面传入的字段
        list($fields, $data) = $this->dataForUpdate($pdata, $allowFields);
        $Openid = $_SESSION['openid'];

        try {
            $sql = "
            update bill
               set $fields
             where order_no = '".$Order_no."'
               and creator_open_id = '".$Openid."' 
            ";

            // 条件上的参数,注意不要与字段名重复
            $params1 = [
            ];
            $params = array_merge($params1,$pdata);
            $effected = $this->fastUpdate($sql, $data, $params);

//            // 处理图片
////            if ($pk && isset($data['images']) && !empty($data['images'])) {
////                $this->upgradeImageList($pk, $data['images']);
////            }

            return Response::json(true, 350, '订单更新成功', $Order_no);
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
     * 删除订单,修改订单状态为4
     * http://118.126.112.43:8080/index.php/bill/delete
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

                return Response::json(true, 350, '订单删除成功', $pk);
            } else {
                return Response::json(false, 351, '订单删除失败', $pk);
            }
        } catch (Exception $e) {
            return Response::exception(351, $e);
        }
    }


    /*
     * 分享给厂商的订单
     * 如果订单中已经有伙伴id，那么通过伙伴id到partner表中查找该条伙伴id数据中是否存在openid。存在则直接
     * 取用openid来保存到订单，不存在则保存访问此地址的用户openid到partner中。
     * 1、先判断bill表中po_from_open_id是否存在，存在则不更新，不存在才更新bill表；
     * 2、po_from_open_id不存在时，判断访问人的openid是否等于订单的creator_open_id，等于则不保存，不等于才保存；
     * 3、确保po_from_partner_id存在。并判断partner中此供应商数据是否存在partner_openid。存在则取用，不存在则判断当前登录用户是否订单所有者openid，
     * 是则不处理，不是则保存当前登录openid到订单的po_from_open_id和partner的partner_openid
     *
     * http://118.126.112.43:8080/index.php?r=bill/shareOrderToPrivider
     * */
    public function shareOrderToPrivider(){
        $openid = $_SESSION['openid'];
        $orderno = $_REQUEST['order_no'];
        if (empty($orderno)){
            return Response::json(false, 350, '没有找到该订单', []);
        }
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare('select * from bill where order_no = :orderno');
        $stmt->bindValue(':orderno', $orderno);
        $stmt->execute();
        $bill_data = $stmt->fetch(\PDO::FETCH_ASSOC);//获取订单数据
        if (empty($bill_data['po_from_partner_id'])){
            return Response::json(false, 451, '此订单未选择伙伴', []);
        }
        if (!empty($bill_data['po_from_open_id'])){//供货人po_from_open_id不为空，那么已经分配了供货人,直接返回订单信息
            return Response::json(true, 350, '供货人已分配过了', $bill_data);
        }else{//供货人po_from_open_id为空，那么添加当前访问用户为供货人
            //通过po_from_partner_id到partner表查找partner_openid是否存在
            //partner_openid存在，说明已经添加过此partner
            //partner_openid不存在，说明还未添加此partner，那么保存当前登录用户的openid为partner_openid
            $stmt2 = $pdo->prepare('select * from partner where id = :po_from_partner_id');
            $stmt2->bindValue(':po_from_partner_id', $bill_data['po_from_open_id']);
            $stmt2->execute();
            $partner_data = $stmt2->fetch(\PDO::FETCH_ASSOC);//获取伙伴数据
            if (empty($partner_data['partner_openid'])) {//伙伴第一次登录，保存伙伴openid到partner_openid和订单的po_from_open_id
                $stmt3 = $pdo->prepare('update partner set partner_openid=:partner_openid where id=:po_from_partner_id');
                $stmt3->bindValue(':partner_openid', $openid);
                $stmt3->bindValue(':po_from_partner_id', $bill_data['po_from_partner_id']);

                $update_partner = $stmt3->execute();
                if ($update_partner == false) {
                    return Response::json(false, 452, '更新伙伴数据失败！', []);
                }
            }
            //保存伙伴id到订单的po_from_open_id
                $stmt4 = $pdo->prepare('update bill set po_from_open_id=:po_from_open_id where order_no=:orderno');
                $stmt4->bindValue(':orderno', $orderno);
                $stmt4->bindValue(':po_from_open_id', $openid);
                $update_bill = $stmt4->execute();
                if ($update_bill == false){
                    return Response::json(false, 453, '更新订单数据失败！', []);
                }
                return Response::json(true, 351, '成功！已经把此订单分配给你', $bill_data);
        }


    }

}
