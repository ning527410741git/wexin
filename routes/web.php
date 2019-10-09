<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// 文章
// Route::group(['middleware'=>['checklogin']],function(){
	Route::any('/index',"Index\IndexController@index");
	Route::any('/add','Index\IndexController@add');
	Route::any('/update','Index\IndexController@update');
	Route::any('/update_do','Index\IndexController@update_do');
	Route::any('/del','Index\IndexController@del');
	Route::any('/details','Index\IndexController@details');
	Route::any('/dels','Index\IndexController@dels');
	// 学生
	Route::any('/select',"Index\SubdentsController@select");
	Route::any('/inse',"Index\SubdentsController@inse");
	Route::any('/updates',"Index\SubdentsController@updates");
	Route::any('/update_dos',"Index\SubdentsController@update_dos");
	Route::any('/delete',"Index\SubdentsController@delete");
	Route::any('/page',"Index\SubdentsController@page");

	// ajax
	Route::any('/ajaxadd',"Index\Ajaxindex@ajaxadd");
	Route::any('/ajaxadd_do',"Index\Ajaxindex@ajaxadd_do");
	Route::any('/ajaxselect',"Index\Ajaxindex@ajaxselect");
	Route::any('/ajaxdel',"Index\Ajaxindex@ajaxdel");
	// 测试
	Route::any('/addstre',"Index\Ajaxindex@addstre");
	Route::any('/wexinlist',"Index\Ajaxindex@wexinlist");


	// 注册
	Route::any('/redse',"Index\LoginController@redse");
	Route::any('/redse_do',"Index\LoginController@redse_do");
	// 登陆
	Route::any('/loginse',"Index\LoginController@loginse");
	Route::any('/loginse_do',"Index\LoginController@loginse_do");
	// 首页
	Route::any('/indexse',"Index\IndexseController@indexse");
	Route::any('/indexse_do',"Index\IndexseController@indexse_do");
	// 查询
	Route::any('/indexseadd',"Index\IndexseController@indexseadd");
	// 删除
	Route::any('/indexsedel',"Index\IndexseController@indexsedel");
	// 修改
	Route::any('/indexseupdata',"Index\IndexseController@indexseupdata");
	Route::any('/indexseupdata_do',"Index\IndexseController@indexseupdata_do");
	
// });

// ========电商=============
// 注册
Route::any('/reds',"Admin\AdminUserController@reds");
Route::any('/redsadd',"Admin\AdminUserController@redsadd");
Route::any('/weiyi',"Index\LoginController@weiyi");

// 登录
Route::any('/logins',"Admin\AdminUserController@logins");
Route::any('/loginsadd',"Admin\AdminUserController@loginsadd");
//首页
Route::any('/admngindex',"Admin\AdminIndexController@adminindex");
// 商品详情
Route::any('/admingoods',"Admin\AdminIndexController@admingoods");

// 加入购物车
// 购物车展示
Route::any('/cartinfo',"Admin\AdminCartController@cartinfo");
// 添加
Route::any('/cartadd',"Admin\AdminCartController@cartadd");

// 确认订单
Route::any('/orderinfo',"Admin\OrderInfoController@orderinfo");
// 收货地址
Route::any('/addressedit',"Admin\AddresseditController@addressedit");


Auth::routes();

Route::get('/logout','Index\UserController@logout')->name('logout');
Route::get('/send','Index\UserController@send');
Route::get('/home', 'HomeController@index')->name('home');

// 支付宝支付
Route::any('/alipay',"Index\AlipayController@aindex");
Route::any('/alipay/do',"Index\AlipayController@do");
Route::any('/return',"Index\AlipayController@return");


// 考试
// 登陆
// Route::group(['middleware'=>['checklogin']],function(){
Route::any('/',"Kaoshi\KaoshiLoginController@loginsert");
Route::any('/loginsert_do',"Kaoshi\KaoshiLoginController@loginsert_do");
// 展示
Route::any('/orderse',"Kaoshi\KaoshiOrderController@orderse");
// 详情
Route::any('/detailse',"Kaoshi\KaoshiDetailController@detailse");
// });

//增删改查
// 添加
Route::any('/zsgcadd',"Index\Zsgc@zsgcadd");
// 执行添加
Route::any('/zsgcadd_do',"Index\Zsgc@zsgcadd_do");
//唯一性
Route::any('/weiyixing',"Index\Zsgc@weiyixing");
// 展示
Route::any('/zsgcselect',"Index\Zsgc@zsgcselect");
//删除
Route::any('/daleteso',"Index\Zsgc@daleteso");
// 修改
Route::any('/updateso',"Index\Zsgc@updateso");
// 执行修改
Route::any('/updateso_do',"Index\Zsgc@updateso_do");
