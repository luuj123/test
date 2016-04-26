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

use Illuminate\Http\Request;
use App\Http\Requests;
use App\EstimateShort;

class LoginController extends Controller
{

    public function login(Request $request){

//        $user = UserRegisters::all();
//        $user = UserRegisters::withHidden("USER_DEVICE_REGISTERS_ID")->get();
//
//        $name = $request->input('name');
//        return $user;

//        $user = UserRegisters::where('USER_DEVICE_REGISTERS_ID', '=', '13011')->get(array('USER_DEVICE_REGISTERS_ID'));
//        return $user;
//        return response()->json(array('data' => $user));

        $skip = 5;
        $take = 5;

        $pageNo = $request->input('pageNo');
        if($pageNo == ''){
            $pageNo = 1;
        }
        $skip = ($pageNo-1)*10;
        $take = 10;


//        $result = EstimateShort::where('ESTIMATE_SHORT_REGDATE', '>', '2015-11-15')->get();


//        $result = EstimateShort::leftJoin('USER_DEVICE_REGISTERS AS UDR', 'ESTIMATE_SHORT_FK_USER','=','UDR.USER_DEVICE_REGISTERS_ID')
//            ->leftJoin('ESTIMATE_ADDR AS EA', 'ESTIMATE_SHORT_ID', '=', 'EA.ESTIMATE_ADDR_FK_SHORT')
//            ->leftJoin('ALLIANCE_ESTIMATE AS AE', 'ESTIMATE_SHORT_ID', '=', 'AE.FK_ESTIMATE_SHORT')
//            ->leftJoin('ESTIMATE_LIST AS EL', 'ESTIMATE_SHORT_FK_LIST', '=', 'EL.ESTIMATE_LIST_ID')
//            ->selectRaw(
//                'ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, USER_DEVICE_REGISTERS_NAME,
//                (SELECT REASON FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REASON,
//                (SELECT REGDATE FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REGDATE'
//            )
//            ->where('ESTIMATE_SHORT_REGDATE', '>', '2015-11-15')
//            ->orderBy('ESTIMATE_SHORT_ID', 'desc')
//            ->skip($skip)->take($take)->get();

//        return $result;

//        $result = EstimateShort::leftJoin('USER_DEVICE_REGISTERS AS UDR', 'ESTIMATE_SHORT_FK_USER','=','UDR.USER_DEVICE_REGISTERS_ID')
//            ->leftJoin('ESTIMATE_ADDR AS EA', 'ESTIMATE_SHORT_ID', '=', 'EA.ESTIMATE_ADDR_FK_SHORT')
//            ->leftJoin('ALLIANCE_ESTIMATE AS AE', 'ESTIMATE_SHORT_ID', '=', 'AE.FK_ESTIMATE_SHORT')
//            ->leftJoin('ESTIMATE_LIST AS EL', 'ESTIMATE_SHORT_FK_LIST', '=', 'EL.ESTIMATE_LIST_ID')
//            ->selectRaw(
//                'ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, USER_DEVICE_REGISTERS_NAME,
//                (SELECT REASON FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REASON,
//                (SELECT REGDATE FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REGDATE'
//            )
//            ->where('ESTIMATE_SHORT_REGDATE', '>', '2015-11-15')
//            ->orderBy('ESTIMATE_SHORT_ID', 'desc')
//            ->skip($skip)->take($take);
//        if($pageNo == 1){
//            $result = $result->where('ESTIMATE_SHORT_ID','=','31335')->get();
//        }else{
//            $result = $result->where('ESTIMATE_SHORT_ID','!=','31335')->get();
//        }
//        $re = array();
//        $re = $result;
//
//        $result[0]['ESTIMATE_SHORT_ID'] = '123123';
//
//        return $result;

//        return response()->json(array('data' => $result));

        $StartDate = $request->StartDate;
        $EndDate = $request->EndDate;

        $result = EstimateShort::leftJoin('USER_DEVICE_REGISTERS AS UDR', 'ESTIMATE_SHORT_FK_USER','=','UDR.USER_DEVICE_REGISTERS_ID')
            ->leftJoin('ESTIMATE_ADDR AS EA', 'ESTIMATE_SHORT_ID', '=', 'EA.ESTIMATE_ADDR_FK_SHORT')
            ->leftJoin('ALLIANCE_ESTIMATE AS AE', 'ESTIMATE_SHORT_ID', '=', 'AE.FK_ESTIMATE_SHORT')
            ->leftJoin('ESTIMATE_LIST AS EL', 'ESTIMATE_SHORT_FK_LIST', '=', 'EL.ESTIMATE_LIST_ID')
            ->selectRaw(
                'ESTIMATE_SHORT_ID, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND,
                 ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_ALLIANCE,
                 EA.ESTIMATE_ADDR_START, EA.ESTIMATE_ADDR_END, REPLACE(UDR.USER_DEVICE_REGISTERS_PHONE,\'+82\',\'0\') AS USER_DEVICE_REGISTERS_PHONE, UDR.USER_DEVICE_REGISTERS_NAME,
                 (SELECT REASON FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REASON,
                 (SELECT REGDATE FROM ESTIMATE_STATE_CHANGE WHERE FK_SHORT_ID = ESTIMATE_SHORT_ID ORDER BY REGDATE DESC LIMIT 1) AS REGDATE,
                 AE.FK_ALLIANCE, EL.ESTIMATE_LIST_CONTENT, ESTIMATE_SHORT_REGDATE,
                 EL.ESTIMATE_LIST_START_LADDER, EL.ESTIMATE_LIST_END_LADDER, EL.ESTIMATE_LIST_PEOPLE, EL.ESTIMATE_LIST_ROOM_SIZE,
                 EL.ESTIMATE_LIST_AIR, EL.ESTIMATE_LIST_BED, EL.ESTIMATE_LIST_TV, EL.ESTIMATE_LIST_PIANO, EL.ESTIMATE_LIST_WARDROBE'
            );

        if($StartDate != "" && $EndDate != ""){
            $result = $result->where('ESTIMATE_SHORT_DATE', '>=', $StartDate)->where('ESTIMATE_SHORT_DATE', '<=', $EndDate);
        }elseif($StartDate != "" && $EndDate == ""){
            $result = $result->where('ESTIMATE_SHORT_DATE', '>=', $StartDate);
        }
        $result = $result->orderBy('ESTIMATE_SHORT_ID', 'desc')->skip($skip)->take($take)->get();

//        $result[0]['ESTIMATE_SHORT_ID'] = '123123';

        for($i = 0; $i < count($result); $i++){
            $result[$i]->TEST = "test";
        }

        return response()->json(array('data' => $result));
    }
}
