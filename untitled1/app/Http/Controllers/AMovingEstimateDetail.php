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
use App\ESTIMATE_LIST;
use App\ESTIMATE_SHORT;

class AMovingEstimateDetail extends Controller
{

    public function EstimateList(Request $request){

        $companyId = $request->companyId;
        $listId = $request->listId;

        $form = array();

        $query = 'SELECT ESTIMATE_SHORT_ID FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_FK_LISt='.$listId.';';
        $query = DB::select( DB::raw( $query ) );

        if(count($query)){
            $form["info"]["ESTIMATE_SHORT_ID"] = $query[0]->ESTIMATE_SHORT_ID;
            $form["info"]["ESTIMATE_LIST_ID"] = $listId;
        }

        $query = ESTIMATE_LIST::leftJoin('ESTIMATE_ADDR', 'ESTIMATE_LIST.ESTIMATE_LIST_ID', '=', 'ESTIMATE_ADDR.ESTIMATE_ADDR_FK_LIST')
                ->leftJoin('USER_DEVICE_REGISTERS', 'ESTIMATE_LIST.ESTIMATE_LIST_FK_USER', '=', 'USER_DEVICE_REGISTERS.USER_DEVICE_REGISTERS_ID')
                ->leftJoin('ESTIMATE_IMG', 'ESTIMATE_IMG.ESTIMATE_IMG_FK_LIST', '=', 'ESTIMATE_LIST.ESTIMATE_LIST_ID')
                ->selectRaw('ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_CONTENT, ESTIMATE_LIST_START_LADDER, ESTIMATE_LIST_END_LADDER, ESTIMATE_LIST_KIND, ESTIMATE_LIST_PEOPLE, ESTIMATE_LIST_ROOM_SIZE,
                             ESTIMATE_LIST_AIR, ESTIMATE_LIST_BED, ESTIMATE_LIST_TV, ESTIMATE_LIST_PIANO, ESTIMATE_LIST_WARDROBE, ESTIMATE_LIST_BIDDING, ESTIMATE_LIST_DATE, ESTIMATE_LIST_PHONE, ESTIMATE_ADDR_START,
                             ESTIMATE_ADDR_END, USER_DEVICE_REGISTERS_PHONE, ESTIMATE_IMG_ITEM')
                ->where('ESTIMATE_LIST_ID', '=', $listId)->get();



        $form["info"]["ESTIMATE_LIST_FOLDER"] = $query[0]->ESTIMATE_LIST_FOLDER;
        $form["info"]["ESTIMATE_LIST_CONTENT"] = $query[0]->ESTIMATE_LIST_CONTENT;
        $form["info"]["ESTIMATE_LIST_START_LADDER"] = $query[0]->ESTIMATE_LIST_START_LADDER-1;
        $form["info"]["ESTIMATE_LIST_END_LADDER"] = $query[0]->ESTIMATE_LIST_END_LADDER-1;
        $form["info"]["ESTIMATE_LIST_KIND"] = $query[0]->ESTIMATE_LIST_KIND;
        $form["info"]["ESTIMATE_LIST_PEOPLE"] = $query[0]->ESTIMATE_LIST_PEOPLE;
        $form["info"]["ESTIMATE_LIST_ROOM_SIZE"] = $query[0]->ESTIMATE_LIST_ROOM_SIZE;
        $form["info"]["ESTIMATE_LIST_AIR"] = $query[0]->ESTIMATE_LIST_AIR;
        $form["info"]["ESTIMATE_LIST_BED"] = $query[0]->ESTIMATE_LIST_BED;
        $form["info"]["ESTIMATE_LIST_TV"] = $query[0]->ESTIMATE_LIST_TV;
        $form["info"]["ESTIMATE_LIST_PIANO"] = $query[0]->ESTIMATE_LIST_PIANO;
        $form["info"]["ESTIMATE_LIST_WARDROBE"] = $query[0]->ESTIMATE_LIST_WARDROBE;
        $form["info"]["ESTIMATE_LIST_BIDDING"] = $query[0]->ESTIMATE_LIST_BIDDING;
        $form["info"]["ESTIMATE_LIST_DATE"] = $query[0]->ESTIMATE_LIST_DATE;
        $form["info"]["ESTIMATE_LIST_PHONE"] = $query[0]->ESTIMATE_LIST_PHONE;
        $form["info"]["ESTIMATE_ADDR_START"] = $query[0]->ESTIMATE_ADDR_START;
        $form["info"]["ESTIMATE_ADDR_END"] = $query[0]->ESTIMATE_ADDR_END;

        if(substr($query[0]->USER_DEVICE_REGISTERS_PHONE, 0, 1) == '+'){
            $form["info"]["USER_DEVICE_REGISTERS_PHONE"] = '0'.substr($query[0]->USER_DEVICE_REGISTERS_PHONE, 3);
        } else {
            $form["info"]["USER_DEVICE_REGISTERS_PHONE"] = $query[0]->USER_DEVICE_REGISTERS_PHONE;
        }

        $img = array();
        $form['imglist'] = array();

        for($i=0; $i<count($query); $i++){
            if(!empty($query[$i]->ESTIMATE_IMG_ITEM)){
                $img['img'] = 'http://www.gae8.com/24moa/estimateBoard/'.$query[0]->ESTIMATE_LIST_FOLDER.'/thumb/'.$query[$i]->ESTIMATE_IMG_ITEM;
                array_push($form['imglist'], $img);
            }
//            $img['img'] = 'http://www.gae8.com/24moa/estimateBoard/'.$query[0]->ESTIMATE_LIST_FOLDER.'/thumb/'.$query[$i]->ESTIMATE_IMG_ITEM;
//            array_push($form['imglist'], $img);
//            array_push($form['img'], $imgUrl);
//            $form["img"][$i] = 'http://www.gae8.com/24moa/estimateBoard/'.$query[0]->ESTIMATE_LIST_FOLDER.'/thumb/'.$query[$i]->ESTIMATE_IMG_ITEM;
        }


        $query = ESTIMATE_SHORT::leftJoin('ESTIMATE_REPLY', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT', '=', 'ESTIMATE_SHORT.ESTIMATE_SHORT_ID')
            ->leftJoin('MV_LIST', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_MOVE', '=', 'MV_LIST.MV_LIST_ID')
            ->selectRaw('ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_ID, ESTIMATE_REPLY_ID, MV_LIST_ID, ESTIMATE_REPLY_CONTENT, ESTIMATE_REPLY_PRICE, MV_LIST_NAME,
                         MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_CHOOSE_PHONE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_ICON, ESTIMATE_REPLY_PACKING_PRICE,
                         ESTIMATE_REPLY_HALF_PACKING_PRICE, ESTIMATE_REPLY_CAR, ESTIMATE_REPLY_MAN, ESTIMATE_REPLY_GIRL, ESTIMATE_REPLY_LADDER_START, ESTIMATE_REPLY_LADDER_END')
            ->where('ESTIMATE_SHORT_FK_LIST', '=', $listId)
            ->where('MV_LIST_ID', '=', $companyId)
            ->orderBy('ESTIMATE_REPLY_PRICE', 'ASC')->get();


        $form['reply'] = $query;

        return response()->json(array('data' => $form));
    }

    function GradeCheck(Request $request){
        $companyId = $request->companyId;
        $listId = $request->listId;

        $query = 'SELECT MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID='.$companyId;
        $query = DB::select( DB::raw( $query ) );

        $query2 = 'SELECT ESTIMATE_SHORT_GRADE FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_FK_LIST='.$listId;
        $query2 = DB::select( DB::raw( $query2 ) );

        if($query[0]->MV_LIST_GRADE >= $query2[0]->ESTIMATE_SHORT_GRADE){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }
}
