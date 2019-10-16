<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tools\Tools;
class ResourceController extends Controller
{
	 public $tools;
	 public $request;
    public function __construct(Tools $tools,Request $request)
    {
    		$this->request=$request;
           $this->tools = $tools;
    }

   	public function uploads(){
   		return view('index.resource.upload');
   	}

   	public function do_upload(){
   		$req=$this->request->all();
   		if (!$this->request->hasFile('rsource')) {
   			dd('没有文件上传');
   		}

   		$file_obj=$this->request->file('rsource');
   		$file_exe=$file_obj->getClientOriginalExtension();
   		$file_name=time().rand(1000,9999).'.'.$file_exe;
   		$path=$this->request->file('rsource')->storeAs('wechat/'.$req['type'],$file_name);
   		$url='https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->tools->get_access_token().'&type='.$req['type'];
   		$re=$this->tools->wechat_curl_file($url,storage_path('app/public/'.$path));
   		$result=json_decode($re,1);
   		dd($result);

   	}
}
