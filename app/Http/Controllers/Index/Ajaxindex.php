<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ajaxe;

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
}
