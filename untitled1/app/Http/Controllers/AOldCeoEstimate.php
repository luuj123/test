<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AOldCeoEstimate extends Controller{
	function MoveFullList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null){ $PageNo = 0; }
		$skip = ($PageNo)*25;
		$take = 25;

		$UserID = $request->UserID;
		$CompanyID = $request->CompanyID;

		$GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$CompanyID."'";
		$Typeresult = DB::select( DB::raw($GetTypeQuery) );

//		$query = "SELECT
//					T.*,
//					IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
//				FROM
//				(
//					select
//						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
//						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
//						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
//						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END
//					from ESTIMATE_SHORT AS ES
//					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
//					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date(ES.ESTIMATE_SHORT_REGDATE) > '2016-03-01' AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
//				) AS T
//				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
//				WHERE 1=1 ORDER BY ESTIMATE_SHORT_ID DESC LIMIT ".$skip.",".$take;

        $query = "SELECT
					T.*,
					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date_format(ES.ESTIMATE_SHORT_REGDATE, '%y.%m.%d') > '15.12.31' AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE != 'TE' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
				) AS T
				WHERE 1=1 order by ESTIMATE_SHORT_ID desc LIMIT ".$skip.",".$take;

		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form["listId"] = $result[$i]->ESTIMATE_ADDR_FK_LIST;
			$form["shortId"] = $result[$i]->ESTIMATE_SHORT_ID;
			$form["count"] = $result[$i]->ESTIMATE_SHORT_COUNT;

            if($Typeresult[0]->MV_LIST_GRADE >= $result[$i]->ESTIMATE_SHORT_GRADE){
                $form["replies"] = $result[$i]->ESTIMATE_SHORT_REPLIES;
            } else {
                $form["replies"] = $result[$i]->ESTIMATE_SHORT_MAX_REPLIES;
            }

			$form["max_replies"] = $result[$i]->ESTIMATE_SHORT_MAX_REPLIES;
			$form["kind"] = $result[$i]->ESTIMATE_SHORT_KIND;
			$form["date"] = $result[$i]->ESTIMATE_SHORT_DATE;
			$form["move_date"] = $result[$i]->ESTIMATE_SHORT_MOVE_DATE;
			$form["hasPhone"] = $result[$i]->ESTIMATE_SHORT_PHONE;
			$form["start_address"] = $result[$i]->ESTIMATE_ADDR_START;
			$form["end_address"] = $result[$i]->ESTIMATE_ADDR_END;
			if($result[$i]->CHK == "0"){
				$form["authority"] = false;
			}else{
				if($Typeresult[0]->MV_LIST_TYPE == "0"){ // ����
					if($result[$i]->ESTIMATE_SHORT_KIND == "4"){
						$form["authority"] = false;
					}else{
						$form["authority"] = true;
					}
				}elseif($Typeresult[0]->MV_LIST_TYPE == "1"){ // ����
					if($result[$i]->ESTIMATE_SHORT_KIND == "1"){
						$form["authority"] = true;
					}else{
						$form["authority"] = false;
					}
				}elseif($Typeresult[0]->MV_LIST_TYPE == "2"){ // ���
					if($result[$i]->ESTIMATE_SHORT_KIND == "4"){
						$form["authority"] = true;
					}else{
						$form["authority"] = false;
					}
				}elseif($Typeresult[0]->MV_LIST_TYPE == "3"){ // �̻�&���
					$form["authority"] = true;
				}elseif($Typeresult[0]->MV_LIST_TYPE == "4"){ // ����&���
					if($result[$i]->ESTIMATE_SHORT_KIND == "1" || $result[$i]->ESTIMATE_SHORT_KIND == "4"){
						$form["authority"] = true;
					}else{
						$form["authority"] = false;
					}
				}else{
					$form["authority"] = false;
				}				
			}			
			array_push($data, $form);
		}

		/*
		$queryCount = "SELECT
					COUNT(1) AS COUNT
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1
				) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE 1=1";
		$resultCount = DB::select( DB::raw($queryCount) );
		*/

		$Count["pageNo"] = $PageNo;
		///$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		//$Count["listCount"] = $resultCount[0]->COUNT;



		return response()->json( array( "success"=>true, "data" => array( "estimate" => $data ), "page" => $Count ) );
	}

	function MoveMyList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null || $PageNo == 0){ $PageNo = 1; }
		$skip = ($PageNo-1)*25;
		$take = 25;

		$UserID = $request->UserID;
		$CompanyID = $request->CompanyID;

		$GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$CompanyID."'";
		$Typeresult = DB::select( DB::raw($GetTypeQuery) );

//		$query = "SELECT
//					T.*,
//					IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
//				FROM
//				(
//					select
//						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
//						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
//						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
//						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END
//					from ESTIMATE_SHORT AS ES
//					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
//					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
//				) AS T
//				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
//				WHERE 1=1 AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1'";

        $query = "SELECT
					T.*,
					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date_format(ES.ESTIMATE_SHORT_REGDATE, '%y.%m.%d') > '15.12.31' AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE != 'TE' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
				) AS T
				WHERE (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) > 0 ";

		if($Typeresult[0]->MV_LIST_TYPE == "0"){ //����
			$query .= " AND ESTIMATE_SHORT_KIND != 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "1"){ //����
			$query .= " AND ESTIMATE_SHORT_KIND = 1";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "2"){ //���
			$query .= " AND ESTIMATE_SHORT_KIND = 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "3"){ //�̻���

		}elseif($Typeresult[0]->MV_LIST_TYPE == "4"){ //������
			$query .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
		}

		$query .= " ORDER BY ESTIMATE_SHORT_ID DESC LIMIT ".$skip.",".$take;
		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form["listId"] = $result[$i]->ESTIMATE_ADDR_FK_LIST;
			$form["shortId"] = $result[$i]->ESTIMATE_SHORT_ID;
			$form["count"] = $result[$i]->ESTIMATE_SHORT_COUNT;

            if($Typeresult[0]->MV_LIST_GRADE >= $result[$i]->ESTIMATE_SHORT_GRADE){
                $form["replies"] = $result[$i]->ESTIMATE_SHORT_REPLIES;
            } else {
                $form["replies"] = $result[$i]->ESTIMATE_SHORT_MAX_REPLIES;
            }

			$form["max_replies"] = $result[$i]->ESTIMATE_SHORT_MAX_REPLIES;
			$form["kind"] = $result[$i]->ESTIMATE_SHORT_KIND;
			$form["date"] = $result[$i]->ESTIMATE_SHORT_DATE;
			$form["move_date"] = $result[$i]->ESTIMATE_SHORT_MOVE_DATE;
			$form["hasPhone"] = $result[$i]->ESTIMATE_SHORT_PHONE;
			$form["start_address"] = $result[$i]->ESTIMATE_ADDR_START;
			$form["end_address"] = $result[$i]->ESTIMATE_ADDR_END;
			if($result[$i]->CHK == "0"){
				$form["authority"] = false;
			}else{
				$form["authority"] = true;
			}			
			array_push($data, $form);
		}

//		$queryCount = "SELECT
//					COUNT(1) AS COUNT
//				FROM
//				(
//					select
//						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
//						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
//						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
//						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END
//					from ESTIMATE_SHORT AS ES
//					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
//					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1
//				) AS T
//				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
//				WHERE 1=1 AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1'";

        $queryCount = "SELECT
					COUNT(1) AS COUNT
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_REGDATE
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date_format(ES.ESTIMATE_SHORT_REGDATE, '%y.%m.%d') > '15.12.31' AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE != 'TE' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
				) AS T
				WHERE (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) > 0 ";
		if($Typeresult[0]->MV_LIST_TYPE == "0"){ //����
			$queryCount .= " AND ESTIMATE_SHORT_KIND != 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "1"){ //����
			$queryCount .= " AND ESTIMATE_SHORT_KIND = 1";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "2"){ //���
			$queryCount .= " AND ESTIMATE_SHORT_KIND = 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "3"){ //�̻���

		}elseif($Typeresult[0]->MV_LIST_TYPE == "4"){ //������
			$queryCount .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
		}

		$resultCount = DB::select( DB::raw($queryCount) );

		$Count["pageNo"] = $PageNo;
		$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		$Count["listCount"] = $resultCount[0]->COUNT;

		return response()->json( array( "success"=>true, "data" => array( "estimate" => $data ), "page" => $Count ) );
	}

	function DeliveryFullList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null || $PageNo == 0){ $PageNo = 1; }
		$skip = ($PageNo-1)*25;
		$take = 25;

		$UserID = $request->user_id;

        $GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$UserID."'";
        $Typeresult = DB::select( DB::raw($GetTypeQuery) );

		$query = 'SELECT T.*,
                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = substring_index(START_A, " ", 1) AND if(LENGTH(MV_REGI_SI) - LENGTH(REPLACE(MV_REGI_SI, " ", "")) > 0, MR.MV_REGI_SI = substring_index(substring_index(START_A, " ", 3), " ", -2), MR.MV_REGI_SI = substring_index(START_A, " ", -1)) AND MR.MV_REGI_FK_LIST = "' . $UserID . '" ) AS START_COMPANY_PERMISSION,
                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = substring_index(END_A, " ", 1) AND if(LENGTH(MV_REGI_SI) - LENGTH(REPLACE(MV_REGI_SI, " ", "")) > 0, MR.MV_REGI_SI = substring_index(substring_index(END_A, " ", 3), " ", -2), MR.MV_REGI_SI = substring_index(END_A, " ", -1)) AND MR.MV_REGI_FK_LIST = "' . $UserID . '" ) AS END_COMPANY_PERMISSION,
                      (SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID="' . $UserID . '") AS MV_LIST_TYPE
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL, DE.DV_ESTIMATE_LIST_GRADE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y"
                  ) AS T order by T.DV_ESTIMATE_LIST_ID desc LIMIT '.$skip.','.$take;
		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form['id'] = $result[$i]->DV_ESTIMATE_LIST_ID;
			$form['date'] = $result[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
			$form['reg_date'] = $result[$i]->DV_ESTIMATE_LIST_REG_DATE;
			$form['worker'] = $result[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
			$form['narrowAlley'] = $result[$i]->DV_ESTIMATE_LIST_OPT_1;
			$form['ladder'] = $result[$i]->DV_ESTIMATE_LIST_OPT_2;

			if (empty($result[$i]->DV_ESTIMATE_LIST_ETC_INFO)) {
				$form['detail'] = "";
			} else {
				$form['detail'] = $result[$i]->DV_ESTIMATE_LIST_ETC_INFO;
			}

			$form['user_id'] = $result[$i]->DV_ESTIMATE_LIST_FK_USER;
			$form['finish'] = $result[$i]->DV_ESTIMATE_LIST_FINISH;
			$form['use_yn'] = $result[$i]->DV_ESTIMATE_LIST_USEYN;

			$form['startAddress'] = $result[$i]->START_A;
			$form['endAddress'] = $result[$i]->END_A;
			$form['phone'] = $result[$i]->PHONE;

			if ($result[$i]->START_COMPANY_PERMISSION == '0' && $result[$i]->END_COMPANY_PERMISSION == '0') {
				$form['permission'] = "0";
			} else {
				if ($result[$i]->MV_LIST_TYPE == 0 || $result[$i]->MV_LIST_TYPE == 1) {
					$form['permission'] = "0";
				} else {
					$form['permission'] = "1";
				}
			}
			$form['type'] = $result[$i]->MV_LIST_TYPE;

            if($Typeresult[0]->MV_LIST_GRADE >= $result[$i]->DV_ESTIMATE_LIST_GRADE){
                $form['call'] = $result[$i]->DV_ESTIMATE_LIST_CALL;
            } else {
                $form['call'] = 3;
            }

			array_push($data, $form);
		}

		$queryCount = 'SELECT COUNT(1) AS COUNT
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y"
                  ) AS T';

		$resultCount = DB::select( DB::raw($queryCount) );

		$Count["pageNo"] = $PageNo;
		$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		$Count["listCount"] = $resultCount[0]->COUNT;

		return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}

	function DeliveryMyList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null || $PageNo == 0){ $PageNo = 1; }
		$skip = ($PageNo-1)*25;
		$take = 25;

		$UserID = $request->user_id;

        $GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$UserID."'";
        $Typeresult = DB::select( DB::raw($GetTypeQuery) );

		$query = "SELECT 
					DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE,
					SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_START_ADDR_KR),' ', 1) AS START_AREA_SI,
					SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_START_ADDR_KR),' ', 2)),' ', -1) AS START_AREA_GU,
					DV_ESTIMATE_LIST_START_ADDR_DONG,
					SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_END_ADDR_KR),' ', 1) AS END_AREA_SI,
					SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_END_ADDR_KR),' ', 2)),' ', -1) AS END_AREA_GU,
					DV_ESTIMATE_LIST_END_ADDR_DONG,
					DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER,
					DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN, DV_ESTIMATE_LIST_CALL,
					(SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DV_ESTIMATE_LIST_FK_USER) AS PHONE, DV_ESTIMATE_LIST_GRADE
				FROM DV_ESTIMATE_LIST
				LEFT JOIN DV_ESTIMATE_CALLED ON DV_ESTIMATE_LIST_ID=DV_ESTIMATE_CALLED_FK_LIST
				WHERE DV_ESTIMATE_LIST_USEYN='Y' AND DV_ESTIMATE_CALLED_FK_MV_LIST='" . $UserID . "' ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT ".$skip.",".$take;
		
		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form['id'] = $result[$i]->DV_ESTIMATE_LIST_ID;
			$form['date'] = $result[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
			$form['reg_date'] = $result[$i]->DV_ESTIMATE_LIST_REG_DATE;

			$form['startAddress'] = trim( $result[$i]->START_AREA_SI." ".$result[$i]->START_AREA_GU." ".$result[$i]->DV_ESTIMATE_LIST_START_ADDR_DONG );
			$form['endAddress'] = trim( $result[$i]->END_AREA_SI." ".$result[$i]->END_AREA_GU." ".$result[$i]->DV_ESTIMATE_LIST_END_ADDR_DONG );

			$form['worker'] = $result[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
			$form['narrowAlley'] = $result[$i]->DV_ESTIMATE_LIST_OPT_1;
			$form['ladder'] = $result[$i]->DV_ESTIMATE_LIST_OPT_2;

			if (empty( $result[$i]->DV_ESTIMATE_LIST_ETC_INFO )) {
				$form['detail'] = "";
			} else {
				$form['detail'] = $result[$i]->DV_ESTIMATE_LIST_ETC_INFO;
			}

			$form['user_id'] = $result[$i]->DV_ESTIMATE_LIST_FK_USER;
			$form['finish'] = $result[$i]->DV_ESTIMATE_LIST_FINISH;
			$form['use_yn'] = $result[$i]->DV_ESTIMATE_LIST_USEYN;

            if($Typeresult[0]->MV_LIST_GRADE >= $result[$i]->DV_ESTIMATE_LIST_GRADE){
                $form['call'] = $result[$i]->DV_ESTIMATE_LIST_CALL;
            } else {
                $form['call'] = 3;
            }


			if ($result[$i]->PHONE == null) {
				$form['phone'] = "";
			} else {
				$form['phone'] = $result[$i]->PHONE;
			}

			$form['permission'] = "1";
			array_push($data, $form);
		}

		$queryCount = "SELECT 
					COUNT(1) AS COUNT
				FROM DV_ESTIMATE_LIST
				LEFT JOIN DV_ESTIMATE_CALLED ON DV_ESTIMATE_LIST_ID=DV_ESTIMATE_CALLED_FK_LIST
				WHERE DV_ESTIMATE_LIST_USEYN='Y' AND DV_ESTIMATE_CALLED_FK_MV_LIST='" . $UserID . "'";

		$resultCount = DB::select( DB::raw($queryCount) );

		$Count["pageNo"] = $PageNo;
		$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		$Count["listCount"] = $resultCount[0]->COUNT;

		return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}

	function NoTenderMoveList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null || $PageNo == 0){ $PageNo = 1; }
		$skip = ($PageNo-1)*25;
		$take = 25;

		$UserID = $request->UserID;
		$CompanyID = $request->CompanyID;

		$GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$CompanyID."'";
		$Typeresult = DB::select( DB::raw($GetTypeQuery) );

		$query = "SELECT
					T.*,
					IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_GRADE
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND ES.ESTIMATE_SHORT_PHONE != '3'
				) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE 1=1 AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' AND ESTIMATE_SHORT_REPLIES = '0' AND ".$Typeresult[0]->MV_LIST_GRADE." >= ESTIMATE_SHORT_GRADE";

		if($Typeresult[0]->MV_LIST_TYPE == "0"){ //����
			$query .= " AND ESTIMATE_SHORT_KIND != 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "1"){ //����
			$query .= " AND ESTIMATE_SHORT_KIND = 1";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "2"){ //���
			$query .= " AND ESTIMATE_SHORT_KIND = 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "3"){ //�̻���

		}elseif($Typeresult[0]->MV_LIST_TYPE == "4"){ //������
			$query .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
		}

		$query .= " ORDER BY ESTIMATE_SHORT_ID DESC LIMIT ".$skip.",".$take;
		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form["listId"] = $result[$i]->ESTIMATE_ADDR_FK_LIST;
			$form["shortId"] = $result[$i]->ESTIMATE_SHORT_ID;
			$form["count"] = $result[$i]->ESTIMATE_SHORT_COUNT;
			$form["replies"] = $result[$i]->ESTIMATE_SHORT_REPLIES;
			$form["max_replies"] = $result[$i]->ESTIMATE_SHORT_MAX_REPLIES;
			$form["kind"] = $result[$i]->ESTIMATE_SHORT_KIND;
			$form["date"] = $result[$i]->ESTIMATE_SHORT_DATE;
			$form["move_date"] = $result[$i]->ESTIMATE_SHORT_MOVE_DATE;
			$form["hasPhone"] = $result[$i]->ESTIMATE_SHORT_PHONE;
			$form["start_address"] = $result[$i]->ESTIMATE_ADDR_START;
			$form["end_address"] = $result[$i]->ESTIMATE_ADDR_END;
			if($result[$i]->CHK == "0"){
				$form["authority"] = false;
			}else{
				$form["authority"] = true;
			}			
			array_push($data, $form);
		}

		$queryCount = "SELECT
					COUNT(1) AS COUNT
				FROM
				(
					select
						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_GRADE,
						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END,
						(SELECT MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID= '".$CompanyID."') AS GRADE
					from ESTIMATE_SHORT AS ES
					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1
				) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$CompanyID."' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE 1=1 AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' AND ESTIMATE_SHORT_REPLIES = '0' AND GRADE >= ESTIMATE_SHORT_GRADE";
		if($Typeresult[0]->MV_LIST_TYPE == "0"){ //����
			$queryCount .= " AND ESTIMATE_SHORT_KIND != 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "1"){ //����
			$queryCount .= " AND ESTIMATE_SHORT_KIND = 1";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "2"){ //���
			$queryCount .= " AND ESTIMATE_SHORT_KIND = 4";
		}elseif($Typeresult[0]->MV_LIST_TYPE == "3"){ //�̻���

		}elseif($Typeresult[0]->MV_LIST_TYPE == "4"){ //������
			$queryCount .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
		}

		$resultCount = DB::select( DB::raw($queryCount) );

		$Count["pageNo"] = $PageNo;
		$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		$Count["listCount"] = $resultCount[0]->COUNT;

		return response()->json( array( "success"=>true, "data" => array( "estimate" => $data ), "page" => $Count ) );
	}

	function NoTenderDeliveryList(Request $request){
		$PageNo = $request->PageNo;
		if($PageNo == '' || $PageNo == null || $PageNo == 0){ $PageNo = 1; }
		$skip = ($PageNo-1)*25;
		$take = 25;

		$UserID = $request->user_id;

        $GetTypeQuery = "SELECT MV_LIST_TYPE, MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID = '".$UserID."'";
        $Typeresult = DB::select( DB::raw($GetTypeQuery) );

		$query = "SELECT 
					DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE,
					SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_START_ADDR_KR),' ', 1) AS START_AREA_SI,
					SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_START_ADDR_KR),' ', 2)),' ', -1) AS START_AREA_GU,
					DV_ESTIMATE_LIST_START_ADDR_DONG,
					SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_END_ADDR_KR),' ', 1) AS END_AREA_SI,
					SUBSTRING_INDEX(TRIM(SUBSTRING_INDEX(TRIM(DV_ESTIMATE_LIST_END_ADDR_KR),' ', 2)),' ', -1) AS END_AREA_GU,
					DV_ESTIMATE_LIST_END_ADDR_DONG,
					DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER,
					DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN, DV_ESTIMATE_LIST_CALL,
					(SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DV_ESTIMATE_LIST_FK_USER) AS PHONE
				FROM DV_ESTIMATE_LIST
				LEFT JOIN DV_ESTIMATE_CALLED ON DV_ESTIMATE_LIST_ID=DV_ESTIMATE_CALLED_FK_LIST
				WHERE DV_ESTIMATE_LIST_USEYN='Y' AND DV_ESTIMATE_CALLED_FK_MV_LIST='" . $UserID . "'
				AND DV_ESTIMATE_LIST_CALL = '0'
				AND ".$Typeresult[0]->MV_LIST_GRADE." >=DV_ESTIMATE_LIST_GRADE
				ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT ".$skip.",".$take;
		
		$result = DB::select( DB::raw($query) );

		$data = array();
		for($i = 0; $i < count($result); $i++){
			$form = array();
			$form['id'] = $result[$i]->DV_ESTIMATE_LIST_ID;
			$form['date'] = $result[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
			$form['reg_date'] = $result[$i]->DV_ESTIMATE_LIST_REG_DATE;

			$form['startAddress'] = trim( $result[$i]->START_AREA_SI." ".$result[$i]->START_AREA_GU." ".$result[$i]->DV_ESTIMATE_LIST_START_ADDR_DONG );
			$form['endAddress'] = trim( $result[$i]->END_AREA_SI." ".$result[$i]->END_AREA_GU." ".$result[$i]->DV_ESTIMATE_LIST_END_ADDR_DONG );

			$form['worker'] = $result[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
			$form['narrowAlley'] = $result[$i]->DV_ESTIMATE_LIST_OPT_1;
			$form['ladder'] = $result[$i]->DV_ESTIMATE_LIST_OPT_2;

			if (empty( $result[$i]->DV_ESTIMATE_LIST_ETC_INFO )) {
				$form['detail'] = "";
			} else {
				$form['detail'] = $result[$i]->DV_ESTIMATE_LIST_ETC_INFO;
			}

			$form['user_id'] = $result[$i]->DV_ESTIMATE_LIST_FK_USER;
			$form['finish'] = $result[$i]->DV_ESTIMATE_LIST_FINISH;
			$form['use_yn'] = $result[$i]->DV_ESTIMATE_LIST_USEYN;
			$form['call'] = $result[$i]->DV_ESTIMATE_LIST_CALL;

			if ($result[$i]->PHONE == null) {
				$form['phone'] = "";
			} else {
				$form['phone'] = $result[$i]->PHONE;
			}

			$form['permission'] = "1";
			array_push($data, $form);
		}

		$queryCount = "SELECT 
					COUNT(1) AS COUNT
				FROM DV_ESTIMATE_LIST
				LEFT JOIN DV_ESTIMATE_CALLED ON DV_ESTIMATE_LIST_ID=DV_ESTIMATE_CALLED_FK_LIST
				WHERE DV_ESTIMATE_LIST_USEYN='Y' AND DV_ESTIMATE_CALLED_FK_MV_LIST='" . $UserID . "' AND DV_ESTIMATE_LIST_CALL = '0'";

		$resultCount = DB::select( DB::raw($queryCount) );

		$Count["pageNo"] = $PageNo;
		$Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
		$Count["listCount"] = $resultCount[0]->COUNT;

		return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}	

	function DeliveryFullList2(Request $request){
		$companyId = $request->companyId;
        $page = $request->PageNo;

        if($page == '' || $page == null || $page == 0){ $page = 1; }
        $skip = ($page-1)*25;
        $take = 25;

        $query = "SELECT T.*,
                      IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' ";

        $query .= ' group by DV_ESTIMATE_LIST_ID ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT '.$skip.','.$take;

        $query = DB::select(DB::raw($query));

        for ($i = 0; $i < count($query); $i++) {
            if (substr($query[$i]->PHONE, 0, 1) == '+') {
                $query[$i]->PHONE = '0' . substr($query[$i]->PHONE, 3);
            }
        }

		$data = array();
		for($i = 0; $i < count($query); $i++){
			$form = array();
			$form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
			$form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
			$form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
			$form['worker'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
			$form['narrowAlley'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
			$form['ladder'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;

			if (empty($query[$i]->DV_ESTIMATE_LIST_ETC_INFO)) {
				$form['detail'] = "";
			} else {
				$form['detail'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
			}

			$form['user_id'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
			$form['finish'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
			$form['use_yn'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;

			$form['startAddress'] = $query[$i]->START_A;
			$form['endAddress'] = $query[$i]->END_A;
			$form['phone'] = $query[$i]->PHONE;

			if ($query[$i]->CHK == '0') {
				$form['permission'] = "0";
			} else {
				$form['permission'] = "1";
			}
			$form['type'] = $query[$i]->MV_LIST_TYPE;
			$form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;	
			array_push($data, $form);
		}

        $queryCount = "SELECT COUNT(1) AS COUNT
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' ";

        $resultCount = DB::select( DB::raw($queryCount) );

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}

	function DeliveryMyList2(Request $request){
		$companyId = $request->companyId;
        $page = $request->PageNo;

        if($page == '' || $page == null || $page == 0){ $page = 1; }
        $skip = ($page-1)*25;
        $take = 25;

        $query = "SELECT T.*,
                      IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' ";

        $query .= ' group by DV_ESTIMATE_LIST_ID ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT '.$skip.','.$take;

        $query = DB::select(DB::raw($query));

        if(!empty($query)){
            for ($i = 0; $i < count($query); $i++) {
                if (substr($query[$i]->PHONE, 0, 1) == '+') {
                    $query[$i]->PHONE = '0' . substr($query[$i]->PHONE, 3);
                }
            }

			$data = array();
			for($i = 0; $i < count($query); $i++){
				$form = array();
				$form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
				$form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
				$form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
				$form['worker'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
				$form['narrowAlley'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
				$form['ladder'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;

				if (empty($query[$i]->DV_ESTIMATE_LIST_ETC_INFO)) {
					$form['detail'] = "";
				} else {
					$form['detail'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
				}

				$form['user_id'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
				$form['finish'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
				$form['use_yn'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;

				$form['startAddress'] = $query[$i]->START_A;
				$form['endAddress'] = $query[$i]->END_A;
				$form['phone'] = $query[$i]->PHONE;

				if ($query[$i]->CHK == '0') {
					$form['permission'] = "0";
				} else {
					$form['permission'] = "1";
				}
				$form['type'] = $query[$i]->MV_LIST_TYPE;
				$form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;	
				array_push($data, $form);
			}
        }

        $queryCount = "SELECT COUNT(1) AS COUNT
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' ";

        $resultCount = DB::select( DB::raw($queryCount) );

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}

	function NoTenderDeliveryList2(Request $request){
		$companyId = $request->companyId;
        $page = $request->PageNo;

        if($page == '' || $page == null || $page == 0){ $page = 1; }
        $skip = ($page-1)*25;
        $take = 25;

        $query = "SELECT T.*,
                      IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' AND DV_ESTIMATE_LIST_CALL = 0";

        $query .= ' group by DV_ESTIMATE_LIST_ID ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT '.$skip.','.$take;

        $query = DB::select(DB::raw($query));

        if(!empty($query)){
            for ($i = 0; $i < count($query); $i++) {
                if (substr($query[$i]->PHONE, 0, 1) == '+') {
                    $query[$i]->PHONE = '0' . substr($query[$i]->PHONE, 3);
                }
            }

			$data = array();
			for($i = 0; $i < count($query); $i++){
				$form = array();
				$form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
				$form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
				$form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
				$form['worker'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
				$form['narrowAlley'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
				$form['ladder'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;

				if (empty($query[$i]->DV_ESTIMATE_LIST_ETC_INFO)) {
					$form['detail'] = "";
				} else {
					$form['detail'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
				}

				$form['user_id'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
				$form['finish'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
				$form['use_yn'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;

				$form['startAddress'] = $query[$i]->START_A;
				$form['endAddress'] = $query[$i]->END_A;
				$form['phone'] = $query[$i]->PHONE;

				if ($query[$i]->CHK == '0') {
					$form['permission'] = "0";
				} else {
					$form['permission'] = "1";
				}
				$form['type'] = $query[$i]->MV_LIST_TYPE;
				$form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;	
				array_push($data, $form);
			}
        }



        $queryCount = "SELECT COUNT(1) AS COUNT
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' AND DV_ESTIMATE_LIST_CALL = 0";

        $resultCount = DB::select( DB::raw($queryCount) );

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json( array( "success"=>true, "data" => $data , "page" => $Count ) );
	}
}