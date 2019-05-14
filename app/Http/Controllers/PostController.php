<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

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

    public function aaa()
    {
//        header("Access-Control-Allow-Origin: http://clent.1809a.com");
        echo time();die;
    }
}