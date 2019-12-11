<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoodsController extends Controller
{
   	public function addGoo(Request $request){
   		$data=file_get_contents('http://127.0.0.1:8848/HB/index.html');
   		 $resurl=json_decode($data,1);
   		 dd($resurl);
   	}
}
