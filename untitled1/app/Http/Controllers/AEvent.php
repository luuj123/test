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



class AEvent extends Controller
{

    public function TonginEvent(Request $request){

        $UserName = $request->UserName;
        $UserPhone = $request->UserPhone;
        $StartAddress = $request->StartAddress;
        $EndAddress = $request->EndAddress;
        $EtcContent = $request->EtcContent;
        $MoveDate = $request->MoveDate;

        $query = "INSERT INTO TONGIN_EVENT (USER_NAME, USER_PHONE, START_ADDR, END_ADDR, ETC_CONTENT, REGDATE, MOVE_DATE)
				VALUES ('".$UserName."', '".$UserPhone."', '".$StartAddress."', '".$EndAddress."' ,'".$EtcContent."', NOW(), '".$MoveDate."')";
        $query = DB::statement( DB::raw( $query ) );

        if($query){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }

    function EndDialog(Request $request){

        $query = 'SELECT EVENT_END_DIALOG_POPUP FROM EVENT_END_DIALOG WHERE EVENT_END_DIALOG_USEYN="Y";';
        $query = DB::select( DB::raw( $query ) );

        $form = array();
        $result = array();

        $query2 = 'SELECT EVENT_END_DIALOG_ID, EVENT_END_DIALOG_APPABLE, EVENT_END_DIALOG_WEBABLE, EVENT_END_DIALOG_IMG_URL, EVENT_END_DIALOG_EVENT_URL FROM EVENT_END_DIALOG WHERE EVENT_END_DIALOG_USEYN="Y";';
        $query2= DB::select( DB::raw( $query2 ) );

        if(count($query)){
            $form['appable'] = $query[0]->EVENT_END_DIALOG_ID;
            $form['webable'] = $query[0]->EVENT_END_DIALOG_WEBABLE;
            $form['img_url'] = $query[0]->EVENT_END_DIALOG_IMG_URL;

            if($query[0]->EVENT_END_DIALOG_APPABLE == "true"){
                $form['event_url'] = "";
            } else {
                $form['event_url'] = $query[4]->EVENT_END_DIALOG_EVENT_URL;
            }

            array_push($result, $form);
        }

        return response()->json(array('popup' => $query, 'popup_data' => $result));
    }

    function Advertisement(){
        $folder = "../storage/advertisement/use_new";
        $items = opendir($folder);
        $data = array();
        $link = array();

        $link[0] = "http://m.cafe.naver.com/ddabongzawon.cafe";
        $link[1] = "http://rentaldotcom.com/mobilev2/?appcode=24moa";
        $link[2] = "http://cafe.naver.com/valpoom";
        $link[3] = "https://play.google.com/store/apps/details?id=com.skt.logii.widget";
        $link[4] = "https://play.google.com/store/apps/details?id=kr.co.amusepark.weddingbyme";

        $counter = 0;
        while($file = readdir($items)){
            if($file != '.' && $file != '..' && is_dir($folder."/".$file) != '1'){
                $form = array();
                $form['img'] = 'http://www.gae8.com/24moa/advertisement/use_new/'.$file;
                $form['event'] = $link[$counter];
                $counter++;
                array_push($data, $form);
            }
        }
        closedir($items);
        return $data;
    }
}
