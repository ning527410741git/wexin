<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ajaxe;
use App\Models\Userwechat;
use App\Models\Indexuser;
use App\Tools\Tools;
use Cache;

class Ajaxindex extends Controller
{
    public $tools;
    public function __construct(Tools $tools)
    {
           $this->tools = $tools;
    }

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






    // 微信测试
// 获取access_token授权
    public function wexin(){
      $key='wechat_access_token';
      if (Cache::has($key)) {
        // 取缓存
        $wechat_access_token=Cache::get($key);
        // dd($wechat_access_token);
      }else{
        // 取不到 调接口 取缓存
        $re=file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxff842daa19066eae&secret=0a273b94c3b8b99f7db9e6402f9b3832');
        // dd($re);
        $resurl=json_decode($re,1);
        // dd($resurl);
        Cache::put($key,$resurl['access_token'],$resurl['expires_in']);
        $wechat_access_token=$resurl['access_token'];
      }

      return $wechat_access_token;
    }


    // 调用接口获得的微信详情页
    public function wexinlist(Request $request){
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

        return view('index.ajax.wexinlist',['dtinfo'=>$dtinfo,'tag_id'=>$request->input('tag_id')]);
    }


        // 查看用户标签
    public function user_tag(Request $request){
      $req=$request->all();
      // dd($req);
      $url="https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=".$this->tools->get_access_token();
      $data=[
            'openid'=>$req['openid']
        ];
      $re=$this->tools->curl_post($url,json_encode($data,JSON_UNESCAPED_UNICODE));
      $resuel=json_decode($re,1);
      $tagList=$this->tools->tagList()['tags'];
      foreach ($resuel['tagid_list'] as $v) {
        foreach ($tagList as $vo) {
          if ($v==$vo['id']) {
            echo $vo['name']."<br>";
          }
        }
      }
      // dd($resuel);
  }

        
    // 网路授权
    public function author(){
        $appid='wxff842daa19066eae';
        $redirect_uri=urlencode(env('APP_URL').'/getUserInfo');
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        // dd($url);
        header("Location:".$url);
    }

    public function getUserInfo(Request $request){
       $data=$request->all();
       // dd($data);
       $appid='wxff842daa19066eae';
       $secret="0a273b94c3b8b99f7db9e6402f9b3832";
       $code=$_GET["code"];
       $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$secret&code=$code&grant_type=authorization_code";
       $res=file_get_contents($url);
       // dd($res);
       $resutl=json_decode($res,1);
       // dd($resutl);
       // print_r($resutl);
       $user_wechat=Userwechat::where(['openid'=>$resutl['openid']])->first();
       // dd($user_wechat);
       if (!empty($user_wechat)) {
        // 登陆
           $request->session()->put('user_id',$user_wechat->user_id);
           echo 'ok';
       }else{
             // 注册
             \DB::connection('mysql')->beginTransaction(); //开启事务
             $user_id=Indexuser::insertGetId([
                  'user_name'=>rand(1000,9999).time(),
                    'user_pwd'=>'',
                    'reg_time'=>time()
                ]);
             $insert_wechat=Userwechat::insert([
                    'user_id'=>$user_id,
                    'openid'=>$resutl['openid'],
                ]);
                if ($user_id && $insert_wechat) {
                    // 登陆
                    $request->session()->put('user_id',$user_id);
                    \DB::connection('mysql')->commit();
                }else{
                    \DB::connection('mysql')->rollback();
                    dd('添加信息错误');
                }
        }

            return redirect('/wexinlist');
    }

    // get请求
    public function curl_get($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    // post请求
     public function curl_post($url,$data)
    {
        $curl = curl_init($url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_POST,true);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    } 

    // 模板接口
    public function push_template_msg(Request $request){
      // $req=$request->all();

        $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->tools->get_access_token();
        $data=[
            "touser"=>'ovm570iKEhl3J6Xg5A0EAxSyGk9A',
            "template_id"=>"h4Ff4Xj6PGSDVROjWiIU9vtx7nJXSrFFbdPERJ_utdw",
            "data"=>[
                   "first1"=>[
                       "value"=>"帅哥",
                       "color"=>""
                        ]
                   ],
            "data"=>[
                   "first2"=>[
                       "value"=>"你真帅",
                       "color"=>""
                        ]
                   ],
        ];

        $re=$this->tools->curl_post($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        $resuel=json_decode($re,1);
        dd($resuel);

    }

}