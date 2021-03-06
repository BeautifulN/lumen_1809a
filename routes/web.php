<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('save', 'PostController@save');
$router->post('save2', 'PostController@save2');
$router->post('save3', 'PostController@save3');
$router->post('save4', 'PostController@save4');

$router->post('aaa', 'PostController@aaa');  //APP 注册
$router->post('bbb', 'PostController@bbb');  //APP 登录
//$router->get('ccc', ['middleware' => 'AccessToken', function () {
//    'PostController@ccc';
//}]);
$router->post('ccc', 'PostController@ccc');

$router->get('log', 'LoginController@log');
$router->post('login', 'LoginController@login');

//app
$router->post('passreg', 'PostController@passreg');  //app的curl注册
$router->post('passlog', 'PostController@passlog');  //app的curl登录
$router->post('content', 'GoodsController@content');  //app的curl商品详情
$router->post('cart', 'GoodsController@cart');  //app的curl添加购物车
$router->post('cartlist', 'GoodsController@cartlist');  //app的curl购物车展示
$router->post('order', 'GoodsController@order');  //app的curl订单生成
$router->post('order_detail', 'GoodsController@order_detail');  //app的curl订单展示




