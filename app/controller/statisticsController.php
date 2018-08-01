<?php
/**
 * Created by imdupeng
 * Date: 2018/7/21
 * Time: 11:36
 */
namespace app\controller;

use app\Myclass\Response;
use core\lib\config;

class statisticsController extends \core\myorm_core
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

    public function culcuTime($type = 1){
        if ($type == 10){
            //获取最近7天起始时间戳和结束时间戳
            $theTime['starttime']= strtotime("-7 day", mktime(0,0,0,date('m'),date('d'),date('Y')));
            $theTime['endtime']= now();
        }
        if ($type == 11){
            //获取最近30天起始时间戳和结束时间戳
            $theTime['starttime']= strtotime("-30 day", mktime(0,0,0,date('m'),date('d'),date('Y')));
            $theTime['endtime']= now();
        }
        if ($type == 8){
            //获取今日起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $theTime['endtime']=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        }

        if ($type == 9){
            //获取昨日起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
            $theTime['endtime']=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        }

        if ($type == 1){
            //获取本周起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
            $theTime['endtime']=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        }
        if ($type == 2){
            //获取上周起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
            $theTime['endtime']=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        }
        if ($type == 3){
            //获取本月起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m'),1,date('Y'));
            $theTime['endtime']=mktime(23,59,59,date('m'),date('t'),date('Y'));
        }
        if ($type == 4){
            //获取上月起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,date('m')-1,1,date('Y'));
            $theTime['endtime']=mktime(0,0,0,date('m'),1,date('Y'))-1;
        }
        if($type == 5){
            //本季度开始和结束时间
            $season = ceil((date('n'))/3);//当月是第几季度
            $theTime['starttime'] = mktime(0, 0, 0,$season*3-3+1,1,date('Y'));
            $theTime['endtime'] = mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'));
        }
        if ($type == 6){
            //获取本年起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,1,1,date('Y'));
            $theTime['endtime']=mktime(0,0,0,1,1,date('Y')+1)-1;
        }
        if ($type == 7){
            //获取去年起始时间戳和结束时间戳
            $theTime['starttime']=mktime(0,0,0,1,1,date('Y')-1);
            $theTime['endtime']=mktime(0,0,0,1,1,date('Y'))-1;
        }
        return $theTime;

    }

    /*
     * 回头客：客户总订单数排名
     * //本周1、上周2、本月3、上月4、季度5、年度6、去年7
     * http://118.126.112.43:8080/index.php?r=statistics/buyer_OrderNumRank
     * */
    public function buyer_OrderNumRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,count(sale_to_open_id) as ordernum from bill left join partner on bill.sale_to_open_id=partner.partner_openid where bill_type=3 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by ordernum desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户总订单数排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }

    }

    /*
     * 回头客：客户总订额排名
     * */
    public function buyer_OrderAmountRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,sum(sale_price) as orderamount from bill left join partner on bill.sale_to_open_id=partner.partner_openid  where bill_type=3 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户总订额排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 回头客：客户平均单价排名
     * */
    public function buyer_EverageAmountRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,avg(sale_price) as orderamount from bill 
        left join partner on bill.sale_to_open_id=partner.partner_openid  where bill_type=3 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' group by sale_to_open_id order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户平均单价排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 回头客：客户利润排名
     * */
    public function buyer_ProfitRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,sum(sale_price-purchas_price) as orderamount from bill 
        left join partner on bill.sale_to_open_id=partner.partner_openid where bill_type=3 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' group by sale_to_open_id order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户利润排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 代理商：订单量排名
     * */
    public function Agent_OrderNumRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,count(sale_to_open_id) as ordernum from bill left join partner on bill.sale_to_open_id=partner.partner_openid where bill_type=2 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by ordernum desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户总订单数排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }

    }

    /*
     * 代理商：订单总额排名
     * */
    public function Agent_OrderAmountRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,sum(sale_price) as orderamount from bill left join partner on bill.sale_to_open_id=partner.partner_openid  where bill_type=2 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户总订额排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 代理商：平均单价排名
     * */
    public function Agent_EverageAmountRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,avg(sale_price) as orderamount from bill 
        left join partner on bill.sale_to_open_id=partner.partner_openid  where bill_type=2 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' group by sale_to_open_id order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户平均单价排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 代理商：利润排名
     * */
    public function Agent_ProfitRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select partner.name,sum(sale_price-purchas_price) as orderamount from bill 
        left join partner on bill.sale_to_open_id=partner.partner_openid where bill_type=2 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' group by sale_to_open_id order by orderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功客户利润排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 商品：销量排名
     * */
    public function Goods_SaleNumRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select goods.name,sum(bill.goods_id) as goodNum from bill 
        left join goods on bill.goods_id=goods.id where created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by goodNum desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功商品销量排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }

    /*
     * 商品：利润率排名
     * */
    public function Goods_ProfitRank($num=10,$type=1){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        $sql = "select goods.name,sum(sale_price-purchas_price) as goodsorderamount from bill 
        left join goods on bill.goods_id=goods.id where created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."' order by goodsorderamount desc limit ".$num;
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if ($data){
            return Response::json(true, '350', '成功商品利润排名！', $data);
        }else{
            return Response::json(false, '351', '无数据或获取数据失败！', []);
        }
    }


    /*
     * 商品：客单价分布。。取20份
     * */
    public function orderPriceDistribution(){


    }

    /*
     * 走势图：成交量、成交额、利润//最近30天
     * */
    public function orderMap(){
        $pdo = new \core\lib\model;
        $days = 30;
        $i = 0;
        //获取今日起始时间戳和结束时间戳
        $theTime['starttime']=mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
        $theTime['endtime']=mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        for($i=0;$i<$days;$i++){
            $theTime[$i]['starttime']=mktime(0,0,0,date('m'),date('d'),date('Y'))-($i+1)*24*60*60;
            $theTime[$i]['endtime']=mktime(23,59,59,date('m'),date('d'),date('Y'))-($i+1)*24*60*60;
            //成交量
            $sql1 = "select count(id) as num from bill where created_at > '".$theTime[$i]['starttime']."' and created_at<".$theTime[$i]['endtime'];
            $stmt1 = $pdo->query($sql1);
            $data1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
            $theTime[$i]['num'] = $data1[0]['num'];
            //成交额
            $sql1 = "select sum(sale_price) as amount from bill where created_at > '".$theTime[$i]['starttime']."' and created_at<".$theTime[$i]['endtime'];
            $stmt1 = $pdo->query($sql1);
            $data1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
            $theTime[$i]['amount'] = $data1[0]['amount'];

            //利润
            $sql1 = "select sum(sale_price-purchas_price) as profit from bill where created_at > '".$theTime[$i]['starttime']."' and created_at<".$theTime[$i]['endtime'];
            $stmt1 = $pdo->query($sql1);
            $data1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
            $theTime[$i]['profit'] = $data1[0]['profit'];
        }
        return Response::json(true, '350', '获取走势图数据成功！', $theTime);

    }

    /*
     * 成交额\成交量：批发、零售、利润
     * */
    public function total_amount($type=8){
        $theTime = $this->culcuTime($type);
        $pdo = new \core\lib\model;
        //成交额
        //批发,type=2
        $sql1 = "select count(*) as orderNum,sum(sale_price) as orderamount,sum(sale_price-purchas_price) as profit from bill where bill_type=2 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."'";
        $stmt1 = $pdo->query($sql1);
        $data1 = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
        $data['amount_pifa'] = $data1[0]['orderamount'];
        $data['Num_pifa'] = $data1[0]['orderNum'];
        $data['profit_pifa'] = $data1[0]['profit'];

        //成交额
        //零售,type=2
        $sql2 = "select count(*) as orderNum,sum(sale_price) as orderamount,sum(sale_price-purchas_price) as profit from bill where bill_type=3 and created_at > '".$theTime['starttime']."' and created_at<'".$theTime['starttime']."'";
        $stmt2 = $pdo->query($sql2);
        $data2 = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        $data['amount_linshou'] = $data2[0]['orderamount'];
        $data['Num_linshou'] = $data2[0]['orderNum'];
        $data['profit_linshou'] = $data2[0]['profit'];
        //利润
        $data['profit'] = $data['profit_pifa'] + $data['profit_linshou'];
        //合计
        $data['total'] = $data['Num_pifa'] + $data['Num_linshou'];
        return Response::json(true, '350', '获取数据成功！', $data);
    }


}