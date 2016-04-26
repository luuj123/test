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
use App\ESTIMATE_SHORT;
use App\MV_POINT;

class AEstimateReply extends Controller
{
    public function insertReply(Request $request){

        $shortId = $request->shortId;
        $companyId = $request->companyId;
        $price = $request->price;
        $packingPrice = $request->packingPrice;
        $halfPackingPrice = $request->halfPackingPrice;
        $ladderStart = $request->ladderStart;
        $ladderEnd = $request->ladderEnd;
        $car = $request->car;
        $workMan = $request->workMan;
        $workGirl = $request->workGirl;
        $info = $request->info;

        DB::beginTransaction();

//        $query = ESTIMATE_SHORT::selectRaw('ESTIMATE_SHORT_REPLIES')
//            ->where('ESTIMATE_SHORT_ID', '=', $shortId)
//            ->where('ESTIMATE_SHORT_REPLIES', '<', 'ESTIMATE_SHORT_MAX_REPLIES')->get();

        $query = 'SELECT ESTIMATE_SHORT_REPLIES FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_ID = '.$shortId.' AND ESTIMATE_SHORT_REPLIES < ESTIMATE_SHORT_MAX_REPLIES;';

        $query = DB::select( DB::raw($query) );

        if(empty($query)){
            DB::rollback();
            return response()->json(array('data' => false));
        } else {
            $query = 'SELECT ESTIMATE_CLICK_ID FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
            $query = DB::select( DB::raw( $query ) );

            if(!empty($query)){
                $query = 'UPDATE ESTIMATE_CLICK SET ESTIMATE_CLICK_BIDDING_REPLY=1, ESTIMATE_CLICK_BIDDING_REPLY_DATE=now() WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
                $query = DB::statement( DB::raw( $query ) );
            }

            $query = 'INSERT INTO ESTIMATE_REPLY VALUES (NULL, '.$shortId.', '.$companyId.',
            '.$price.','.$packingPrice.','.$halfPackingPrice.', "'.$ladderStart.'", "'.$ladderEnd.'",
            '.$car.', '.$workMan.', '.$workGirl.', 0, "'.$info.'")';

            $query = DB::statement( DB::raw($query) );

            $query = "SELECT MV_LIST_POINT_PRODUCT
                                  FROM MV_LIST WHERE MV_LIST_ID='$companyId'";
            $query = DB::select( DB::raw($query) );

            if($query[0]->MV_LIST_POINT_PRODUCT > 15){
                DB::commit();
                return response()->json(array('data' => true));
            } else {
                $query = MV_POINT::selectRaw('MV_POINT_FREE, MV_POINT_CASH')
                    ->where('MV_POINT_FK_MOVE', '=', $companyId)->get();

                $free = $query[0]->MV_POINT_FREE;
                $cash = $query[0]->MV_POINT_CASH;

                if($free > 0){
                    $free--;
                } else if($cash > 0){
                    $cash--;
                } else {
                    DB::rollback();
                    return response()->json(array('data' => 'point_lack'));
                }

                $query = 'UPDATE MV_POINT SET MV_POINT_FREE = '.$free.', MV_POINT_CASH = '.$cash.' WHERE MV_POINT_FK_MOVE = '.$companyId.';';
                $query = DB::statement( DB::raw($query) );

                DB::commit();

                return response()->json(array('data' => true));
            }


        }
    }

    public function updateReply(Request $request){
        $shortId = $request->shortId;
        $companyId = $request->companyId;
        $price = $request->price;
        $packingPrice = $request->packingPrice;
        $halfPackingPrice = $request->halfPackingPrice;
        $ladderStart = $request->ladderStart;
        $ladderEnd = $request->ladderEnd;
        $car = $request->car;
        $workMan = $request->workMan;
        $workGirl = $request->workGirl;
        $info = $request->info;

        $query = 'UPDATE ESTIMATE_REPLY SET ESTIMATE_REPLY_PRICE = '.$price.', ESTIMATE_REPLY_PACKING_PRICE = '.$packingPrice.',
        ESTIMATE_REPLY_HALF_PACKING_PRICE = '.$halfPackingPrice.', ESTIMATE_REPLY_LADDER_START = '.$ladderStart.',
        ESTIMATE_REPLY_LADDER_END = '.$ladderEnd.', ESTIMATE_REPLY_CAR = '.$car.', ESTIMATE_REPLY_MAN = '.$workMan.',
        ESTIMATE_REPLY_GIRL = '.$workGirl.', ESTIMATE_REPLY_CONTENT = "'.$info.'"
        WHERE ESTIMATE_REPLY_FK_SHORT = '.$shortId.' AND ESTIMATE_REPLY_FK_MOVE = '.$companyId.';';

        $query = DB::statement( DB::raw($query) );

        if($query){
            return response()->json(array('data' => true));
        }else {
            return response()->json(array('data' => false));
        }
    }

    public function deleteReply(Request $request){
        DB::beginTransaction();

        $shortId = $request->shortId;
        $companyId = $request->companyId;

        $query = 'DELETE FROM ESTIMATE_REPLY WHERE ESTIMATE_REPLY_FK_SHORT = '.$shortId.' AND ESTIMATE_REPLY_FK_MOVE = '.$companyId.';';
        $query = DB::statement( DB::raw($query) );

        if($query){
            $query = 'SELECT ESTIMATE_CLICK_ID FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
            $query = DB::select( DB::raw( $query ) );

            if(!empty($query)){
                $query = 'UPDATE ESTIMATE_CLICK SET ESTIMATE_CLICK_BIDDING_REPLY=0, ESTIMATE_CLICK_BIDDING_REPLY_DATE=now() WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
                $query = DB::statement( DB::raw( $query ) );
            }

            DB::commit();
            return response()->json(array('data' => true));
        }else {
            DB::rollback();
            return response()->json(array('data' => false));
        }
    }

    public function readReply(Request $request){

        $shortId = $request->shortId;
        $companyId = $request->companyId;

        $query = ESTIMATE_SHORT::leftJoin('ESTIMATE_REPLY', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT', '=', 'ESTIMATE_SHORT.ESTIMATE_SHORT_ID')
            ->leftJoin('MV_LIST', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_MOVE', '=', 'MV_LIST.MV_LIST_ID')
            ->selectRaw('ESTIMATE_SHORT_ID, ESTIMATE_REPLY_ID, MV_LIST_ID, ESTIMATE_REPLY_CONTENT, ESTIMATE_REPLY_PRICE, MV_LIST_NAME,
                        MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_ETC_PHONE, MV_LIST_ICON, ESTIMATE_REPLY_PACKING_PRICE,
                        ESTIMATE_REPLY_HALF_PACKING_PRICE, ESTIMATE_REPLY_CAR, ESTIMATE_REPLY_MAN, ESTIMATE_REPLY_GIRL, ESTIMATE_REPLY_LADDER_START,
                        ESTIMATE_REPLY_LADDER_END')
            ->where('ESTIMATE_REPLY_FK_SHORT', '=', $shortId)
            ->where('MV_LIST_ID', '=', $companyId)
            ->orderBy('ESTIMATE_REPLY_PRICE', 'ASC')->get();

        for($i=0; $i<count($query); $i++){
            if($query[$i]->MV_LIST_ICON == NULL || $query[$i]->MV_LIST_ICON == '' || $query[$i]->MV_LIST_ICON == 'NULL' || $query[$i]->MV_LIST_ICON == 'null'){
                $query[$i]->MV_LIST_ICON = "";
            } else {
                $query[$i]->MV_LIST_ICON = "http://www.gae8.com/24moa/upload/".$query[$i]->MV_LIST_ICON;
            }
        }


        if(count($query) == 0){
            return response()->json(array('data' => false));
        } else {
            return response()->json(array('data' => $query));
        }


    }

    function PushUser(Request $request){
        $shortId = $request->data;

        $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_SHORT LEFT JOIN USER_DEVICE_REGISTERS
        ON ESTIMATE_SHORT.ESTIMATE_SHORT_FK_USER = USER_DEVICE_REGISTERS.USER_DEVICE_REGISTERS_ID
        WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
        $query = DB::select( DB::raw( $query ) );

        if (count($query)) {
            $headers = array('Content-Type:application/json; charset=UTF-8', 'Authorization:key=' . $this->apiKey);

            $list = array();
            $list['title'] = "고객님의 이사 견적에 예상 견적이 등록되었습니다.";

            $arr = array();
            $arr['data'] = array();
            $arr['data']['msg'] = $shortId . '&' . $list['title'] . '&201';
            $arr['registration_ids'] = array();

            $arr['registration_ids'][0] = $query[0]->USER_DEVICE_REGISTERS;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));

            $response = curl_exec($ch);

            curl_close($ch);

            return response()->json(array('data' => $response));
        }
        return response()->json(array('data' => false));
    }
}
