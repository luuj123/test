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


class ACLEstimateList extends Controller
{

    public function EstimateRead(Request $request){
        $id = $request->id;
        $companyId = $request->companyId;
        $state = $request->data;
        $doName = $request->doName;
        $siName = $request->siName;
        $dongName = $request->dongName;

        $lastId = -1;
        $category = -1;
        $itemSize = 0;

        $nextSize = $itemSize + $this->maxItems;
        $data['info'] = array();
        $data['estimate'] = array();
        if($id != null){
            $query = 'SELECT CL_ESTIMATE_SHORT_ID, CL_ESTIMATE_SHORT_KIND, CL_ESTIMATE_SHORT_NAME, CL_ESTIMATE_SHORT_CURRENT_DATE, CL_ESTIMATE_SHORT_DATE,
            CL_ESTIMATE_SHORT_PUBLIC, CL_ESTIMATE_SHORT_ADDRESS, CL_ESTIMATE_SHORT_PROGRESS FROM CL_ESTIMATE_SHORT
            WHERE CL_ESTIMATE_SHORT_FK_USER = '.$id.';';

            $query = DB::select( DB::raw($query) );
            for($i=0; $i<count($query); $i++){
                $form = array();
                $form['shortId'] = $query[$i]->CL_ESTIMATE_SHOROT_ID;
                $form['kind'] = $query[$i]->CL_ESTIMATE_SHORT_KIND;
                $form['name'] = $query[$i]->CL_ESTIMATE_SHORT_NAME;
                $form['date'] = $query[$i]->CL_ESTIMATE_SHORT_CURRENT_DATE;
                $form['cleaning_date'] = $query[$i]->CL_ESTIMATE_SHORT_DATE;
                $form['public'] = $query[$i]->CL_ESTIMATE_SHORT_PUBLIC;
                $form['address'] = $query[$i]->CL_ESTIMATE_SHORT_ADDRESS;
                $form['authority'] = true;
                $form['progress'] = $query[$i]->CL_ESTIMATE_SHORT_PROGRESS;
                $form['phone'] = "";
                array_push($data['info'], $form);
            }
        }

        if($state === "company"){
            $query = 'SELECT CL_ESTIMATE_SHORT_ID, CL_ESTIMATE_SHORT_KIND, CL_ESTIMATE_SHORT_NAME, CL_ESTIMATE_SHORT_CURRENT_DATE, CL_ESTIMATE_SHORT_DATE, CL_ESTIMATE_SHORT_ADDRESS, CL_ESTIMATE_SHORT_PROGRESS, USER_DEVICE_REGISTERS_PHONE FROM CL_ESTIMATE_SHORT
                    LEFT JOIN USER_DEVICE_REGISTERS ON CL_ESTIMATE_SHORT.CL_ESTIMATE_SHORT_FK_USER = USER_DEVICE_REGISTERS.USER_DEVICE_REGISTERS_ID WHERE  CL_ESTIMATE_SHORT_PUBLIC = 1';
            if($category == 1){
                $query .= ' AND CL_ESTIMATE_SHORT_KIND = 0 OR CL_ESTIMATE_SHORT_KIND = 1';
            }elseif($category == 2){
                $query .= ' AND CL_ESTIMATE_SHORT_KIND = 2 OR CL_ESTIMATE_SHORT_KIND = 3';
            }
            if($lastId != -1){
                $query .= ' AND CL_ESTIMATE_SHORT_ID < '.$lastId.'';
            }
            $query .= ' ORDER BY CL_ESTIMATE_SHORT_ID DESC LIMIT '.$itemSize.', '.$nextSize.';';
            $query = DB::select( DB::raw($query) );
            for($i=0; $i<count($query); $i++){
                $form = array();
                $form['shortId'] = $query[$i]->CL_ESTIMATE_SHOROT_ID;
                $form['kind'] = $query[$i]->CL_ESTIMATE_SHOROT_KIND;
                $form['name'] = $query[$i]->CL_ESTIMATE_SHOROT_NAME;
                $form['date'] = $query[$i]->CL_ESTIMATE_SHOROT_CURRENT_DATE;
                $form['cleaning_date'] = $query[$i]->CL_ESTIMATE_SHOROT_DATE;
                $form['address'] = $query[$i]->CL_ESTIMATE_SHOROT_ADDRESS;
                $form['authority'] = true;
                $form['progress'] = $query[$i]->CL_ESTIMATE_SHOROT_PROGRESS;
                if($form['progress'] == 1){
                    $form['phone'] = $query[$i]->USER_DEVICE_REGISTERS_PHONE;
                    if(substr($form['phone'], 0, 1) == '+'){
                        $form['phone'] = '0'.substr($form['phone'], 3);
                    }
                }else{
                    $form['phone'] = "";
                }
                array_push($data['estimate'], $form);
            }
        }elseif($state === "user"){
            $query = 'SELECT CL_ESTIMATE_SHORT_ID, CL_ESTIMATE_SHORT_KIND, CL_ESTIMATE_SHORT_NAME, CL_ESTIMATE_SHORT_CURRENT_DATE, CL_ESTIMATE_SHORT_DATE, CL_ESTIMATE_SHORT_ADDRESS, CL_ESTIMATE_SHORT_PROGRESS FROM CL_ESTIMATE_SHORT
                    WHERE  CL_ESTIMATE_SHORT_PUBLIC = 1';
            if($category == 1){
                $query .= ' AND CL_ESTIMATE_SHORT_KIND = 0 OR CL_ESTIMATE_SHORT_KIND = 1';
            }elseif($category == 2){
                $query .= ' AND CL_ESTIMATE_SHORT_KIND = 2 OR CL_ESTIMATE_SHORT_KIND = 3';
            }
            if($lastId != -1){
                $query .= ' AND CL_ESTIMATE_SHORT_ID < '.$lastId.'';
            }
            $query .= ' ORDER BY CL_ESTIMATE_SHORT_ID DESC LIMIT '.$itemSize.', '.$nextSize.';';
            $query = DB::select( DB::raw($query) );
            for($i=0; $i<count($query); $i++){
                $form = array();
                $form['shortId'] = $query[$i]->CL_ESTIMATE_SHORT_ID;
                $form['kind'] = $query[$i]->CL_ESTIMATE_SHORT_KIND;
                $form['name'] = mb_substr($query[$i]->CL_ESTIMATE_SHORT_NAME, 0, 1, "UTF-8")."**";
                $form['date'] = $query[$i]->CL_ESTIMATE_SHORT_CURRENT_DATE;
                $form['cleaning_date'] = $query[$i]->CL_ESTIMATE_SHORT_DATE;
                $form['address'] = $query[$i]->CL_ESTIMATE_SHORT_ADDRESS;
                $form['authority'] = true;
                $form['progress'] = $query[$i]->CL_ESTIMATE_SHORT_PROGRESS;
                $form['phone'] = "";
                array_push($data['estimate'], $form);
            }
        }

        return response()->json(array('data' => $data));
    }

    function CLEstimateInsert(Request $request){

        $cleaning_date = $request->cleaning_date;
        $kind = $request->kind;
        $clean_customer_name = $request->clean_customer_name;
        $clean_address = $request->clean_address;
        $clean_room_size = $request->clean_room_size;
        $id = $request->id;


        DB::beginTransaction();

        $time = date("YmdHis");

        $cleaning_date = str_replace(" ", "", $cleaning_date);

        $pattern = '/[\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]+/u';

        $cleaning_date = preg_replace($pattern, ".", $cleaning_date);

        $aaa = explode(".", $cleaning_date);

        $aaa[0] = substr($aaa[0], 2, 2);

        if(strlen($aaa[1]) == 1){
            $aaa[1] = "0".$aaa[1];
        }
        if(strlen($aaa[2]) == 1){
            $aaa[2] = "0".$aaa[2];
        }
        $cleaningDate = $aaa[0].".".$aaa[1].".".$aaa[2];


//        $cleaningDate = $year.".".$month.".".$date;

        $year = substr($time, 2, 2);
        $month = substr($time, 4, 2);
        $date = substr($time, 6, 2);
        $shortTime = $year.".".$month.".".$date;

        $query = 'INSERT INTO CL_ESTIMATE_SHORT VALUES (NULL, "'.$shortTime.'", '.$kind.', "'.$clean_customer_name.'",
        "'.$cleaningDate.'", "'.$clean_address.'", '.$clean_room_size.', 1, 1, '.$id.', NOW())';
        $query = DB::statement( DB::raw( $query ) );

//        $query = 'INSERT INTO CL_ESTIMATE_FK_SUB VALUES ('.$list['id'].', (SELECT CL_ESTIMATE_SHORT_ID FROM CL_ESTIMATE_SHORT ORDER BY CL_ESTIMATE_SHORT_ID DESC LIMIT 0, 1))';
//        $mySql = $this->sendQuery($mySql, $query);
        DB::commit();

        $_SESSION['clean'] = $id;

        $query = "SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID = '".$id."'";
        $query = DB::select( DB::raw( $query ) );


        $this->CLEstimateEnd($query[0]->USER_DEVICE_REGISTERS_PHONE);

        $addr = explode(" ", trim($clean_address));

//        if($addr[0] == "서울특별시" || $addr[0] == "경기도" || $addr[0] == "인천광역시" || $addr[0] == "부산광역시"){
//            $this->CLCompany('01066681115');
//            $this->CLCompany('01099486048');
//        } else {
//            $this->CLCompany('01099486048');
//        }

        return response()->json(array('data' => true));
    }

    function CLEstimateEnd($PhoneNumber){

        $PhoneNumber = str_replace("+82", "0", $PhoneNumber);

        $SendText = "[청소견적%20등록완료]%0A%0A이사모아에%20정식%20가입된%20청소전문업체가%203일%20이내로%20상담전화%20드립니다♡";
        $url = "http://jycadmin.24all.co.kr/Source/controller/SMSController.php?numberList=".$PhoneNumber."&sender=16702477&info=".$SendText;
        $ch=curl_init(); //파라미터:url -선택사항

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        curl_close($ch);
    }

    function CLEstimateUpdate(Request $request){

        $cleaning_date = $request->cleaning_date;
        $kind = $request->kind;
        $clean_customer_name = $request->clean_customer_name;
        $clean_address = $request->clean_address;
        $clean_room_size = $request->clean_room_size;
        $id = $request->id;
        $shortId = $request->short_id;

        $dateList = explode(".", $cleaning_date);
        $year = substr($dateList[0], -2, 2);
        $month = $dateList[1];
        $date = $dateList[2];
        $cleaningDate = $year.".".$month.".".$date;

        DB::beginTransaction();

        $query = 'UPDATE CL_ESTIMATE_SHORT SET CL_ESTIMATE_SHORT_KIND = '.$kind.', CL_ESTIMATE_SHORT_NAME = "'.$clean_customer_name.'",
        CL_ESTIMATE_SHORT_SIZE = '.$clean_room_size.', CL_ESTIMATE_SHORT_ADDRESS = "'.$clean_address.'",
        CL_ESTIMATE_SHORT_DATE = "'.$cleaningDate.'" WHERE CL_ESTIMATE_SHORT_ID = '.$shortId.';';
        $query = DB::statement( DB::raw( $query ) );

        if($query){
            DB::commit();
            return response()->json(array('data' => true));
        } else {
            DB::rollback();
            return response()->json(array('data' => false));
        }

    }

    function CLEstimateModify(Request $request){

        $id = $request->id;

        $query = 'SELECT CL_ESTIMATE_SHORT_KIND, CL_ESTIMATE_SHORT_NAME, CL_ESTIMATE_SHORT_SIZE, CL_ESTIMATE_SHORT_ADDRESS,
        CL_ESTIMATE_SHORT_DATE FROM CL_ESTIMATE_SHORT WHERE CL_ESTIMATE_SHORT_ID = '.$id.';';
        $query = DB::select( DB::raw( $query ) );

        $data['info'] = array();
        $data['success'] = false;

        if(count($query)){
            $form = array();
            for($i=0; $i<count($i); $i++){
                $form['kind'] = $query[$i]->CL_ESTIMATE_SHORT_KIND;
                $form['name'] = $query[$i]->CL_ESTIMATE_SHORT_NAME;
                $form['size'] = $query[$i]->CL_ESTIMATE_SHORT_SIZE;
                $form['address'] = $query[$i]->CL_ESTIMATE_SHORT_ADDRESS;
                $form['cleaning_date'] = $query[$i]->CL_ESTIMATE_SHORT_DATE;
                array_push($data['info'], $form);
            }

            $data['success'] = true;

            return response()->json(array('data' => $data));
        } else {
            return response()->json(array('data' => false));
        }
    }
}
