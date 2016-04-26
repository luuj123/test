<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;

class AUser extends Controller
{

    public function RegisterSet(Request $request){

        $ip = $request->ip;
        $sender_id = $request->sender;
        $userPhone = $request->userPhone;
        $push_id = $request->push_id;

        $userPhone = str_replace("+82", "0", $userPhone);

        if(empty($userPhone)){
            return response()->json(array('data' => false));
        }

        $query = "SELECT USER_DEVICE_REGISTERS_ID, replace(USER_DEVICE_REGISTERS_PHONE,'+82','0') AS PHONE, USER_DEVICE_REGISTERS_COMPANY, MV_LIST_ID, ESTIMATE_COMPANY_FK_USER
                FROM USER_DEVICE_REGISTERS
                left join MV_LIST ON MV_LIST_PRIVATE_PHONE='".$userPhone."' AND MV_LIST.USEYN='Y'
                left join ESTIMATE_COMPANY ON ESTIMATE_COMPANY_FK_LIST=MV_LIST_ID
                WHERE USER_DEVICE_REGISTERS_PHONE = '".$userPhone."' order by USER_DEVICE_REGISTERS_ID desc limit 1;";
        $query = DB::select( DB::raw( $query ) );

        $mv_id = 0;

        if(count($query)){
            $mv_id = $query[0]->MV_LIST_ID;
            if(!empty($query[0]->MV_LIST_ID)){
                $CompanyID = $query[0]->MV_LIST_ID;
                $UserID = $query[0]->USER_DEVICE_REGISTERS_ID;
                if(empty($query[0]->ESTIMATE_COMPANY_FK_USER)){
                    $CompanyID = $query[0]->MV_LIST_ID;
                    $UserID = $query[0]->USER_DEVICE_REGISTERS_ID;
                    $query = "UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_COMPANY = 2 WHERE USER_DEVICE_REGISTERS_ID=".$UserID;
                    $query = DB::statement( DB::raw( $query ) );
                    $query = "INSERT INTO ESTIMATE_COMPANY VALUES (NULL, ".$UserID.", ".$CompanyID.")";
                    $query = DB::statement( DB::raw( $query ) );
                    $query = "INSERT INTO MV_POINT VALUES (NULL, 1, 0, 0, ".$CompanyID.", NOW())";
                    $query = DB::statement( DB::raw( $query ) );
                    $time = date("YmdHis");
                    $query = "INSERT INTO MV_PAYMENT VALUES (NULL, 1, 0, 0, '가입', 0, '".$time."', ".$CompanyID.", (SELECT MV_POINT_ID FROM MV_POINT WHERE MV_POINT_FK_MOVE = ".$CompanyID."), NOW())";
                    $query = DB::statement( DB::raw( $query ) );
                    //신 내역 저장
                    $query = "INSERT INTO POINT_TOTAL (UserID, Category, inPoint, Money, Gubun, Content, MV_CL, RegDate, MngCode)
                  VALUES (".$CompanyID.", 'PO_IN04', 1, 0, 'F', '가입', 'mv', NOW(), ".$_SESSION["ADMIN_ID"].");";
                    $query = DB::statement( DB::raw( $query ) );
                } else {
                    if(!($query[0]->ESTIMATE_COMPANY_FK_USER == $query[0]->USER_DEVICE_REGISTERS_ID)){
                        $query = "UPDATE ESTIMATE_COMPANY SET ESTIMATE_COMPANY_FK_USER=".$UserID." WHERE ESTIMATE_COMPANY_FK_LIST=".$CompanyID;
                        $query = DB::statement( DB::raw( $query ) );
                    }
                }
            }
        }


        $query = 'SELECT USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_SENDER, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_VALUE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = "'.$userPhone.'" order by USER_DEVICE_REGISTERS_ID desc limit 1;';
        $query = DB::select( DB::raw($query) );

        if(empty($query)){
            $value = 3;

            $query = 'INSERT INTO USER_DEVICE_REGISTERS(USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_PHONE, USER_DEVICE_REGISTERS_NAME, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_REGDATE, USER_DEVICE_REGISTERS_SENDER)
                    VALUES ( NULL, "'.$push_id.'", "'.$userPhone.'", NULL, '.$value.', NOW(), "'.$sender_id.'");';
            $query = DB::statement( DB::raw($query) );
            $query = 'SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = "'.$userPhone.'";';
            $query = DB::select( DB::raw($query) );
            $query = 'INSERT INTO ALARM_SWITCH VALUES (NULL, '.$query[0]->USER_DEVICE_REGISTERS_ID.', 1);';
            $query = DB::statement( DB::raw($query) );

            $query = 'SELECT USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_SENDER, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_VALUE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = "'.$userPhone.'" order by USER_DEVICE_REGISTERS_ID desc limit 1;';
            $query = DB::select( DB::raw($query) );

            $form = array();
            $form['companyState'] = 3;
            $form['request'] = false;
            return response()->json(array('data' => $form));
        }

        $id = $query[0]->USER_DEVICE_REGISTERS_ID;
        $type = $query[0]->USER_DEVICE_REGISTERS_COMPANY;

        if($query[0]->USER_DEVICE_REGISTERS_COMPANY == 4){
            if($query[0]->USER_DEVICE_REGISTERS_SENDER == $sender_id){
                if(!($query[0]->USER_DEVICE_REGISTERS_VALUE == $push_id)){
                    $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_VALUE ="'.$push_id.'" WHERE USER_DEVICE_REGISTERS_ID = '.$query[0]->USER_DEVICE_REGISTERS_ID.' AND USER_DEVICE_REGISTERS_COMPANY=4;';
                    $query = DB::statement( DB::raw($query) );
                }

            } else {
                if(!$query[0]->USER_DEVICE_REGISTERS_VALUE == $push_id){
                    $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_VALUE ="'.$push_id.'", USER_DEVICE_REGISTERS_SENDER="'.$sender_id.'" WHERE USER_DEVICE_REGISTERS_ID = '.$query[0]->USER_DEVICE_REGISTERS_ID.' AND USER_DEVICE_REGISTERS_COMPANY=4;';
                    $query = DB::statement( DB::raw($query) );
                }
            }

        } else if($query[0]->USER_DEVICE_REGISTERS_COMPANY == 3){
            $query = 'SELECT ID FROM COMPANY_JOIN_FORM WHERE REPLACE(COMPANY_PHONE, "-", "")="'.$userPhone.'";';
            $query = DB::select( DB::raw($query) );

            if(!empty($query)){
                $form = array();
                $form['companyState'] = 3;
                $form['request'] = true;
                return response()->json(array('data' => $form));
            } else {
                $form = array();
                $form['companyState'] = 3;
                $form['request'] = false;
                return response()->json(array('data' => $form));
            }
        } else {
            if($query[0]->USER_DEVICE_REGISTERS_ID == null || !isset($query[0]->USER_DEVICE_REGISTERS_ID)){
                $value = 3;
                if(isset($ip)){
                    $query = 'SELECT COUNT(*) AS COUNT FROM SS_VISIT WHERE IP = "'.$ip.'";';
                    $query = DB::select( DB::raw($query) );

                    if($query[0]->COUNT == 1){
                        $value = 3;
                    }
                }
//            $query = 'INSERT INTO USER_DEVICE_REGISTERS(USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_PHONE, USER_DEVICE_REGISTERS_NAME, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_REGDATE)
//                    VALUES ( NULL, "'.$registerId.'", "'.$userPhone.'", NULL, '.$value.', NOW());';
                $query = 'INSERT INTO USER_DEVICE_REGISTERS(USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_PHONE, USER_DEVICE_REGISTERS_NAME, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_REGDATE, USER_DEVICE_REGISTERS_SENDER)
                    VALUES ( NULL, "'.$push_id.'", "'.$userPhone.'", NULL, '.$value.', NOW(), "'.$sender_id.'");';
                $query = DB::statement( DB::raw($query) );
                $query = 'SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = "'.$userPhone.'";';
                $query = DB::select( DB::raw($query) );
                $query = 'INSERT INTO ALARM_SWITCH VALUES (NULL, '.$query[0]->USER_DEVICE_REGISTERS_ID.', 1);';
                $query = DB::statement( DB::raw($query) );
            }else{
//            $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_VALUE ="'.$registerId.'" WHERE USER_DEVICE_REGISTERS_ID = '.$row[0].';';

                if(($query[0]->USER_DEVICE_REGISTERS_SENDER == "480942233848" || $query[0]->USER_DEVICE_REGISTERS_SENDER == "643222627340") && $type == "2"){
                    $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_COMPANY ="1" WHERE USER_DEVICE_REGISTERS_ID = '.$query[0]->USER_DEVICE_REGISTERS_ID.';';
                    $query = DB::statement( DB::raw($query) );

                    $query = 'INSERT INTO USER_DEVICE_REGISTERS(USER_DEVICE_REGISTERS_ID, USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_PHONE, USER_DEVICE_REGISTERS_NAME, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_REGDATE, USER_DEVICE_REGISTERS_SENDER)
                    VALUES ( NULL, "'.$push_id.'", "'.$userPhone.'", NULL, 4, NOW(), "'.$sender_id.'");';
                    $query = DB::statement( DB::raw($query) );

                    $query = 'SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = "'.$userPhone.'" AND USER_DEVICE_REGISTERS_COMPANY=4;';
                    $query = DB::select( DB::raw($query) );
                    $newId = $query[0]->USER_DEVICE_REGISTERS_ID;

                    $query = 'UPDATE ESTIMATE_COMPANY SET ESTIMATE_COMPANY_FK_USER='.$newId.' WHERE ESTIMATE_COMPANY_FK_USER='.$id;
                    $query = DB::statement( DB::raw($query) );

                    $query = 'INSERT INTO ALARM_SWITCH VALUES (NULL, '.$newId.', 1);';
                    $query = DB::statement( DB::raw($query) );

                    $id = $newId;
                } else {
                    $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_VALUE ="'.$push_id.'", USER_DEVICE_REGISTERS_SENDER="'.$sender_id.'" WHERE USER_DEVICE_REGISTERS_ID = '.$query[0]->USER_DEVICE_REGISTERS_ID.' AND USER_DEVICE_REGISTERS_COMPANY=4;';
                    $query = DB::statement( DB::raw($query) );
                }

            }
        }


        return $this->findUserRegister($id, $mv_id);


//        return response()->json(array('data' => $query));
    }

    function findUserRegister($id, $mv_id){

        $data = array();
        $data['userId'] = $id;
        $data['companyId'] = -1;

        $query = 'SELECT VALUE FROM ALARM_SWITCH WHERE FK_USER_DEVICE_ID = '.$id.';';
        $query = DB::select( DB::raw($query) );

        if(!isset($query[0]->VALUE)){
            $query = 'INSERT INTO ALARM_SWITCH VALUES (NULL, '.$id.', 1);';
            $query = DB::statement( DB::raw($query) );
        }

        $query = 'SELECT ESTIMATE_SHORT_DATE FROM ESTIMATE_SHORT ORDER BY ESTIMATE_SHORT_ID DESC LIMIT 0, 1;';
        $query = DB::select( DB::raw($query) );

        if(!empty($query)){
            $data['estimate'] = $query[0]->ESTIMATE_SHORT_DATE;
        }

        $query = 'SELECT NOTICE_BOARD_TITLE_DATE FROM NOTICE_BOARD_TITLE ORDER BY NOTICE_BOARD_TITLE_ID DESC LIMIT 0, 1;';
        $query = DB::select( DB::raw($query) );

        if(!empty($query)){
            $data['notice'] = $query[0]->NOTICE_BOARD_TITLE_DATE;
        }

        $query = 'SELECT USER_DEVICE_REGISTERS_COMPANY FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID = '.$id.';';
//        $query = 'SELECT USER_DEVICE_REGISTERS_COMPANY, MV_LIST_TYPE
//                FROM USER_DEVICE_REGISTERS
//                LEFT JOIN ESTIMATE_COMPANY ON ESTIMATE_COMPANY_FK_USER='.$id.'
//                LEFT JOIN MV_LIST ON MV_LIST_ID=ESTIMATE_COMPANY_FK_LIST
//                WHERE USER_DEVICE_REGISTERS_ID = '.$id.';';
        $query = DB::select( DB::raw($query) );

        if(!empty($query)){
            if($query[0]->USER_DEVICE_REGISTERS_COMPANY == 4){

                $query = 'SELECT USEYN FROM MV_LIST WHERE USEYN="Y" AND MV_LIST_ID = (SELECT ESTIMATE_COMPANY_FK_LIST FROM ESTIMATE_COMPANY WHERE ESTIMATE_COMPANY_FK_USER='.$id.' AND ESTIMATE_COMPANY_FK_LIST='.$mv_id.');';
                $query = DB::select( DB::raw( $query ) );

                if(count($query)){
                    if($query[0]->USEYN == "N"){
                        $data['state'] = "user";
                        $data['type'] = "user";

                        return response()->json(array('data' => $data));
                    }
                }

                $data['companyState'] = 4;
                $data['state'] = "company";
                $query = 'SELECT ESTIMATE_COMPANY_FK_LIST, (SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID=ESTIMATE_COMPANY_FK_LIST) AS TYPE FROM ESTIMATE_COMPANY WHERE ESTIMATE_COMPANY_FK_USER = '.$id.';';
                $query = DB::select( DB::raw($query) );

                if($query[0]->ESTIMATE_COMPANY_FK_LIST != NULL && isset($query[0]->ESTIMATE_COMPANY_FK_LIST)){
                    $data['companyId'] = $query[0]->ESTIMATE_COMPANY_FK_LIST;
                    $type = $query[0]->TYPE;

                    $data['type'] = "move";

                    if($type == 0 || $type == 1){
                        $data['accessPermission'] = "move";
                    } else if($type == 2){
                        $data['accessPermission'] = "deliver";
                    } else if($type == 3 || $type ==4){
                        $data['accessPermission'] = "all";
                    }

                }else{
                    $query = 'SELECT CL_ESTIMATE_COMPANY_FK_LIST FROM CL_ESTIMATE_COMPANY WHERE CL_ESTIMATE_COMPANY_FK_USER = '.$id.';';
                    $query = DB::select( DB::raw($query) );

                    if($query[0]->CL_ESTIMATE_COMPANY_FK_LIST != NULL && isset($query[0]->CL_ESTIMATE_COMPANY_FK_LIST)){
                        $data['companyId'] = $query[0]->CL_ESTIMATE_COMPANY_FK_LIST;
                        $data['type'] = "clean";
                    }
                }
            } else if($query[0]->USER_DEVICE_REGISTERS_COMPANY == 3){
                $form = array();
                $form['companyState'] = 3;
                $form['request'] = false;
                return response()->json(array('data' => $form));
            } else{
                $data['state'] = "user";
                $data['type'] = "user";
            }
        }

        return response()->json(array('data' => $data));
    }

    public function RegisterGet(Request $request){
        $actionData = $request->actionData;

        return $this->findUserRegister($actionData, '0');
    }

    public function RegisterUpdate(Request $request){
        $uesrId = $request->data;
        $registerId = $request->actionData;

        $query = 'UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_VALUE ="'.$registerId.'" WHERE USER_DEVICE_REGISTERS_ID = '.$userId.';';
        DB::statement( DB::raw($query) );

        if($query){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }

    function UserCall(Request $request){
        $companyId = $request->companyId;
        $priority = $request->priority;

        $query = 'SELECT USEYN FROM MV_LIST WHERE MV_LIST_ID='.$companyId;
        $query = DB::select( DB::raw( $query ) );

        if(count($query)){

            if($query[0]->USEYN == "N"){
                return response()->json(array('data' => false));
            }
        }

        DB::beginTransaction();

        $time = date("YmdHis");
        $year = (int)substr($time, 0, 4);
        $month = (int)substr($time, 4, 2);

        $query = 'SELECT MV_CALLED_ID FROM MV_CALLED WHERE MV_CALLED_YEAR = '.$year.' AND MV_CALLED_MONTH = '.$month.' AND MV_CALLED_FK_LIST = '.$companyId.';';
        $query = DB::select( DB::raw($query) );
        if(!empty($query)){
            $query = 'UPDATE MV_CALLED SET MV_CALLED_COUNT = MV_CALLED_COUNT + 1 WHERE MV_CALLED_ID = '.$query[0]->MV_CALLED_ID.';';
        }else{
            $query = 'INSERT INTO MV_CALLED VALUES (NULL, '.$year.', '.$month.', 1, '.$companyId.');';
        }
        DB::statement( DB::raw($query) );

        $query = 'INSERT INTO MV_CALLED_TIME (MV_LIST_ID, REGDATE) VALUES ( '.$companyId.', NOW());';
        DB::statement( DB::raw($query) );

        $query = 'UPDATE MV_LIST SET MV_LIST_CALLED = MV_LIST_CALLED + 1 WHERE MV_LIST_ID = '.$companyId.';';
        DB::statement( DB::raw($query) );

        if($priority == 0 || $priority == 1){
            $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES ('.$companyId.', "PO_OUT01", 0, 0, "소비자 콜/추천업체", "MV", NOW(), 0);';
            DB::statement( DB::raw($query) );

            DB::commit();
            return response()->json(array('data' => true));
        } else {
            $query = 'SELECT MV_POINT_FREE, MV_POINT_CASH FROM MV_POINT WHERE MV_POINT_FK_MOVE = '.$companyId.';';
            $query = DB::select( DB::raw($query) );
//            var_dump($query);
            if(!empty($query)){
                $free = $query[0]->MV_POINT_FREE;
                $cash = $query[0]->MV_POINT_CASH;

                if(($free + $cash) < 5){
                    DB::rollback();

                    return response()->json(array('data' => 'point_lack'));
                }

                $free -= 5;
                if(!$this->checkFreeAndCashPoint($free, 0)){
                    $cash += $free;
                    if(!$this->checkFreeAndCashPoint($cash, 0)){
                        $cash = 0;
                    }
                    $free = 0;
                }

//            if($priority == 0 || $priority == 1){
//                $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES ('.$companyId.', "PO_OUT01", 0, 0, "소비자 콜/추천업체", "MV" NOW(), 0);';
//                $mySql = $this->sendQuery($mySql, $query);
//            } else {
//                $query = 'UPDATE MV_POINT SET MV_POINT_FREE = '.$free.', MV_POINT_CASH = '.$cash.' WHERE MV_POINT_FK_MOVE = '.$companyId.';';
//                $this->sendQuery($mySql, $query);
//
//                $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES ('.$companyId.', "PO_OUT01", 5, 0, "소비자 콜", "MV", NOW(), 0);';
//                $mySql = $this->sendQuery($mySql, $query);
//            }

//                $query = 'UPDATE MV_POINT SET MV_POINT_FREE = '.$free.', MV_POINT_CASH = '.$cash.' WHERE MV_POINT_FK_MOVE = '.$companyId.';';
//                $this->sendQuery($mySql, $query);

                $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES ('.$companyId.', "PO_OUT01", 0, 0, "소비자 콜", "MV", NOW(), 0);';
                DB::statement( DB::raw($query) );

                DB::commit();
                return response()->json(array('data' => true));
            }else{
                DB::rollback();
                return response()->json(array('data' => false));
            }
        }
    }

    function  UserAppVersionVisitChk(Request $request){
        $UserID = $request->UserID;
        $UserPhone = $request->UserPhone;
        $AppVersion = $request->AppVersion;
        $OsVersion = $request->OsVersion;
        $WhichUser = $request->WhichUser;

        $query = "SELECT COUNT(1) AS COUNT FROM USER_APP_VERSION WHERE USERID = '".$UserID."'";
        $query = DB::select( DB::raw( $query ) );

        if($query[0]->COUNT > 0){
            $query = 'UPDATE USER_APP_VERSION SET APPVERSION = "'.$AppVersion.'", OS_VERSION = "'.$OsVersion.'", REGDATE = NOW() WHERE USERID = "'.$UserID.'"';
        }elseif($query[0]->COUNT < 1){
            $query = 'INSERT INTO USER_APP_VERSION (USERID, USER_PHONE, APPVERSION, REGDATE, WHICH_APP, OS_VERSION, WHICH_USER) VALUES ("'.$UserID.'", "'.$UserPhone.'", "'.$AppVersion.'", NOW(), "0",  "'.$OsVersion.'", "'.$WhichUser.'")';
        }
        $query = DB::statement( DB::raw( $query ) );

        $query = "INSERT INTO USER_ACCESS (USERID, REGDATE, APP_GUBUN, USER_GUBUN) VALUES (".$UserID.", NOW(), '24MOA', 'COMPANY')";
        $query = DB::statement( DB::raw( $query ) );

        if($query){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }


    function checkFreeAndCashPoint($value, $isNewCompany){
        if($value < 0 && $isNewCompany == 0){
            return false;
        }
        return true;
    }

    public function PhoneUpdate(Request $request){
        $data = $request->data;
        $actionData = $request->actionData;
    }
}
