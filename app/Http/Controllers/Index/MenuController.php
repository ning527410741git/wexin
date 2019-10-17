<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tools\Tools;
class MenuController extends Controller
{

	 public $tools;
	 public $request;
    public function __construct(Tools $tools,Request $request)
    {
    		$this->request=$request;
           $this->tools = $tools;
    }

    public function load_menu(){
    	$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->tools->get_access_token();
        $data = [
            'button'=> [
                [
                    'type'=>'click',
                    'name'=>'今日歌曲',
                    'key'=>'V1001_TODAY_MUSIC'
                ],
                [
                    'name'=>'菜单',
                    'sub_button'=>[
                        [
                            'type'=>'view',
                            'name'=>'搜索',
                            'url'=>'http://www.soso.com/'
                        ],
                        [
                            'type'=>'click',
                            'name'=>'赞一下我们',
                            'key'=>'V1001_GOOD'
                        ]
                    ]
                ]
            ]
        ];
       // dd($url);
        $re = $this->tools->curl_post($url,json_encode($data,JSON_UNESCAPED_UNICODE));
        $result = json_decode($re,1);
        dd($result);
    }

    public function addwech(){
    	echo 1;
    }

  
}
