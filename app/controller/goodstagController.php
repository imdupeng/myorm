<?php
/**
 * Created by imdupeng
 * Date: 2018/6/15
 * Time: 11:45
 */

namespace app\controller;

use app\Myclass\Response;
use core\lib\config;

class goodstagController extends \core\myorm_core
{
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

    public function getTagName($tag_id){
        if (empty($tag_id)){
            return false;
        }
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare("select * from tag where id = :tag_id");
        $stmt->bindValue(':tag_id', $tag_id);
        $stmt->execute();
        $tag =$stmt->fetch(\PDO::FETCH_ASSOC);
        return $tag;
    }

    /*
     * 获取商品标签
     * @param string $goods_id  商品id
     * http://118.126.112.43:8080/index.php/goodstag/goodstaglist
     * */
    public function goodstaglist()
    {
        $goods_id = (string)($_REQUEST['goods_id'] ?? '');
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare("select id,tag_id from goods_tag where goods_id = :goods_id1");
        $stmt->bindValue(':goods_id1', $goods_id);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $k=>$val){
            $tag_id = $val['tag_id'];
            $result[$k]['tag'] = $this->getTagName($tag_id);
        }
        if ($result){
            return Response::json(true, 350, '查询商品tag成功', $result);
        }else{
            return Response::json(false, 351, '查询商品tag失败', []);
        }

    }

    /*
     * 获取标签
     * @param string $goods_id  商品id
     * http://118.126.112.43:8080/index.php/goodstag/taglist
     * */
    public function taglist()
    {
        $pdo = new \core\lib\model;
        $stmt = $pdo->prepare("select * from tag");
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if ($result){
            return Response::json(true, 350, '查询tag成功', $result);
        }else{
            return Response::json(false, 351, '查询tag失败', []);
        }

    }



    /*
     * 添加商品标签
     * @param string $goods_id  商品id
     * http://118.126.112.43:8080/index.php/goodstag/addtag
     * */
    public function addtag()
    {

        if (!empty($_REQUEST['goods_id']) && !empty($_REQUEST['tag_id'])){
            $goods_id = (string)($_REQUEST['goods_id'] ?? '');
            $tag_id = (string)($_REQUEST['tag_id'] ?? '');
            $pdo = new \core\lib\model;
            $stmt = $pdo->prepare("insert into goods_tag (goods_id,tag_id) values (:goods_id,:tag_id)");
            $stmt->bindValue(':goods_id', $goods_id);
            $stmt->bindValue(':tag_id', $tag_id);
            $effected = $stmt->execute();
            $addgoodstagId   = $effected ? $pdo->lastInsertId() : null;
            $data['addgoodstagId']=$addgoodstagId;
        }
        if (!empty($_REQUEST['tag_name']) && !empty($_REQUEST['tag_group'])){
            $tag_name = (string)($_REQUEST['tag_name'] ?? '');
            $tag_group = (string)($_REQUEST['tag_group'] ?? '');
            $pdo = new \core\lib\model;
            $stmt = $pdo->prepare("insert into tag (name,group) values (:tag_name,:tag_group)");
            $stmt->bindValue(':tag_name', $tag_name);
            $stmt->bindValue(':tag_group', $tag_group);
            $effected = $stmt->execute();
            $addtagId   = $effected ? $pdo->lastInsertId() : null;
            $data['addtagId']=$addtagId;
        }

        if ($addgoodstagId || $addtagId){
            return Response::json(true, 350, '添加tag成功', $data);
        }else{
            return Response::json(false, 351, '添加tag失败', []);
        }
    }



}
