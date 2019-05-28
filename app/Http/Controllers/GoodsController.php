<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoodsController extends Controller
{
   public function content()  //商品详情
   {
       $str = file_get_contents("php://input");

       echo $str;
//       echo  121312;
   }

   public function cart()  //添加购物车
   {
       $str1 = file_get_contents("php://input");
       $str = json_decode($str1,true);
//       echo $str;

       $where = [
           'goods_id' => $str['goods_id']
       ];
       $data2 = DB::table('goods')->where($where)->first();
       $data = DB::table('cart')->where($where)->first();
//       print_r($data);
       if ($data){
           $buy = $str['buy_number'] + $data->buy_number;
           $buy_num = [
               'buy_number' => $buy
           ];
//           print_r($buy);
           $arr = DB::table('cart')->where($where)->update($buy_num);
           $arr = ['status'=>0,'mag'=>'加入购物车成功'];
           json_encode($arr,JSON_UNESCAPED_UNICODE);
           return $arr;
       }else{

           $info = [
               'goods_id'             => $str['goods_id'],
               'user_id'              => $str['user_id'],
               'buy_number'           => $str['buy_number'],
               'goods_name'           => $data2->goods_name,
               'goods_selfprice'      => $data2->goods_selfprice,
               'create_time'          => $data2->create_time,
           ];
           $res = DB::table('cart')->insertGetId($info);
           if ($res){
               $arr = ['status'=>0,'mag'=>'加入购物车成功'];
               json_encode($arr,JSON_UNESCAPED_UNICODE);
               return $arr;
           }else{
               $arr = ['status'=>1,'mag'=>'加入购物车失败'];
               json_encode($arr,JSON_UNESCAPED_UNICODE);
               return $arr;
           }
       }
   }


   public function order()  //购物车展示
   {
       $str = file_get_contents("php://input");
       $order_str = json_decode($str,true);
//       print_r($order_str);
       $where = [
           'goods_id' => $order_str['goods_id']
       ];
       $data = DB::table('cart')->where($where)->first();
//       print_r($data);

       $order_amount = $data->buy_number * $data->goods_selfprice;  //总价
       $order_number = date("YmdHis",time()).rand(1000,9999);  //订单号
       $orderInfo = [
           'user_id'        =>$order_str['user_id'],
           'order_amount'   =>$order_amount,
           'order_number'   =>$order_number,
           'order_status'   =>1,
           'pay_status'     =>1,
           'pay_type'       =>1,
           'pay_time'       =>time(),
       ];
       $order_id = DB::table('order')->insertGetId($orderInfo);  //订单添加

       $cart_data = DB::table('cart')
           ->where('goods_id',$order_str['goods_id'])
           ->where('user_id',$order_str['user_id'])
           ->get();

       foreach ($cart_data as $v){
           $order_detail = [
               'user_id'=>$order_str['user_id'],
               'goods_id'=>$order_str['goods_id'],
               'order_number'=>$order_number,
               'order_id'=>$order_id,
               'buy_number'=>$v->buy_number,
               'goods_selfprice'=>$v->goods_selfprice,
               'goods_status'=>$v->goods_status,
               'goods_name'=>$v->goods_name,
           ];

           $arr = DB::table('order_detail')->insert($order_detail);
           if ($arr){
               $arr = ['status'=>0,'mag'=>'下单成功'];
               json_encode($arr,JSON_UNESCAPED_UNICODE);
               return $arr;
           }else{
               $arr = ['status'=>1,'mag'=>'下单失败'];
               json_encode($arr,JSON_UNESCAPED_UNICODE);
               return $arr;
           }
       }
//       print_r($cart_data);


   }


    public function order_detail()  //商品详情
    {
        $str = file_get_contents("php://input");

        echo $str;
//       echo  121312;
    }
}