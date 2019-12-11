<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class GoodsController extends Controller
{
   	public function addGoo(Request $request){
   		$arr=array("id"=>1);
   		echo json_encode($arr);
   	}
}
