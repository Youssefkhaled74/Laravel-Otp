<?php

namespace App\Http\Controllers\Api;

trait ApiResponseTrait
{
    public function apiResponse($data=null,$status=null,$message=null){
        $array=[
            'data'=>$data,
            'message'=>$message,
            'status'=>$status,
        ];
        return response($array);
    }
}