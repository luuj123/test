<?php
/**
 * Created by PhpStorm.
 * User: s-huyn
 * Date: 2015-12-21
 * Time: 오전 11:14
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\CODE;
use App\CODE_AREA;

class CommonController extends Controller {
    //전화번호 자르기
    public static function PhoneCut($PhoneNum){
        // 전화번호의 숫자만 취한 후 중간에 하이픈(-)을 넣는다.
        $tel = preg_replace("/[^0-9]/", "", $PhoneNum);
        // 숫자 이외 제거
        if (substr($tel,0,2)=='02')
            return preg_replace("/([0-9]{2})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $tel);
        else if (strlen($tel)=='8' && (substr($tel,0,2)=='15' || substr($tel,0,2)=='16' || substr($tel,0,2)=='18'))
            // 지능망 번호이면
            return preg_replace("/([0-9]{4})([0-9]{4})$/", "\\1-\\2", $tel);
        else
            return preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/", "\\1-\\2-\\3", $tel);
    }

    //사업자 번호
    public static function BiznoCut($val){
        $tmpString = substr($val,0,3)."-".substr($val,3,2)."-".substr($val,5,5);
        return $tmpString;
    }

    public static function UserNameChk($UserName){ //이름 구분
        $tmpName = "";
        if($UserName==null){
            $tmpName = "앱";
        } else {
            $tmpName = str_replace("M","",$UserName);
        }
        return $tmpName;
    }

    public static function CM_Kind($Category, $KindNum){
        $kindResult = "";
        if($KindNum == 0){
            $KindResult = $Category == "M" ? "가정" : "입주";
        }else if($KindNum == 1){
            $KindResult = $Category == "M" ? "원룸" : "이사";
        }else if($KindNum == 2){
            $KindResult = $Category == "M" ? "사무실" : "거주";
        }else if($KindNum == 3){
            $KindResult = $Category == "M" ? "보관" : "부분";
        }
        return $Category = "M" ? $KindResult."이사" : $KindResult."청소";
    }

    function GetCode(Request $request){
        $SelectCode = $request->SelectCode;
        $OrderColumn = $request->OrderColumn;
        $OrderWay = $request->OrderWay;

        if($SelectCode == "CC"){
            $query = CODE::selectRaw("CODE_NAME, CODE_VALUE, PARENT_CODE")
                ->where("PARENT_CODE", "=", $SelectCode)->orderBy($OrderColumn,$OrderWay)->get();
        }else{
            $query = CODE::selectRaw("CODE_NAME, CODE_VALUE, PARENT_CODE")
                ->where("PARENT_CODE", "=", $SelectCode)->orderBy($OrderColumn,$OrderWay)->get();
        }
        return response()->json( array( "data" => $query ) );
    }

    function GetAreaCode(Request $request){
        $Parent = $request->Parent;

        $query = "SELECT CODE_VALUE, CODE_NAME, PARENT_CODE FROM CODE_AREA WHERE CODE_NAME NOT LIKE '%출장%'";

        if($Parent != ""){
            $query .= " AND PARENT_CODE=".$Parent;
        }else{
            $query .= " AND PARENT_CODE IS NULL";
        }
        $query .= " ORDER BY CODE_NAME";

        $result = DB::select( DB::raw( $query ) );

        return response()->json( array( "data" => $result ) );
    }

    public static function startPushContent($shortId, $registerIds)
    {
        $senderKey = '643222627340';
        $apiKey = 'AIzaSyDs5xZ6Qn2CC3XJvF8bDaT4foBP-hUydhs';

        $headers = array('Content-Type:application/json; charset=UTF-8', 'Authorization:key=' . $apiKey);

        $list = array();
        $list['title'] = "새로운 견적이 도착했습니다.";

        $arr = array();
        $arr['data'] = array();
        $arr['data']['msg'] = $shortId . '&' . $list['title'] . '&202';
        $arr['registration_ids'] = array();

        $count = 0;
        foreach ($registerIds as $index => $value) {

            if($value == "APA91bFbQw5SKlTq0sdVysiyCItYnow3bpjFIPmng5PO4wKHYcWTglGxx3uwDSmvC69SbCRy8dF_nZfiG8IAfiJ1d7kcUPqoo94PP7LMzhzJqrni37sYqHDLqbdpr4KmHgKmFA20eQK6"){

            } else {
                $arr['registration_ids'][$count] = $value;
                error_log($value.' '.$shortId);
            }

            $count++;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));

        $response = curl_exec($ch);

        error_log("test" . $response);

        curl_close($ch);

        $result = json_decode($response,1);
        $status[] = $result;
        for($i = 0; $i < count($status[0]['results']); $i++){
            if(isset($status[0]['results'][$i]['message_id'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID,SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV', '".$shortId."', '".$apiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['message_id']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['message_id']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            if(isset($status[0]['results'][$i]['error'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID, SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV','".$shortId."', '".$apiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['error']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['error']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            $result = DB::statement(DB::raw($query));
        }

        return $response;
    }

    public static function startPushContentNew($shortId, $registerIds)
    {
        $newSenderKey = '480942233848';
        $newApiKey = 'AIzaSyB9NJW4-HInkcgkjagB3oq-nQ5qUmONsGs';

        $headers = array('Content-Type:application/json; charset=UTF-8', 'Authorization:key=' . $newApiKey);

        $list = array();
        $list['title'] = "새로운 견적이 도착했습니다.";

        $arr = array();
        $arr['data'] = array();
        $arr['data']['msg'] = $shortId . '&' . $list['title'] . '&202';
        $arr['registration_ids'] = array();

        $count = 0;
        foreach ($registerIds as $index => $value) {

            if($value == "APA91bFbQw5SKlTq0sdVysiyCItYnow3bpjFIPmng5PO4wKHYcWTglGxx3uwDSmvC69SbCRy8dF_nZfiG8IAfiJ1d7kcUPqoo94PP7LMzhzJqrni37sYqHDLqbdpr4KmHgKmFA20eQK6"){

            } else {
                $arr['registration_ids'][$count] = $value;
                error_log($value.' '.$shortId);
            }

            $count++;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));

        $response = curl_exec($ch);

        error_log("test" . $response);

        curl_close($ch);

        $result = json_decode($response,1);
        $status[] = $result;
        for($i = 0; $i < count($status[0]['results']); $i++){
            if(isset($status[0]['results'][$i]['message_id'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID, SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV','".$shortId."', '".$newApiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['message_id']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['message_id']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            if(isset($status[0]['results'][$i]['error'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID, SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV','".$shortId."', '".$newApiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['error']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['error']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            $result = DB::statement(DB::raw($query));
        }

        return $response;
    }

    public static function startPushContentMaster($shortId, $listId, $registerIds)
    {
        $masterSenderKey = '466875261460';
        $masterApiKey = 'AIzaSyDE49HqOF7g8cQnqaggLkOTKYqAL0-gQAk';

        $headers = array('Content-Type:application/json; charset=UTF-8', 'Authorization:key=' . $masterApiKey);

        $list = array();
        $list['title'] = "새로운 견적이 도착했습니다.";

        $arr = array();
        $arr['data'] = array();
//        $arr['data']['msg'] = $shortId . '&' . $list['title'] . '&202';

        $arr['data']['type'] = 'estimate';
        $arr['data']['gubun'] = 'move';
        $arr['data']['estimate_id'] = $listId;
        $arr['data']['short_id'] = $shortId;

        $arr['registration_ids'] = array();

        $count = 0;
        foreach ($registerIds as $index => $value) {

            if($value == "APA91bFbQw5SKlTq0sdVysiyCItYnow3bpjFIPmng5PO4wKHYcWTglGxx3uwDSmvC69SbCRy8dF_nZfiG8IAfiJ1d7kcUPqoo94PP7LMzhzJqrni37sYqHDLqbdpr4KmHgKmFA20eQK6"){

            } else {
                $arr['registration_ids'][$count] = $value;
                error_log($value.' '.$shortId);
            }

            $count++;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arr));

        $response = curl_exec($ch);

        error_log("test" . $response);

        curl_close($ch);

        $result = json_decode($response,1);
        $status[] = $result;
        for($i = 0; $i < count($status[0]['results']); $i++){
            if(isset($status[0]['results'][$i]['message_id'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID, SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV','".$shortId."', '".$masterApiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['message_id']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['message_id']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            if(isset($status[0]['results'][$i]['error'])){
                $query = "INSERT INTO PUSH_RESULT (GUBUN, ESTIMATE_ID, SENDER, GCM_KEY, REGDATE, RESULT, MULITCAST_ID)
                      VALUES ('MV','".$shortId."', '".$masterApiKey."','".$arr['registration_ids'][$i]."', NOW(), '".$status[0]['results'][$i]['error']."', '".$status[0]['multicast_id']."')";

                //$query = "UPDATE PUSH_RESULT SET REGDATE = NOW(), RESULT = '".$status[0]['results'][$i]['error']."', MULITCAST_ID = '".$status[0]['multicast_id']."' WHERE GCM_KEY = '".$arr['registration_ids'][$i]."'";
            }
            $result = DB::statement(DB::raw($query));
        }
        return $response;
    }


    public static function EstimateEnd($PhoneNumber){

        $PhoneNumber = str_replace("+82", "0", $PhoneNumber);

        $SendText = "[이사모아%20견적등록완료]%0A상담전화가%20곧%20올꺼에요^^%20가격/품질%20비교%20후%20만족스런%20이사하세요♡";
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
}