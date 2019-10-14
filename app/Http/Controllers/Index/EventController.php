<?php

namespace App\Http\Controllers\Index;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function event(){
    	$info=file_get_contents("php://input");
    	file_put_contents(storage_path('logs/wexin/'.date('Y-m-d').'.log'),$info,FILE_APPEND);
    }
}
