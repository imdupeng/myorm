<?php
/**
 * Created by imdupeng.cn
 * Date: 2018/6/6
 * Time: 0:16
 */

//请求数据带上请求第几页$page，和每页展示数量$page_size
$res = array(
    'status' => true,
    'message' => 'ok',
    'total_num' => '500',//总条数
    'page' => '1',//请求第几页
    'page_size' => '2',//页展示数量

    'data' => array(
        'goods1'=> array(
            'name'=>'纯棉大衣',
            'description'=>'纯棉大衣',
            'vendor_id'=>'3',
            'vendor_name'=>'高新百货'
        ),
        'goods2'=> array(
            'name'=>'纯棉大衣2',
            'description'=>'纯棉大衣2',
            'vendor_id'=>'3',
            'vendor_name'=>'高新百货'
        )
    )
);

echo json_encode($res);