<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
   
    public function save()  //对称加密
    {
        $str = file_get_contents("php://input");
        echo '密文+base64:'.$str;

        echo "<hr/>";

        $arr = base64_decode($str);  //base64解开
        $method = 'AES-256-CBC';
        $key = '123456';
        $option = OPENSSL_RAW_DATA;
        $iv = '123456qazwsx9876';
        $pass = openssl_decrypt($arr,$method,$key,$option,$iv);  //解密
        echo '明文:'.$pass;
    }

    public function save2()  //非对称加密
    {
        $str = file_get_contents("php://input");
        echo '密文+base64:'.$str;

        $arr = base64_decode($str);  //base64解开
//        print_r($arr);
        $pk = openssl_get_publickey('file://'.storage_path('app/key/public.pem'));
        openssl_public_decrypt($arr,$dec_data,$pk);

        echo '<hr>';
        echo '明文:'.$dec_data;
    }

    public function save3()  //签名
    {
        echo '<pre>'; print_r($_GET);echo '</pre>';
        $str = file_get_contents("php://input");
        echo 'json:'.$str;

        $sign = $_GET['sign'];  //收到的签名
        $pk = openssl_get_publickey('file://'.storage_path('app/key/public.pem'));
        //开始验签
        $rs = openssl_verify($str,base64_decode($sign),$pk);
        var_dump($rs);
    }

    public function save4()  //测试
    {
        $str = file_get_contents("php://input");
//        echo '密文+base64:'.$str;

        $arr = base64_decode($str);  //base64解开
//        print_r($arr);
        $pk = openssl_get_publickey('file://'.storage_path('app/key/public.pem'));
        openssl_public_decrypt($arr,$dec_data,$pk);

        echo '<hr>';
        echo '明文:'.$dec_data;

        $post_json = json_decode($dec_data);
//        print_r($post_json);die;
        $email =  $post_json->email;
        $password = password_hash($post_json->password,PASSWORD_BCRYPT);
        $nickname= $post_json->nickname;
        $tel= $post_json->tel;
        $info = [
            'email'    => $email,
            'password' => $password,
            'nickname' => $nickname,
            'tel'      => $tel,
        ];
//        print_r($email);die;

        $email = DB::table('user')->where(['email'=>$email])->first();
//        print_r($email);die;
        if($email){
            $response = [
            'errno' =>  50003,
            'msg'   =>  '邮箱已存在',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }

        $arr = DB::table('user')->insertGetId($info);
        if ($arr){
            $response = [
                'errno' =>  50000,
                'msg'   =>  'ok',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }

    public function passreg()  //注册  接受APP的curl的值
    {
        $str = file_get_contents("php://input");
//        echo '密文+base64:'.$str;

        $arr = base64_decode($str);  //base64解开
//        print_r($arr);
        $pk = openssl_get_publickey('file://'.storage_path('app/key/public.pem'));
        openssl_public_decrypt($arr,$dec_data,$pk);
//        echo '<hr>';
//        echo '明文:'.$dec_data;

        $post_json = json_decode($dec_data);
        $nickname= $post_json->nickname;
        $password = password_hash($post_json->password,PASSWORD_BCRYPT);

        $info = [
            'password' => $password,
            'nickname' => $nickname,
        ];
        $email = DB::table('user')->where(['nickname'=>$nickname])->first();
        if($email){
            $arr = ['status'=>3,'msg'=>'用户已存在'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }

        $arr = DB::table('user')->insertGetId($info);
        if($arr){
            $arr = ['status'=>1,'msg'=>'注册成功'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }else{
            $arr = ['status'=>0,'msg'=>'注册失败'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }
    }

    public function passlog()  //登录  接受APP的curl的值
    {
        $str = file_get_contents("php://input");
//        echo '密文+base64:'.$str;

        $arr = base64_decode($str);  //base64解开
//        print_r($arr);
        $pk = openssl_get_publickey('file://'.storage_path('app/key/public.pem'));
        openssl_public_decrypt($arr,$dec_data,$pk);
//        echo '<hr>';
//        echo '明文:'.$dec_data;
        $post_json = json_decode($dec_data);

        $nickname= $post_json->nickname;
        $password= $post_json->password;
        $password= (password_hash($password,PASSWORD_BCRYPT));

        $arr = DB::table('user')->where(['nickname'=>$nickname])->first();
        if ($arr){

//            var_dump(password_verify($password,$res->password));die;
//            if (password_verify($password,$res->password)){
            $token = $this->token($arr->id);
            $token_key = 'APPtowtoken:id' .$arr->id;
            Redis::set($token_key,$token);
            Redis::expire($token_key,259250);
//            }

            $arr = ['status'=>1,'msg'=>'登录成功','token'=>$token,'id'=>$arr->id];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;

        }else{
            $arr = ['status'=>0,'msg'=>'登录失败'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;

        }
    }


    //app 注册
    public function aaa(Request $request)
    {
        $nickname =  $request->input('nickname');
        $password = password_hash($request->input('password'),PASSWORD_BCRYPT);
//        print_r($email);
//        print_r($password);die;
        $info = [
            'nickname'    => $nickname,
            'password'    => $password,
        ];
//        print_r($info);die;

        $nickname = DB::table('user')->where(['nickname'=>$nickname])->first();
//        print_r($email);die;
        if($nickname){
            $arr = ['status'=>3,'msg'=>'用户已存在'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }

        $arr = DB::table('user')->insertGetId($info);
        if($arr){
            $arr = ['status'=>1,'msg'=>'注册成功'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }else{
            $arr = ['status'=>0,'msg'=>'注册失败'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }
    }

    //app 登录
    public function bbb(Request $request){

//        header("Access-Control-Allow-Origin: *");
//        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
//        header('Access-Control-Allow-Headers:x-requested-with,content-type');

        $nickname = $request->input('nickname');
        $password = $request->input('password');

        $arr = DB::table('user')->where(['nickname'=>$nickname])->first();
//        print_r($arr);
        if ($arr){

            if (password_verify($password,$arr->password)){

                $token = $this->token($arr->id);
                $token_key = 'apptoken:id' .$arr->id;
                Redis::set($token_key,$token);
                Redis::expire($token_key,259210);
            }

            $arr = ['status'=>1,'msg'=>'登录成功','token'=>$token,'id'=>$arr->id];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;

        }else{
            $arr = ['status'=>0,'msg'=>'登录失败'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;

        }
    }

    //设置token值
    protected function token($id){
        $token = substr(sha1($id . time() . Str::random(10)),5,20);
        return $token;
    }

    //个人中心
    public function ccc(){

        $id = $_GET['id'];
        $token = $_GET['token'];

        $key = 'APPtowtoken:id'.$id;

        $token2 = Redis::get($key);
        if ($token == $token2){
            $arr = ['status'=>1,'msg'=>'进入成功'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;
        }else{
            $arr = ['status'=>0,'msg'=>'进入失败'];
            json_encode($arr,JSON_UNESCAPED_UNICODE);
            return $arr;

        }

    }

}