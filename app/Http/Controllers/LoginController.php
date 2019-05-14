<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class LoginController extends Controller
{
    public function log()
    {
        return view('login.login');
    }

    public function login(Request $request){
        $email = $request->input('email');
        $password = $request->input('password');

        $arr = DB::table('user')->where(['email'=>$email])->first();
        if ($arr){

            if (password_verify($password,$arr->password)){

                $token = $this->token($arr->id);
                $token_key = 'zzztoken:id' .$arr->id;
                Redis::set($token_key,$token);
                Redis::expire($token_key,604801);
            }

            $response = [
                'errno' => 0,
                'msg'   => 'ok',
                'data'  => [
                    'token' => $token
                ]
            ];

            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }else{
            $response = [
                'errno' =>  50005,
                'msg'   =>  '邮箱不正确',
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));

        }
    }

    //设置token值
    protected function token($id){
        $token = substr(sha1($id . time() . Str::random(10)),5,20);
        return $token;
    }
}