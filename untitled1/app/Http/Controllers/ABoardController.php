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


class ABoardController extends Controller
{
    public function NoticeBoardUser(Request $request){

        $list = array();
        $list['success'] = false;
        $list['data'] = array();

        $query = 'SELECT NOTICE_BOARD_USER_DATE, NOTICE_BOARD_USER_TEXT, NOTICE_BOARD_USER_ADDRESS, NOTICE_BOARD_USER_STATE FROM NOTICE_BOARD_USER WHERE NOTICE_BOARD_USER_PUBLIC = 1';
        $query = DB::select( DB::raw($query) );

        for($i=0; $i<count($query); $i++){
            $form = array();
            $form['date'] = explode(" ", $query[$i]->NOTICE_BOARD_USER_DATE)[0];
            $form['title'] = $query[$i]->NOTICE_BOARD_USER_TEXT;
            $form['url'] = $query[$i]->NOTICE_BOARD_USER_ADDRESS;
            $form['state'] = $query[$i]->NOTICE_BOARD_USER_STATE;

            array_push($list['data'], $form);
        }

        $list['success'] = true;

        return response()->json(array('data' => $list));
    }

    function NoticeBoardInformation(Request $request){
        $list = array();
        $list['success'] = false;
        $list['data'] = array();

        $query = 'SELECT TITLE, URL FROM NOTICE_BOARD_INFORMATION WHERE PUBLIC = 1';
        $query = DB::select( DB::raw($query) );

        for($i=0; $i<count($query); $i++){
            $form = array();

            $form['title'] = $query[$i]->TITLE;
            $form['url'] = $query[$i]->URL;

            array_push($list['data'], $form);
        }

        $list['success'] = true;

        return response()->json(array('data' => $list));
    }

    function NoticeBoardCompany(){

        $list = array();
        $list['success'] = false;
        $list['data'] = array();

        $query = 'SELECT NOTICE_BOARD_COMPANY_TEXT, NOTICE_BOARD_COMPANY_ADDRESS FROM NOTICE_BOARD_COMPANY WHERE NOTICE_BOARD_COMPANY_PUBLIC = 1';
        $query = DB::select( DB::raw($query) );

        for($i=0; $i<count($query); $i++){
            $form = array();
            $form['text'] = $query[$i]->NOTICE_BOARD_COMPANY_TEXT;
            $form['address'] = $query[$i]->NOTICE_BOARD_COMPANY_ADDRESS;

            array_push($list['data'], $form);
        }
        $list['success'] = true;

        return response()->json($list);
    }

//    public function NoticeBoardCompany(Request $request){
//        $query = NOTICE_BOARD_COMPANY::selectRaw('NOTICE_BOARD_COMPANY_TEXT AS TEXT, NOTICE_BOARD_COMPANY_ADDRESS AS ADDRESS')
//            ->where('NOTICE_BOARD_COMPANY_PUBLIC', '=', '1')->get();
//        return response()->json(array('data' => $query));
//    }
}
