<?php
/**
 * Created by imdupeng
 * Date: 2018/6/13
 * Time: 20:38
 */
namespace app\Myclass;
class Response
{
    /*
     * 按json方式输出数据
     * @param bool $status 成功提示；true成功，false失败
     * @param int $code 状态码；200成功
     * @param string $message 提示信息
     * @param array $data   数据
     *
     * */
    public static function json($status,$code,$message='',$data=[]){
        if (!is_bool($status) || !is_numeric($code)){
            return false;
        }
        $res = ['status'=>$status,'code'=>$code,'message'=>$message,'data'=>$data];


            echo json_encode($res);

        exit;
    }


    public static function error($code, $message='',$data=[]){
        $res = ['status'=>false,'code'=>$code,'message'=>$message,'data'=>$data];

        if(($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') == 'XMLHttpRequest'
            || ($_REQUEST['data_format'] ?? 'JSON') == 'JSON'
        ) {
            echo json_encode($res);
        } else {
            print_r($res);
        }
        exit;
    }


    public static function exception($code, Exception $exception){

        if (DEBUG) {
            $res = ['status'=>false,'code'=>$code,'message'=> $exception->getMessage(),'data'=>(string)$exception];
        } else {
            $res = ['status'=>false,'code'=>$code,'message'=> $exception->getMessage(),'data'=>''];
        }

        if(($SERVER['HTTP_X_REQUESTED_WITH'] ?? '') == 'XMLHttpRequest') {
            echo json_encode($res);
        } else {
            print_r($res);
        }
        exit;
    }
}