<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ajaxe;
use App\Models\Userwechat;
use App\Models\Indexuser;
use App\Tools\Tools;
use Cache;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Ajaxindex extends Controller
{
    public $tools;
    public $request;
    public function __construct(Tools $tools,Request $request)
    {
        $this->request=$request;
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


    // 带参数的二维码
    public function wechat_list(){
        $user_info=User::all();
        return view("index.ajax.wechatList",['user_info'=>$user_info]);
    }

    public function create_qrcode(Request $request){
      $req=$request->all();
      // dd($req);
      $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->tools->get_access_token();
      // {"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}

      $data=[
          'expire_seconds'=>30 * 24 * 3600,
          "action_name"=>"QR_SCENE",
          'action_info'=>[
            'scene'=>[
              "scene_id"=>$req['uid']
            ]

          ],

      ];

      $re=$this->tools->curl_post($url,json_encode($data,JSON_UNESCAPED_UNICODE));
      $result=json_decode($re,1);
      $qrcode_url="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$result['ticket'];
      $qrcode_source=$this->tools->curl_get($qrcode_url);
      $qrcode_name=$req['uid'].rand(10000,99999).'jpg';
      Storage::put('wexin/qrcode/'.$qrcode_name,$qrcode_source);
      User::where(['id'=>$req['uid']])->update([
          'qrcode_url'=>'/storage/wexin/qrcode/'.$qrcode_name,
        ]);
      return redirect('/wechat_list');

    }


    // js-sdk
    public function get_location(){
      $url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
      $appid='wxff842daa19066eae';
      $_now_=time();
      $rand_str=rand(1000,9999).'jssdk'.time();
      $jsapi_ticket = $this->tools->get_jsapi_ticket();
      $sign_str='jsapi_ticket='.$jsapi_ticket.'&noncestr='.$rand_str.'&timestamp='.$_now_.'&url='.$url;
      $signature=sha1($sign_str);

      return view('index.ajax.location',['signature'=>$signature,'appid'=>$appid,'time'=>$_now_,'rand_str'=>$rand_str]);

  } 

}

 public function youjia()
    {
        $str = '{"resultcode":"200","reason":"查询成功!","result":[{"city":"北京","b90":"-","b93":"6.62","b97":"7.04","b0":"6.28","92h":"6.62","95h":"7.04","98h":"8.02","0h":"6.28"},{"city":"上海","b90":"-","b93":"6.58","b97":"7.00","b0":"6.22","92h":"6.58","95h":"7.00","98h":"7.70","0h":"6.22"},{"city":"江苏","b90":"-","b93":"6.59","b97":"7.01","b0":"6.21","92h":"6.59","95h":"7.01","98h":"7.89","0h":"6.21"},{"city":"天津","b90":"-","b93":"6.61","b97":"6.98","b0":"6.24","92h":"6.61","95h":"6.98","98h":"7.90","0h":"6.24"},{"city":"重庆","b90":"-","b93":"6.69","b97":"7.07","b0":"6.32","92h":"6.69","95h":"7.07","98h":"7.96","0h":"6.32"},{"city":"江西","b90":"-","b93":"6.58","b97":"7.07","b0":"6.29","92h":"6.58","95h":"7.07","98h":"8.07","0h":"6.29"},{"city":"辽宁","b90":"-","b93":"6.59","b97":"7.03","b0":"6.16","92h":"6.59","95h":"7.03","98h":"7.65","0h":"6.16"},{"city":"安徽","b90":"-","b93":"6.58","b97":"7.06","b0":"6.28","92h":"6.58","95h":"7.06","98h":"7.89","0h":"6.28"},{"city":"内蒙古","b90":"-","b93":"6.56","b97":"7.00","b0":"6.13","92h":"6.56","95h":"7.00","98h":"7.68","0h":"6.13"},{"city":"福建","b90":"-","b93":"6.59","b97":"7.04","b0":"6.24","92h":"6.59","95h":"7.04","98h":"7.70","0h":"6.24"},{"city":"宁夏","b90":"-","b93":"6.53","b97":"6.90","b0":"6.14","92h":"6.53","95h":"6.90","98h":"7.90","0h":"6.14"},{"city":"甘肃","b90":"-","b93":"6.51","b97":"6.96","b0":"6.15","92h":"6.51","95h":"6.96","98h":"7.40","0h":"6.15"},{"city":"青海","b90":"-","b93":"6.57","b97":"7.04","b0":"6.18","92h":"6.57","95h":"7.04","98h":"0","0h":"6.18"},{"city":"广东","b90":"-","b93":"6.64","b97":"7.19","b0":"6.25","92h":"6.64","95h":"7.19","98h":"8.07","0h":"6.25"},{"city":"山东","b90":"-","b93":"6.60","b97":"7.08","b0":"6.24","92h":"6.60","95h":"7.08","98h":"7.80","0h":"6.24"},{"city":"广西","b90":"-","b93":"6.68","b97":"7.22","b0":"6.31","92h":"6.68","95h":"7.22","98h":"8.00","0h":"6.31"},{"city":"山西","b90":"-","b93":"6.58","b97":"7.10","b0":"6.30","92h":"6.58","95h":"7.10","98h":"7.80","0h":"6.30"},{"city":"贵州","b90":"-","b93":"6.74","b97":"7.13","b0":"6.35","92h":"6.74","95h":"7.13","98h":"8.03","0h":"6.35"},{"city":"陕西","b90":"-","b93":"6.51","b97":"6.88","b0":"6.15","92h":"6.51","95h":"6.88","98h":"7.68","0h":"6.15"},{"city":"海南","b90":"-","b93":"7.73","b97":"8.20","b0":"6.33","92h":"7.73","95h":"8.20","98h":"9.27","0h":"6.33"},{"city":"四川","b90":"-","b93":"6.65","b97":"7.17","b0":"6.34","92h":"6.65","95h":"7.17","98h":"7.80","0h":"6.34"},{"city":"河北","b90":"-","b93":"6.61","b97":"6.98","b0":"6.24","92h":"6.61","95h":"6.98","98h":"7.80","0h":"6.24"},{"city":"西藏","b90":"-","b93":"7.51","b97":"7.94","b0":"6.80","92h":"7.51","95h":"7.94","98h":"0","0h":"6.80"},{"city":"河南","b90":"-","b93":"6.62","b97":"7.07","b0":"6.23","92h":"6.62","95h":"7.07","98h":"7.72","0h":"6.23"},{"city":"新疆","b90":"-","b93":"6.51","b97":"7.00","b0":"6.13","92h":"6.51","95h":"7.00","98h":"7.82","0h":"6.13"},{"city":"黑龙江","b90":"-","b93":"6.55","b97":"6.95","b0":"6.02","92h":"6.55","95h":"6.95","98h":"7.93","0h":"6.02"},{"city":"吉林","b90":"-","b93":"6.58","b97":"7.10","b0":"6.17","92h":"6.58","95h":"7.10","98h":"7.73","0h":"6.17"},{"city":"云南","b90":"-","b93":"6.76","b97":"7.26","b0":"6.32","92h":"6.76","95h":"7.26","98h":"7.94","0h":"6.32"},{"city":"湖北","b90":"-","b93":"6.62","b97":"7.09","b0":"6.23","92h":"6.62","95h":"7.09","98h":"7.66","0h":"6.23"},{"city":"浙江","b90":"-","b93":"6.59","b97":"7.01","b0":"6.23","92h":"6.59","95h":"7.01","98h":"7.68","0h":"6.23"},{"city":"湖南","b90":"-","b93":"6.58","b97":"6.99","b0":"6.31","92h":"6.58","95h":"6.99","98h":"7.79","0h":"6.31"}],"error_code":0}';
        echo $str;
    }
