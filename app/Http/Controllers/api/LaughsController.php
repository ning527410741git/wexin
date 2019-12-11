<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LaughsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //获取access_token
        $ip = ($_SERVER['REMOTE_ADDR']);
        $num = sprintf("%u",crc32($ip));
        // Redis::setex('b',10,12);
        // echo Redis::get('b');
        header("Location:http://www.laravel.com/api/laugh/".$num);
        // Redis::set($num,1);
        // dump($redis);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($redis)
    {
        $first = Redis::get($redis);
        // echo $first;exit;
        //如果有值就自增，没值添加
        if($first){
            //有值
            if($first>=5){
                echo "调用次数太频繁";exit;
            }else{
                Redis::setex($redis,60,$first+1);
            }
        }else{
            //没值
            Redis::setex($redis,60,1);
        }


        //展示 传过来access_token 
        $conn = mysqli_connect('localhost','root','');
        mysqli_select_db($conn,'203a');
        $sql = "select * from laugh";
        $re = mysqli_query($conn,$sql);
        $data =[];
        while($info = mysqli_fetch_array($re)){
            array_push($data,$info);
        }
        $get_into = date("Y/m/d/H",time());
        
        $new_info = $get_into."/1.txt";
        if(file_exists($new_info)){
            // mkdir(storage_path($get_into),777,true);
            echo 12;
        }
        echo 34;
        
        // file_put_contents(storage_path($new_info),json_encode($data),FILE_APPEND);
        // $into = file_put_contents($new_info,json_encode($data),FILE_APPEND);
        // echo $get_into;
        // var_dump(json_encode($data));
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
