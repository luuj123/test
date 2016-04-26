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
use App\CODE_AREA;



class ACodeArea extends Controller
{

    public function CodeArea(Request $request){
        $area_code = $request->area_code;

        if($area_code == ""){
            $query = CODE_AREA::selectRaw('CODE_VALUE, CODE_NAME')
                ->whereNull('PARENT_CODE')
                ->where('CODE_NAME', 'not like', '%출장%')
                ->orderBy('CODE_VALUE')->get();
        } else {
            $query = CODE_AREA::selectRaw('CODE_VALUE, CODE_NAME')
                ->where('PARENT_CODE', '=', $area_code)
                ->where('CODE_NAME', 'not like', '%출장%')
                ->orderBy('CODE_VALUE')->get();
        }

        return response()->json($query);
    }
}
