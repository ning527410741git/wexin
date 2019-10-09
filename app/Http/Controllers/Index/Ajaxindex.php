<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ajaxe;
use Cache;

class Ajaxindex extends Controller
{
    public function ajaxadd(Request $request){
    	return view('index.ajax.ajaxadd');
    }

    public function ajaxadd_do(Request $request){
    	$data=$request->except('_token');
    	$res=Ajaxe::create($data);

    	// dd($res);
    	if ($res) {
    		$arr=[
    			'error'=>1,
    			'msg'=>'添加成功'
    		];
    		return json_encode($arr);
    	}else{
    		$arr=[
    			'error'=>2,
    			'msg'=>'添加失败'
    		];
    		return json_encode($arr);
    	}
    }
    public function ajaxselect(Request $request){
    	$data=$request->all();
    	$res=Ajaxe::paginate(2);
    	return view('index.ajax.ajaxselect',['res'=>$res]);
    }

    public function ajaxdel(Request $request){
        $data=$request->all();
        $res=Ajaxe::where(['id'=>$data['id']])->delete();
        if ($res) {
           echo 1;
        }else{
           echo 2;
        }
    }

    public function wexin(){
      $key='wechat_access_token';
      if (Cache::has($key)) {
        // 取缓存
        $wechat_access_token=Cache::get($key);
        // dd($wechat_access_token);
      }else{
        // 取不到调接口 取缓存
        $re=file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxff842daa19066eae&secret=0a273b94c3b8b99f7db9e6402f9b3832');
        // dd($re);
        $resurl=json_decode($re,1);
        // dd($resurl);
        Cache::put($key,$resurl['access_token'],$resurl['expires_in']);
        $wechat_access_token=$resurl['access_token'];
      }

      return $wechat_access_token;
    }

    public function wexinlist(){
        $token=$this->wexin();
        $data=file_get_contents('https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$token.'&next_openid=');
        $user=json_decode($data,1);

        $dtinfo=[];
        foreach ($user['data']['openid'] as  $v) {
           $userInfo=file_get_contents('https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$v.'&lang=zh_CN');
           // dd($userInfo);
           $dt=json_decode($userInfo,1);
           $dtinfo[]=$dt;
        }
        // dd($dt);

        return view('index.ajax.wexinlist',['dtinfo'=>$dtinfo]);
    }

}
