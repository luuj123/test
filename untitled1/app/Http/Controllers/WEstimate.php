<?php
/**
 * Created by PhpStorm.
 * User: s-huyn
 * Date: 2016-03-22
 * Time: 오후 4:16
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
date_default_timezone_set("Asia/Seoul");
class WEstimate extends Controller{
    function SetEstimate(Request $request){

        $senderKey = '643222627340';
        $apiKey = 'AIzaSyDs5xZ6Qn2CC3XJvF8bDaT4foBP-hUydhs';
        $newSenderKey = '480942233848';
        $newApiKey = 'AIzaSyB9NJW4-HInkcgkjagB3oq-nQ5qUmONsGs';
        $masterSenderKey = '466875261460';
        $masterApiKey = 'AIzaSyDE49HqOF7g8cQnqaggLkOTKYqAL0-gQAk';

        $StartAddress = $request->StartAddress;
        $EndAddress = $request->EndAddress;
        $RoomSize = $request->RoomSize;
        $StartFloor = $request->StartFloor;
        $EndFloor = $request->EndFloor;
        $MoveDate = $request->MoveDate;
        $People = $request->People;
        $UserName = $request->UserName;
        $UserPhone = $request->UserPhone;
        $CallCount = $request->CallCount;
        $ServiceType = $request->ServiceType;
        $Air = $request->Air;
        $Piano = $request->Piano;
        $StoneBad = $request->StoneBad;
        $TV = $request->TV;
        $wardrobe = $request->wardrobe;


        $time = date("YmdHis");
        $year = substr($MoveDate, 2, 2);
        $month = substr($MoveDate, 5, 2);
        $date = substr($MoveDate, 8, 2);
        $movingDate = $year.".".$month.".".$date;

        $GetUserID = "";

        $query = "SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS
                  WHERE USER_DEVICE_REGISTERS_PHONE = '".$UserPhone."' OR USER_DEVICE_REGISTERS_PHONE = '+82".substr($UserPhone, 1)."'";
        $UserID = DB::select(DB::raw($query));

        if(empty($UserID)){ //User 정보 없음
            $UserInsertQ = "INSERT INTO USER_DEVICE_REGISTERS (USER_DEVICE_REGISTERS_PHONE, USER_DEVICE_REGISTERS_NAME, USER_DEVICE_REGISTERS_COMPANY, USER_DEVICE_REGISTERS_REGDATE)
                            VALUES ('".$UserName."', '".$UserPhone."', 1, NOW())";
            $UserInsertR = DB::statement(DB::raw($UserInsertQ));

            $query = "SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = '".$UserPhone."'";
            $UserID = DB::select(DB::raw($query));
        }else{
            $UserUpdateQ = "UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_NAME = '".$UserName."' WHERE USER_DEVICE_REGISTERS_ID = '".$UserID[0]->USER_DEVICE_REGISTERS_ID."'";
            $UserUpdateR = DB::update(DB::raw($UserUpdateQ));
        }

        $GetUserID = $UserID[0]->USER_DEVICE_REGISTERS_ID;

        $ListInsertQ = "INSERT INTO ESTIMATE_LIST 
		(ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_CONTENT, ESTIMATE_LIST_START_LADDER, ESTIMATE_LIST_END_LADDER, ESTIMATE_LIST_KIND, ESTIMATE_LIST_PEOPLE, ESTIMATE_LIST_ROOM_SIZE, ESTIMATE_LIST_AIR, ESTIMATE_LIST_BED, ESTIMATE_LIST_TV, ESTIMATE_LIST_PIANO, ESTIMATE_LIST_WARDROBE, ESTIMATE_LIST_BIDDING, ESTIMATE_LIST_DATE, ESTIMATE_LIST_PHONE, ESTIMATE_LIST_FK_USER, ESTIMATE_LIST_REGDATE )
		VALUES
		('".$UserPhone."/".$time."', '', ".$StartFloor.", ".$EndFloor.", ".$ServiceType.", ".$People.", ".$RoomSize.", ".$Air.", ".$StoneBad.", ".$TV.", ".$Piano.", ".$wardrobe.", 1, '".$movingDate."', 1, ".$UserID[0]->USER_DEVICE_REGISTERS_ID.",NOW())";
        $ListInsertR = DB::statement(DB::raw($ListInsertQ));

        $GetListIDQ = 'SELECT ESTIMATE_LIST_ID FROM ESTIMATE_LIST WHERE ESTIMATE_LIST_FK_USER = '.$GetUserID.' ORDER BY ESTIMATE_LIST_ID DESC LIMIT 0, 1';
        $GetListID = DB::select(DB::raw($GetListIDQ));

        $year = substr($time, 2, 2);
        $month = substr($time, 4, 2);
        $date = substr($time, 6, 2);
        $shortTime = $year.".".$month.".".$date;

        $grade = rand(1, 10);

        $SetShortQ = 'INSERT INTO ESTIMATE_SHORT (ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
		VALUES (NULL, 0, 0, '.$CallCount.', '.$ServiceType.', "'.$shortTime.'", "'.$movingDate.'", 1, 1, '.$GetUserID.', '.$GetListID[0]->ESTIMATE_LIST_ID.', "", NOW(), '.$grade.');';
        $SetShortR = DB::statement(DB::raw($SetShortQ));

        $GetShortQ = 'SELECT ESTIMATE_SHORT_ID FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_FK_USER = '.$GetUserID.' ORDER BY ESTIMATE_SHORT_ID DESC LIMIT 0, 1;';
        $GetShortID = DB::select(DB::raw($GetShortQ));

        $SetRegiQ = 'INSERT INTO ESTIMATE_REGI VALUES (NULL, "", "", "", 2, '.$GetShortID[0]->ESTIMATE_SHORT_ID.');';
        $SetRegiR = DB::statement(DB::raw($SetRegiQ));

        $SetAddrQ = 'INSERT INTO ESTIMATE_ADDR VALUES (NULL, "'.$StartAddress.'", "'.$EndAddress.'", '.$GetShortID[0]->ESTIMATE_SHORT_ID.', '.$GetListID[0]->ESTIMATE_LIST_ID.');';
        $SetAddrR = DB::statement(DB::raw($SetAddrQ));

        //2016.03.14
        $startarray = explode(" ", trim($StartAddress));
        $startDo = $startarray[0];
        if (count($startarray) > 2) {
            $startSi = $startarray[1] . " " . $startarray[2];
        } else {
            $startSi = $startarray[1];
        }

        if($ServiceType == 0){
            $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo.'";';
            $SetRegiR = DB::update(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startSi.'";';
            $SetRegiR = DB::update(DB::raw($query));

            if($SetRegiR){
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startarray[0].'";';
                $SetRegiR = DB::update(DB::raw($query));
            }
        } else if($ServiceType == 1){
            $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo.'";';
            $SetRegiR = DB::update(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startSi.'";';
            $SetRegiR = DB::update(DB::raw($query));

            if($SetRegiR){
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startarray[0].'";';
                $SetRegiR = DB::update(DB::raw($query));
            }
        } else if($ServiceType == 2){
            $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo.'";';
            $SetRegiR = DB::update(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startSi.'";';
            $SetRegiR = DB::update(DB::raw($query));

            if($SetRegiR){
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startarray[0].'";';
                $SetRegiR = DB::update(DB::raw($query));
            }
        } else if($ServiceType == 3){
            $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo.'";';
            $SetRegiR = DB::update(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startSi.'";';
            $SetRegiR = DB::update(DB::raw($query));

            if($SetRegiR){
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "'.$startDo." ".$startarray[0].'";';
                $SetRegiR = DB::update(DB::raw($query));
            }
        }

        $startarray = explode(" ", trim($StartAddress));
        $startDo = $startarray[0];
        if (count($startarray) > 2) {
            $startSi = $startarray[1] . " " . $startarray[2];
        } else {
            $startSi = $startarray[1];
        }


        $endarray = explode(" ", trim($EndAddress));
        $endDo = $endarray[0];
        if (count($endarray) > 2) {
            $endSi = $endarray[1] . " " . $endarray[2];
        } else {
            $endSi = $endarray[1];
        }

        $company = array();
        $companyNew = array();
        $companyMaster = array();

        if ($ServiceType == 1) {
            $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
			LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
			LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
			LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
			WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
			GROUP BY MV_LIST.MV_LIST_ID;';
            $result = DB::select(DB::raw($query));
        } else {
            $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
			LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
			LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
			LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
			WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
			GROUP BY MV_LIST.MV_LIST_ID;';
            $result = DB::select(DB::raw($query));
        }

        if (count($result) > 0) {
            for($i = 0; $i < count($result); $i++){
                if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $newSenderKey){
                    array_push($companyNew, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                } else if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $masterSenderKey){
                    array_push($companyMaster, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                } else {
                    array_push($company, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                }
            }
        } else {
            $endarray = explode(" ", trim($endSi));

            if ($ServiceType == 1) {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
				LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
				LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
				LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
				WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
				GROUP BY MV_LIST.MV_LIST_ID;';
                $result = DB::select(DB::raw($query));
            } else {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
				LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
				LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
				LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
				WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
				GROUP BY MV_LIST.MV_LIST_ID;';
                $result = DB::select(DB::raw($query));
            }

            if (count($result) > 0) {
                for($i = 0; $i < count($result); $i++){
                    if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $newSenderKey){
                        array_push($companyNew, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $masterSenderKey){
                        array_push($companyMaster, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else {
                        array_push($company, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    }
                }
            }
        }


        if (!(($endDo . " " . $endSi) == ($startDo . " " . $startSi))) {
            if ($ServiceType == 1) {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
				LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
				LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
				LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
				WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
				GROUP BY MV_LIST.MV_LIST_ID;';
                $result = DB::select(DB::raw($query));
            } else {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
				LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
				LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
				LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
				WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
				GROUP BY MV_LIST.MV_LIST_ID;';
                $result = DB::select(DB::raw($query));
            }

            if (count($result) > 0) {
                for($i = 0; $i < count($result); $i++){
                    if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $newSenderKey){
                        array_push($companyNew, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $masterSenderKey){
                        array_push($companyMaster, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else {
                        array_push($company, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                    }
                }

                $company = array_unique($company);
                $companyNew = array_unique($companyNew);
                $companyMaster = array_unique($companyMaster);

                CommonController::startPushContent($GetShortID[0]->ESTIMATE_SHORT_ID, $company);
                CommonController::startPushContentNew($GetShortID[0]->ESTIMATE_SHORT_ID, $companyNew);
                CommonController::startPushContentMaster($GetShortID[0]->ESTIMATE_SHORT_ID, $GetListID[0]->ESTIMATE_LIST_ID, $companyMaster);
            } else {
                $startarray = explode(" ", trim($startSi));

                if ($ServiceType == 1) {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
					LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
					LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
					LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
					WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
					GROUP BY MV_LIST.MV_LIST_ID;';
                    $result = DB::select(DB::raw($query));
                } else {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
					LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
					LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
					LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
					WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
					GROUP BY MV_LIST.MV_LIST_ID;';
                    $result = DB::select(DB::raw($query));
                }
                if (count($result) > 0) {
                    for($i = 0; $i < count($result); $i++){
                        if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $newSenderKey){
                            array_push($companyNew, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else if($result[$i]->USER_DEVICE_REGISTERS_SENDER == $masterSenderKey){
                            array_push($companyMaster, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else {
                            array_push($company, $result[$i]->USER_DEVICE_REGISTERS_VALUE);
                        }
                    }

                    $company = array_unique($company);
                    $companyNew = array_unique($companyNew);
                    $companyMaster = array_unique($companyMaster);

                    CommonController::startPushContent($GetShortID[0]->ESTIMATE_SHORT_ID, $company);
                    CommonController::startPushContentNew($GetShortID[0]->ESTIMATE_SHORT_ID, $companyNew);
                    CommonController::startPushContentMaster($GetShortID[0]->ESTIMATE_SHORT_ID, $GetListID[0]->ESTIMATE_LIST_ID, $companyMaster);
                }
            }
        } else {
            $company = array_unique($company);
            $companyNew = array_unique($companyNew);
            $companyMaster = array_unique($companyMaster);

            CommonController::startPushContent($GetShortID[0]->ESTIMATE_SHORT_ID, $company);
            CommonController::startPushContentNew($GetShortID[0]->ESTIMATE_SHORT_ID, $companyNew);
            CommonController::startPushContentMaster($GetShortID[0]->ESTIMATE_SHORT_ID, $GetListID[0]->ESTIMATE_LIST_ID, $companyMaster);
        }
        CommonController::EstimateEnd($UserPhone);

		return response()->json( array( "data" => "true" ) );
    }
}