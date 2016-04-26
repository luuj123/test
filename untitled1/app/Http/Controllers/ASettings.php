<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

use Illuminate\Http\Response;
//
//use App\Http\Requests;
//
use App\Http\Controllers\Controller;
use App\UserRegisters;
//use App\EstimateShort;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\ALARM_SWITCH;
use App\NOTICE_BOARD_COMPANY;


class ASettings extends Controller
{

    public function alarm(Request $request){
        $userId = $request->userId;
        $action = $request->action;

        if($action == "get"){
            $query = ALARM_SWITCH::selectRaw('VALUE')
                ->where('FK_USER_DEVICE_ID', '=', $userId)->get();

            return response()->json(array('VALUE' => $query[0]->VALUE));
        } else if($action == "set"){
            $value = $request->value;

            $query = ALARM_SWITCH::selectRaw('VALUE')
                ->where('FK_USER_DEVICE_ID', '=', $userId)->get();

            if(isset($query[0]->VALUE)){
                $query2 = 'UPDATE ALARM_SWITCH SET VALUE = '.$value.' WHERE FK_USER_DEVICE_ID = '.$userId.';';
                $query2 = DB::statement( DB::raw($query2) );

                return response()->json(array('data' => $query2));
            }
        }

    }

}
