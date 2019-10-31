<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tools\Tools;
use App\Models\Userwechat;
use App\Models\Openid;
use App\Models\User;

class EventController extends Controller
{
	 public $tools;
	 public $request;
    public function __construct(Tools $tools,Request $request)
    {
    		$this->request=$request;
           $this->tools = $tools;
    }

    public function event(){  
       $info = file_get_contents("php://input");
        file_put_contents(storage_path('logs/wexin/'.date('Y-m-d').'.log'),"<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<\n",FILE_APPEND);
        file_put_contents(storage_path('logs/wexin/'.date('Y-m-d').'.log'),$info,FILE_APPEND);
        $xml_obj = simplexml_load_string($info,'SimpleXMLElement',LIBXML_NOCDATA);
        $xml_arr = (array)$xml_obj;
        // 关注操作
      if($xml_arr['MsgType']=='event' && $xml_arr['Event']=='subscribe'){
       
             $wechat_user=$this->tools->get_wechat_user($xml_arr['FromUserName']);
             $msg='欢迎久睡成瘾同学感谢您的关注 回复1老师姓名 回复2老师图片';
             echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[".$msg."]]></Content></xml>";
        }


        // 签到
        if ($xml_arr['MsgType']=='event' && $xml_arr['Event']=='CLICK' && $xml_arr['EventKey']=='sign') {
           // 判断是否签到
            $usere_wechat=Userwechat::where(['openid'=>$xml_arr['FromUserName']])->first();
            $today = date('Y-m-d',time()); //今天
            $last_day = date('Y-m-d',strtotime("-1 days")); //昨天s
            if ($usere_wechat->sign_day==$today) {
                //已签到
                $msg="您已签到";
                echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[".$msg."]]></Content></xml>";
            }else{
                // 根据签到次数加积分
                // 连续签到
                if ($usere_wechat->sign_day==$last_day) {
                    // 连续签到
                    $sign_num = $usere_wechat->sign_num + 1;
                    if($sign_num >= 6){
                        $sign_num = 1;
                    }

                    Userwechat::where(['openid'=>$xml_arr['FromUserName']])->update([
                        'sign_day'=>$today,
                        'sign_num'=>$sign_num,
                        'sign_score'=>$usere_wechat->sign_score + 5 * $sign_num
                        ]);
                    
                }else{
                     // 非连续签到
                     Userwechat::where(['openid'=>$xml_arr['FromUserName']])->update([
                        'sign_day'=>$today,
                        'sign_num'=>1,
                        'sign_score'=>$usere_wechat->sign_score + 5
                        ]);
                }

            }

        }

		//普通消息
        if($xml_arr['MsgType']=='text' && $xml_arr['Content']=="2"){
        	
            $media_id="NxMp1rpGmAmkRZe4psA49-HnNN2frl-ENJkvSmZZgM8";

            echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId><![CDATA[".$media_id."]]></MediaId></Image></xml>";

        }else if ($xml_arr['MsgType']=='text' && $xml_arr['Content']=="1") {
            $msg='王亚蒙';
             echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[".$msg."]]></Content></xml>";
        }
    }
}
