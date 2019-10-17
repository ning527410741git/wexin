<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tools\Tools;

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
        // dd($xml_arr);
        // 关注操作
      if($xml_arr['MsgType']=='event' && $xml_arr['Event']=='subscribe'){
            $nickname=$this->tools->get_wechat_user($xml_arr['FromUserName']);
            // dd($nickname);
            $msg="你好".$nickname['nickname'].",欢迎来到！";
            echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[".$msg."]]></Content></xml>";
        }
		//普通消息
        if($xml_arr['MsgType']=='text' && $xml_arr['Content']=="111"){
        	
            $media_id="3SldExvZbrAw7mxtTLHcbsohYat_TT4ZtZPJzDFcvHZ7ABQKhNDsd5VgLzOIWPAH";

            echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[image]]></MsgType><Image><MediaId><![CDATA[".$media_id."]]></MediaId></Image></xml>";

          // echo "<xml><ToUserName><![CDATA[".$xml_arr['FromUserName']."]]></ToUserName><FromUserName><![CDATA[".$xml_arr['ToUserName']."]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[".$msg."]]></Content></xml>";
        }
    }
}
