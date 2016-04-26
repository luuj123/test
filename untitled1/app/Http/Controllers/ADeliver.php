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
use App\DV_ESTIMATE_LIST;
use App\CODE_AREA;

class ADeliver extends Controller
{

    public function SearchList(Request $request)
    {

        $companyId = $request->companyId;
        $start_area = $request->start_area;
        $end_area = $request->end_area;
        $start_regdate = $request->start_regdate;
        $end_regdate = $request->end_regdate;
        $start_movedate = $request->start_movedate;
        $end_movedate = $request->end_movedate;
        $nobiz = $request->nobiz;
        $page = $request->PageNo;

        if($page == '' || $page == null || $page == 0){ $page = 1; }
        $skip = ($page-1)*25;
        $take = 25;

        $query = 'select MV_LIST_TYPE, MV_LIST_GRADE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
            $grade = $query[0]->MV_LIST_GRADE;
        }

        $query = 'SELECT ESTIMATE_CLICK_FK_SHORT, ESTIMATE_CLICK_OPEN, ESTIMATE_CLICK_BIDDING_CALL, ESTIMATE_CLICK_BIDDING_REPLY FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY=' . $companyId . ' AND ESTIMATE_CLICK_MV_DV="DV";';
        $query = DB::select( DB::raw( $query ) );

        $result = array();

        if(count($query)){

            $form = array();


            for($i=0; $i<count($query); $i++){
                $form['shortId'] = $query[$i]->ESTIMATE_CLICK_FK_SHORT;
                $form['click_open'] = $query[$i]->ESTIMATE_CLICK_OPEN;
                $form['click_call'] = $query[$i]->ESTIMATE_CLICK_BIDDING_CALL;
                $form['click_reply'] = $query[$i]->ESTIMATE_CLICK_BIDDING_REPLY;

                array_push($result, $form);
            }
        }

//        $query = "SELECT T.*,
//                      IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
//                  FROM
//                  (
//                    select
//                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
//                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
//                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
//                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
//                        DE.DV_ESTIMATE_LIST_CALL,
//						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
//                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
//                  ) AS T
//				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
//				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' ";

        $query = "SELECT T.*,
                      (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( START_A Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR END_A like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE, DE.DV_ESTIMATE_LIST_GRADE

                    from DV_ESTIMATE_LIST AS DE

                    WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' ";


        if ($start_area == "" && $end_area == "") {

        } else if (!$start_area == "" && $end_area == "") {
            $query .= "AND DV_ESTIMATE_LIST_START_ADDR_KR LIKE '%" . $start_area . "%'";
        } else if ($start_area == "" && !$end_area == "") {
            $query .= "AND DV_ESTIMATE_LIST_END_ADDR_KR LIKE '%" . $end_area . "%'";
        } else if (!$start_area == "" && !$end_area == "") {
            $query .= "AND DV_ESTIMATE_LIST_START_ADDR_KR LIKE '%" . $start_area . "%' AND DV_ESTIMATE_LIST_END_ADDR_KR LIKE '%" . $end_area . "%'";
        }

        if ($start_regdate == "" && $end_regdate == "") {

        } else if (!$start_regdate == "" && $end_regdate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_REG_DATE) >= date('" . $start_regdate . "') ";
        } else if ($start_regdate == "" && !$end_regdate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_REG_DATE) <= date('" . $end_regdate . "') ";
        } else if (!$start_regdate == "" && !$end_regdate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_REG_DATE) >= date('" . $start_regdate . "') AND date(DV_ESTIMATE_LIST_REG_DATE) <= date('" . $end_regdate . "') ";
        }


        if (!$start_movedate == "" && $end_movedate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) >= date('" . $start_movedate . "') ";
        } else if ($start_movedate == "" && !$end_movedate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) <= date('" . $end_movedate . "') ";
        } else if (!$start_movedate == "" && !$end_movedate == "") {
            $query .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) >= date('" . $start_movedate . "') AND date(DV_ESTIMATE_LIST_MOVE_DATE) <= date('" . $end_movedate . "') ";
        }

        if ($nobiz == 'true') {
            $query .= "AND DV_ESTIMATE_LIST_CALL = 0 AND ".$grade." >= DV_ESTIMATE_LIST_GRADE";
        }

        $query .= ' ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT '.$skip.','.$take;
//        $query .= " order by T.DV_ESTIMATE_LIST_ID desc LIMIT ".$skip.",".$take;


        $query = DB::select(DB::raw($query));


//        if(substr($query[0]->USER_DEVICE_REGISTERS_PHONE, 0, 1) == '+'){
//            $form["info"]["USER_DEVICE_REGISTERS_PHONE"] = '0'.substr($query[0]->USER_DEVICE_REGISTERS_PHONE, 3);
//        } else {
//            $form["info"]["USER_DEVICE_REGISTERS_PHONE"] = $query[0]->USER_DEVICE_REGISTERS_PHONE;
//        }

        if(count($query)){
            $form2 = array();
            $data = array();
            for ($i = 0; $i < count($query); $i++) {
//                if (substr($query[$i]->PHONE, 0, 1) == '+') {
//                    $query[$i]->PHONE = '0' . substr($query[$i]->PHONE, 3);
//                }
//
//                if($query[$i]->DV_ESTIMATE_LIST_CALL > 2){
//                    $query[$i]->DV_ESTIMATE_LIST_CALL = 5;
//                }
//
//                if($query[$i]->CHK > 0){
//                    $query[$i]->CHK = 1;
//                }
//
//                if (!($nobiz == 'true')) {
//                    if($grade < $query[$i]->DV_ESTIMATE_LIST_GRADE){
//                        $query[$i]->DV_ESTIMATE_LIST_CALL = 3;
//                    }
//                }
//
//                if($query[$i]->CLICKED == null || $query[$i]->CLICKED == 0){
//                    $query[$i]->CLICKED = false;
//                } else if($query[$i]->CLICKED == 1){
//                    $query[$i]->CLICKED = true;
//                }
//
//                if($query[$i]->BIDDING_CALL == null || $query[$i]->BIDDING_CALL == 0){
//                    $query[$i]->BIDDING_CALL = false;
//                } else if($query[$i]->BIDDING_CALL == 1){
//                    $query[$i]->BIDDING_CALL = true;
//                }
//
//                if($query[$i]->BIDDING_REPLY == null || $query[$i]->BIDDING_REPLY == 0){
//                    $query[$i]->BIDDING_REPLY = false;
//                } else if($query[$i]->BIDDING_REPLY == 1){
//                    $query[$i]->BIDDING_REPLY = true;
//                }

                $form2['DV_ESTIMATE_LIST_ID'] = $query[$i]->DV_ESTIMATE_LIST_ID;
                $form2['DV_ESTIMATE_LIST_MOVE_DATE'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
                $form2['DV_ESTIMATE_LIST_REG_DATE'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
                $form2['DV_ESTIMATE_LIST_NEED_PEOPLE'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
                $form2['DV_ESTIMATE_LIST_OPT_1'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
                $form2['DV_ESTIMATE_LIST_OPT_2'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;
                $form2['DV_ESTIMATE_LIST_ETC_INFO'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
                $form2['DV_ESTIMATE_LIST_FK_USER'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
                $form2['DV_ESTIMATE_LIST_FINISH'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
                $form2['DV_ESTIMATE_LIST_USEYN'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;
                $form2['START_A'] = $query[$i]->START_A;
                $form2['END_A'] = $query[$i]->END_A;
                $form2['DV_ESTIMATE_LIST_CALL'] = $query[$i]->DV_ESTIMATE_LIST_CALL;
                $form2['MV_LIST_TYPE'] = $query[$i]->MV_LIST_TYPE;
                $form2['DV_ESTIMATE_LIST_GRADE'] = $query[$i]->DV_ESTIMATE_LIST_GRADE;
                $form2['PHONE'] = $query[$i]->PHONE;

                if (substr($query[$i]->PHONE, 0, 1) == '+') {
                    $form2['PHONE'] = '0' . substr($query[$i]->PHONE, 3);
                }

                if($query[$i]->CHK > 0){
                    $form2['CHK'] = 1;
                } else {
                    $form2['CHK'] = $query[$i]->CHK;
                }

                if (!($nobiz == 'true')) {
                    if ($grade < $query[$i]->DV_ESTIMATE_LIST_GRADE) {
                        $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->DV_ESTIMATE_LIST_GRADE;
                    }
                }

                if(count($result)){
                    for($j=0; $j<count($result); $j++){

                        if($result[$j]['shortId'] == $query[$i]->DV_ESTIMATE_LIST_ID){
                            if ($result[$j]['click_open'] == null || $result[$j]['click_open'] == 0) {
                                $form2['CLICKED'] = false;
                            } else if ($result[$i]['click_open'] == 1) {
                                $form2['CLICKED'] = true;
                            }

                            if ($result[$j]['click_call'] == null || $result[$j]['click_call'] == 0) {
                                $form2['BIDDING_CALL'] =false;
                            } else if ($result[$i]['click_call'] == 1) {
                                $form2['BIDDING_CALL'] =true;
                            }

                            if ($result[$j]['click_reply'] == null || $result[$j]['click_reply'] == 0) {
                                $form2['BIDDING_REPLY'] = false;
                            } else if ($result[$j]['click_reply'] == 1) {
                                $form2['BIDDING_REPLY'] = true;
                            }
                        } else {
                            $form2['CLICKED'] = false;
                            $form2['BIDDING_CALL'] =false;
                            $form2['BIDDING_REPLY'] = false;
                        }
                    }
                }
                array_push($data, $form2);

            }

            return response()->json( array( "success"=>true, "data" => $data, "empty" => false ) );
        } else {
            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
        }


//        if(!isset($query)){
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
//        } else {
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => false ) );
//        }

//        return response()->json( array( "success"=>true, "data" => $query ) );

//        $queryCount = 'SELECT COUNT(1) AS COUNT
//                  FROM
//                  (
//                    select
//                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
//                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
//                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
//                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
//                        DE.DV_ESTIMATE_LIST_CALL
//                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y"
//                  ) AS T WHERE 1=1 ';

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


        if ($start_area == "" && $end_area == "") {

        } else if (!$start_area == "" && $end_area == "") {
            $queryCount .= "AND DV_ESTIMATE_LIST_START_ADDR_KR LIKE '%" . $start_area . "%'";
        } else if ($start_area == "" && !$end_area == "") {
            $queryCount .= "AND DV_ESTIMATE_LIST_END_ADDR_KR LIKE '%" . $end_area . "%'";
        } else if (!$start_area == "" && !$end_area == "") {
            $queryCount .= "AND DV_ESTIMATE_LIST_START_ADDR_KR LIKE '%" . $start_area . "%' AND DV_ESTIMATE_LIST_END_ADDR_KR LIKE '%" . $end_area . "%'";
        }

        if ($start_regdate == "" && $end_regdate == "") {

        } else if (!$start_regdate == "" && $end_regdate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_REG_DATE) >= date('" . $start_regdate . "') ";
        } else if ($start_regdate == "" && !$end_regdate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_REG_DATE) <= date('" . $end_regdate . "') ";
        } else if (!$start_regdate == "" && !$end_regdate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_REG_DATE) >= date('" . $start_regdate . "') AND DV_ESTIMATE_LIST_REG_DATE <= date('" . $end_regdate . "') ";
        }


        if (!$start_movedate == "" && $end_movedate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) >= date('" . $start_movedate . "') ";
        } else if ($start_movedate == "" && !$end_movedate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) <= date('" . $end_movedate . "') ";
        } else if (!$start_movedate == "" && !$end_movedate == "") {
            $queryCount .= "AND date(DV_ESTIMATE_LIST_MOVE_DATE) >= date('" . $start_movedate . "') AND date(DV_ESTIMATE_LIST_MOVE_DATE) <= date('" . $end_movedate . "') ";
        }

        if ($nobiz) {
            $queryCount .= "AND DV_ESTIMATE_LIST_CALL = 0 ";
        }

        $resultCount = DB::select( DB::raw($queryCount) );

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json( array( "success"=>true, "data" => $query , "page" => $Count ) );

//
//        $query = ESTIMATE_SHORT::leftJoin('ESTIMATE_REPLY', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT', '=', 'ESTIMATE_SHORT.ESTIMATE_SHORT_ID')
//            ->leftJoin('MV_LIST', 'ESTIMATE_REPLY.ESTIMATE_REPLY_FK_MOVE', '=', 'MV_LIST.MV_LIST_ID')
//            ->selectRaw('ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_ID, ESTIMATE_REPLY_ID, MV_LIST_ID, ESTIMATE_REPLY_CONTENT, ESTIMATE_REPLY_PRICE, MV_LIST_NAME,
//                         MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_CHOOSE_PHONE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_ICON, ESTIMATE_REPLY_PACKING_PRICE,
//                         ESTIMATE_REPLY_HALF_PACKING_PRICE, ESTIMATE_REPLY_CAR, ESTIMATE_REPLY_MAN, ESTIMATE_REPLY_GIRL, ESTIMATE_REPLY_LADDER_START, ESTIMATE_REPLY_LADDER_END')
//            ->where('ESTIMATE_SHORT_FK_LIST', '=', $listId)
//            ->where('MV_LIST_ID', '=', $companyId)
//            ->orderBy('ESTIMATE_REPLY_PRICE', 'ASC')->get();
//
//
//        $form['reply'] = $query;

        return response()->json(array('data' => $query));
    }

    public function EstimateList(Request $request)
    {

        $companyId = $request->companyId;
        $nobiz = $request->nobiz;
        $page = $request->PageNo;

        if($page == '' || $page == null || $page == 0){ $page = 1; }
        $skip = ($page-1)*25;
        $take = 25;

        $query = 'select MV_LIST_TYPE, MV_LIST_GRADE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
            $grade = $query[0]->MV_LIST_GRADE;
        }

        $query = 'SELECT ESTIMATE_CLICK_FK_SHORT, ESTIMATE_CLICK_OPEN, ESTIMATE_CLICK_BIDDING_CALL, ESTIMATE_CLICK_BIDDING_REPLY FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY=' . $companyId . ' AND ESTIMATE_CLICK_MV_DV="DV";';
        $query = DB::select( DB::raw( $query ) );

        $result = array();

        if(count($query)){

            $form = array();


            for($i=0; $i<count($query); $i++){
                $form['shortId'] = $query[$i]->ESTIMATE_CLICK_FK_SHORT;
                $form['click_open'] = $query[$i]->ESTIMATE_CLICK_OPEN;
                $form['click_call'] = $query[$i]->ESTIMATE_CLICK_BIDDING_CALL;
                $form['click_reply'] = $query[$i]->ESTIMATE_CLICK_BIDDING_REPLY;

                array_push($result, $form);
            }
        }

//        $query = "SELECT T.*,
//                      IF(MR.MV_REGI_ID IS NULL, '0', '1') AS CHK
//                  FROM
//                  (
//                    select
//                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
//                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
//                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
//                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
//                        DE.DV_ESTIMATE_LIST_CALL,
//						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE
//                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
//                  ) AS T
//				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '".$companyId."' AND ( START_A Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR END_A like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
//				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1' ";

        $query = "SELECT T.*,
                      (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( START_A Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR END_A like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL,
						(SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID='".$companyId."') AS MV_LIST_TYPE, DE.DV_ESTIMATE_LIST_GRADE

                    from DV_ESTIMATE_LIST AS DE

                    WHERE DE.DV_ESTIMATE_LIST_USEYN='Y'
                  ) AS T
				WHERE MV_LIST_TYPE != '0' AND MV_LIST_TYPE != '1' AND (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( START_A Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR END_A like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) > 0 ";

        if ($nobiz == 'true') {
            $query .= "AND DV_ESTIMATE_LIST_CALL = 0 AND ".$grade." >= DV_ESTIMATE_LIST_GRADE";
        }

        $query .= ' ORDER BY DV_ESTIMATE_LIST_ID DESC LIMIT '.$skip.','.$take;


        $query = DB::select(DB::raw($query));

        if(!empty($query)){
            $form2 = array();
            $data = array();
            for ($i = 0; $i < count($query); $i++) {
//                if (substr($query[$i]->PHONE, 0, 1) == '+') {
//                    $query[$i]->PHONE = '0' . substr($query[$i]->PHONE, 3);
//                }
//
//                if($query[$i]->DV_ESTIMATE_LIST_CALL > 2){
//                    $query[$i]->DV_ESTIMATE_LIST_CALL = 5;
//                }
//
//                if($query[$i]->CHK > 0){
//                    $query[$i]->CHK = 1;
//                }
//
//                if (!($nobiz == 'true')) {
//                    if($grade < $query[$i]->DV_ESTIMATE_LIST_GRADE){
//                        $query[$i]->DV_ESTIMATE_LIST_CALL = 3;
//                    }
//                }
//
//                if($query[$i]->CLICKED == null || $query[$i]->CLICKED == 0){
//                    $query[$i]->CLICKED = false;
//                } else if($query[$i]->CLICKED == 1){
//                    $query[$i]->CLICKED = true;
//                }
//
//                if($query[$i]->BIDDING_CALL == null || $query[$i]->BIDDING_CALL == 0){
//                    $query[$i]->BIDDING_CALL = false;
//                } else if($query[$i]->BIDDING_CALL == 1){
//                    $query[$i]->BIDDING_CALL = true;
//                }
//
//                if($query[$i]->BIDDING_REPLY == null || $query[$i]->BIDDING_REPLY == 0){
//                    $query[$i]->BIDDING_REPLY = false;
//                } else if($query[$i]->BIDDING_REPLY == 1){
//                    $query[$i]->BIDDING_REPLY = true;
//                }

                $form2['DV_ESTIMATE_LIST_ID'] = $query[$i]->DV_ESTIMATE_LIST_ID;
                $form2['DV_ESTIMATE_LIST_MOVE_DATE'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
                $form2['DV_ESTIMATE_LIST_REG_DATE'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
                $form2['DV_ESTIMATE_LIST_NEED_PEOPLE'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
                $form2['DV_ESTIMATE_LIST_OPT_1'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
                $form2['DV_ESTIMATE_LIST_OPT_2'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;
                $form2['DV_ESTIMATE_LIST_ETC_INFO'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
                $form2['DV_ESTIMATE_LIST_FK_USER'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
                $form2['DV_ESTIMATE_LIST_FINISH'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
                $form2['DV_ESTIMATE_LIST_USEYN'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;
                $form2['START_A'] = $query[$i]->START_A;
                $form2['END_A'] = $query[$i]->END_A;
                $form2['DV_ESTIMATE_LIST_CALL'] = $query[$i]->DV_ESTIMATE_LIST_CALL;
                $form2['MV_LIST_TYPE'] = $query[$i]->MV_LIST_TYPE;
                $form2['DV_ESTIMATE_LIST_GRADE'] = $query[$i]->DV_ESTIMATE_LIST_GRADE;
                $form2['PHONE'] = $query[$i]->PHONE;

                if (substr($query[$i]->PHONE, 0, 1) == '+') {
                    $form2['PHONE'] = '0' . substr($query[$i]->PHONE, 3);
                }

                if($query[$i]->CHK > 0){
                    $form2['CHK'] = 1;
                } else {
                    $form2['CHK'] = $query[$i]->CHK;
                }

                if (!($nobiz == 'true')) {
                    if ($grade < $query[$i]->DV_ESTIMATE_LIST_GRADE) {
                        $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->DV_ESTIMATE_LIST_GRADE;
                    }
                }

                if(count($result)){
                    for($j=0; $j<count($result); $j++){

                        if($result[$j]['shortId'] == $query[$i]->DV_ESTIMATE_LIST_ID){
                            if ($result[$j]['click_open'] == null || $result[$j]['click_open'] == 0) {
                                $form2['CLICKED'] = false;
                            } else if ($result[$i]['click_open'] == 1) {
                                $form2['CLICKED'] = true;
                            }

                            if ($result[$j]['click_call'] == null || $result[$j]['click_call'] == 0) {
                                $form2['BIDDING_CALL'] =false;
                            } else if ($result[$i]['click_call'] == 1) {
                                $form2['BIDDING_CALL'] =true;
                            }

                            if ($result[$j]['click_reply'] == null || $result[$j]['click_reply'] == 0) {
                                $form2['BIDDING_REPLY'] = false;
                            } else if ($result[$j]['click_reply'] == 1) {
                                $form2['BIDDING_REPLY'] = true;
                            }
                        } else {
                            $form2['CLICKED'] = false;
                            $form2['BIDDING_CALL'] =false;
                            $form2['BIDDING_REPLY'] = false;
                        }
                    }
                }
                array_push($data, $form2);
            }
            return response()->json( array( "success"=>true, "data" => $data, "empty" => false ) );
        } else {
            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
        }

//        if(!isset($query)){
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
//        } else {
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => false ) );
//        }

//        return response()->json( array( "success"=>true, "data" => $query ) );

//        $queryCount = "SELECT
//					COUNT(1) AS COUNT
//				FROM DV_ESTIMATE_LIST
//				LEFT JOIN DV_ESTIMATE_CALLED ON DV_ESTIMATE_LIST_ID=DV_ESTIMATE_CALLED_FK_LIST
//				WHERE DV_ESTIMATE_LIST_USEYN='Y' AND DV_ESTIMATE_CALLED_FK_MV_LIST='" . $companyId . "'";

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

        if ($nobiz == 'true') {
            $queryCount .= " AND DV_ESTIMATE_LIST_CALL = 0 ";
        }


        $resultCount = DB::select( DB::raw($queryCount) );

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT/25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json( array( "success"=>true, "data" => $query , "page" => $Count ) );


    }

    public function EstimateInsert(Request $request)
    {
        $date = $request->date;
        $startAddress_si = $request->startAddress_si;
        $startAddress_gu = $request->startAddress_gu;
        $startAddress_dong = $request->startAddress_dong;
        $endAddress_si = $request->endAddress_si;
        $endAddress_gu = $request->endAddress_gu;
        $endAddress_dong = $request->endAddress_dong;
        $worker = $request->worker;
        $narrowAlley = $request->narrowAlley;
        $ladder = $request->ladder;
        $detail = $request->detail;
        $user_id = $request->user_id;

        DB::beginTransaction();

        if ($startAddress_si == "" || $startAddress_si == null) {
            $startAddress_si = 0;
        }

        if ($endAddress_si == "" || $endAddress_si == null) {
            $endAddress_si = 0;
        }

        $query = DV_ESTIMATE_LIST::selectRaw('count(DV_ESTIMATE_LIST_FK_USER) AS COUNT')
            ->where('DV_ESTIMATE_LIST_FK_USER', '=', $user_id)->get();

        if ($query[0]->COUNT > 0) {
            $query = DB::update(DB::raw('UPDATE DV_ESTIMATE_LIST SET DV_ESTIMATE_LIST_USEYN="N" WHERE DV_ESTIMATE_LIST_FK_USER=' . $user_id));
        }

        $address = array();
        array_push($address, $startAddress_si);
        array_push($address, $startAddress_gu);

        array_push($address, $endAddress_si);
        array_push($address, $endAddress_gu);

        for ($i = 0; $i < count($address); $i++) {
            if (!empty($address[$i])) {

                if ($i == 0 || $i == 2) {
//                    $query = 'SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE="'.$address[$i].'" AND PARENT_CODE IS NULL;';
                    $query = CODE_AREA::selectRaw('CODE_NAME')->where('CODE_VALUE', '=', $address[$i])->whereNull('PARENT_CODE')->get();
                } else if ($i == 1) {
//                    $query = 'SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE="'.$address[$i].'" AND PARENT_CODE = "'.$address[0].'";';
                    $query = CODE_AREA::selectRaw('CODE_NAME')->where('CODE_VALUE', '=', $address[$i])->where('PARENT_CODE', '=', $address[0])->get();
                } else if ($i == 3) {
//                    $query = 'SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE="'.$address[$i].'" AND PARENT_CODE = "'.$address[2].'";';
                    $query = CODE_AREA::selectRaw('CODE_NAME')->where('CODE_VALUE', '=', $address[$i])->where('PARENT_CODE', '=', $address[2])->get();
                }

                if ($i == 0) {
                    $startAddress_si = $query[0]->CODE_NAME;
                } else if ($i == 1) {
                    $startAddress_gu = $query[0]->CODE_NAME;

                    $startAddress = $startAddress_si . ' ' . $startAddress_gu;
                } else if ($i == 2) {
                    $endAddress_si = $query[0]->CODE_NAME;
                } else if ($i == 3) {
                    $endAddress_gu = $query[0]->CODE_NAME;

                    $endAddress = $endAddress_si . ' ' . $endAddress_gu;
                }
            }
        }

        $query = 'INSERT INTO DV_ESTIMATE_LIST(DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE, DV_ESTIMATE_LIST_START_ADDR_SI, DV_ESTIMATE_LIST_START_ADDR_GU, DV_ESTIMATE_LIST_START_ADDR_DONG,
                    DV_ESTIMATE_LIST_END_ADDR_SI, DV_ESTIMATE_LIST_END_ADDR_GU, DV_ESTIMATE_LIST_END_ADDR_DONG,
                    DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER, DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN,
                    DV_ESTIMATE_LIST_CALL, DV_ESTIMATE_LIST_START_ADDR_KR, DV_ESTIMATE_LIST_END_ADDR_KR)
                    VALUES(null, "' . $date . '", now(), "' . $startAddress_si . '", "' . $startAddress_gu . '", "' . $startAddress_dong . '",
                    "' . $endAddress_si . '", "' . $endAddress_gu . '", "' . $endAddress_dong . '", "' . $worker . '",
                    "' . $narrowAlley . '", "' . $ladder . '", "' . $detail . '", ' . $user_id . ', "N", "Y", "0",
                    "' . $startAddress . '", "' . $endAddress . '" );';
        $query = DB::statement(DB::raw($query));

        if ($query) {
            DB::commit();
            return response()->json(array('data' => true));
        } else {
            DB::rollback();
            return response()->json(array('data' => false));
        }
    }

    public function User_my_estimate(Request $request)
    {
        $user_id = $request->user_id;

        $query = DV_ESTIMATE_LIST::selectRaw('DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE, DV_ESTIMATE_LIST_START_ADDR_SI, DV_ESTIMATE_LIST_START_ADDR_GU, DV_ESTIMATE_LIST_START_ADDR_DONG,
                  DV_ESTIMATE_LIST_END_ADDR_SI, DV_ESTIMATE_LIST_END_ADDR_GU, DV_ESTIMATE_LIST_END_ADDR_DONG,
                  DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER, DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN')
            ->where('DV_ESTIMATE_LIST_FK_USER', '=', $user_id)
            ->orderBy('DV_ESTIMATE_LIST_ID', 'desc')
            ->skip(0)->take(1)->get();
        if (!empty($query[0])) {
            $form = array();
            $result = array();

            $form['id'] = $query[0]->DV_ESTIMATE_LIST_ID;
            $form['date'] = $query[0]->DV_ESTIMATE_LIST_MOVE_DATE;
            $form['reg_date'] = $query[0]->DV_ESTIMATE_LIST_REG_DATE;
            $form['startAddress'] = $this->CodeAddress($query[0]->DV_ESTIMATE_LIST_START_ADDR_SI, $query[0]->DV_ESTIMATE_START_ADDR_GU, $query[0]->DV_ESTIMATE_LIST_START_ADDR_DONG);
            $form['endAddress'] = $this->CodeAddress($query[0]->DV_ESTIMATE_LIST_END_ADDR_SI, $query[0]->DV_ESTIMATE_END_ADDR_GU, $query[0]->DV_ESTIMATE_LIST_END_ADDR_DONG);
            $form['worker'] = $query[0]->DV_ESTIMATE_LIST_NEED_PEOPLE;
            $form['narrowAlley'] = $query[0]->DV_ESTIMATE_LIST_OPT_1;
            $form['ladder'] = $query[0]->DV_ESTIMATE_LIST_OPT_2;

            if (empty($query[0]->DV_ESTIMATE_LIST_ETC_INFO)) {
                $form['detail'] = "";
            } else {
                $form['detail'] = $query[0]->DV_ESTIMATE_LIST_ETC_INFO;
            }

            $form['user_id'] = $query[0]->DV_ESTIMATE_LIST_FK_USER;
            $form['finish'] = $query[0]->DV_ESTIMATE_LIST_FINISH;
            $form['use_yn'] = $query[0]->DV_ESTIMATE_LIST_USEYN;

            array_push($result, $form);

            return response()->json(array('data' => $result));
        } else {
            return response()->json(array('data' => false));
        }

    }

    function CodeAddress($si, $gu, $dong)
    {

        if ($dong == "") {
            $query = "SELECT
                      CONCAT(
                      (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = '" . $si . "' AND PARENT_CODE IS NULL), ' ',
                      (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = '" . $gu . "' AND PARENT_CODE = '" . $si . "')
                      ) AS ADDR
                      FROM DUAL;";
        } else {
            $query = "SELECT
                      CONCAT(
                      (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = '" . $si . "' AND PARENT_CODE IS NULL), ' ',
                      (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = '" . $gu . "' AND PARENT_CODE = '" . $si . "'), ' ',
                      (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = '" . $dong . "' AND PARENT_CODE = '" . $si . $gu . "')
                      ) AS ADDR
                      FROM DUAL;";
        }
        $query = DB::select(DB::raw($query));

        if (!empty($query[0]->ADDR)) {

            return $query[0]->ADDR;
        }
    }

    public function company_estimate(Request $request)
    {
        $user_id = $request->user_id;

        $query = DV_ESTIMATE_LIST::leftJoin('DV_ESTIMATE_CALLED', 'DV_ESTIMATE_LIST_ID', '=', 'DV_ESTIMATE_CALLED_FK_LIST')
            ->selectRaw('DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE, DV_ESTIMATE_LIST_START_ADDR_KR AS START_AREA,
                                              DV_ESTIMATE_LIST_END_ADDR_KR AS END_AREA,
                                              DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER, DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN, DV_ESTIMATE_LIST_CALL,
                                              (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DV_ESTIMATE_LIST_FK_USER) AS PHONE')
            ->where('DV_ESTIMATE_LIST_USEYN', '=', 'Y')
            ->where('DV_ESTIMATE_CALLED_FK_MV_LIST', '=', $user_id)->get();

        if (!empty($query[0])) {
            $form = array();
            $result = array();
            for ($i = 0; $i < count($query); $i++) {
                $form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
                $form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
                $form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
                $form['startAddress'] = $query[$i]->START_AREA;
                $form['endAddress'] = $query[$i]->END_AREA;
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
                $form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;

                if (empty($query[$i]->PHONE)) {
                    $form['phone'] = "";
                } else {
                    $form['phone'] = $query[$i]->PHONE;
                }

                $form['permission'] = "1";

                array_push($result, $form);
            }

            return response()->json(array('data' => $result));
        } else {
            return response()->json(array('data' => false));
        }
    }

    public function company_estimate_new(Request $request)
    {
        $user_id = $request->user_id;

        $query = DV_ESTIMATE_LIST::leftJoin('DV_ESTIMATE_CALLED', 'DV_ESTIMATE_LIST_ID', '=', 'DV_ESTIMATE_CALLED_FK_LIST')
            ->selectRaw('DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE, DV_ESTIMATE_LIST_START_ADDR_KR AS START_A,
                                              DV_ESTIMATE_LIST_END_ADDR_KR AS END_A,
                                              DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER, DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN, DV_ESTIMATE_LIST_CALL,
                                              (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DV_ESTIMATE_LIST_FK_USER) AS PHONE')
            ->where('DV_ESTIMATE_LIST_USEYN', '=', 'Y')
            ->where('DV_ESTIMATE_CALLED_FK_MV_LIST', '=', $user_id)->get();

        if (count($query)) {


            return response()->json(array('data' => $query));
        } else {
            return response()->json(array('data' => false));
        }
    }

    public function user_estimate_list(Request $request)
    {
        $query = DV_ESTIMATE_LIST::selectRaw('DV_ESTIMATE_LIST_ID, DV_ESTIMATE_LIST_MOVE_DATE, DV_ESTIMATE_LIST_REG_DATE, DV_ESTIMATE_LIST_START_ADDR_KR AS startAddress,
                  DV_ESTIMATE_LIST_END_ADDR_KR AS endAddress,
                  DV_ESTIMATE_LIST_NEED_PEOPLE, DV_ESTIMATE_LIST_OPT_1, DV_ESTIMATE_LIST_OPT_2, DV_ESTIMATE_LIST_ETC_INFO, DV_ESTIMATE_LIST_FK_USER, DV_ESTIMATE_LIST_FINISH, DV_ESTIMATE_LIST_USEYN, DV_ESTIMATE_LIST_CALL')
            ->where('DV_ESTIMATE_LIST_USEYN', '=', 'Y')
            ->orderBy('DV_ESTIMATE_LIST_ID', 'desc')->get();

        if (empty($query)) {
            return response()->json(array('data' => false));
        } else {
            return response()->json(array('data' => $query));
        }
    }

    public function company_estimate_list(Request $request){
        $user_id = $request->user_id;

//        $query = 'SELECT T.*,
//                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = START_AREA_SI AND MR.MV_REGI_SI = START_AREA_GU AND MR.MV_REGI_FK_LIST = "'.$user_id.'" ) AS START_COMPANY_PERMISSION,
//                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = END_AREA_SI AND MR.MV_REGI_SI = END_AREA_GU AND MR.MV_REGI_FK_LIST = "'.$user_id.'" ) AS END_COMPANY_PERMISSION,
//                      (SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID="'.$user_id.'") AS TYPE
//                  FROM
//                  (
//                    select
//                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
//                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
//                        DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_ADDRESS, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_ADDRESS,
//                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
//                        DE.DV_ESTIMATE_LIST_CALL
//                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y"
//                  ) AS T order by T.DV_ESTIMATE_LIST_ID desc;';

        $query = 'SELECT T.*,
                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = START_AREA_SI AND MR.MV_REGI_SI = START_AREA_GU AND MR.MV_REGI_FK_LIST = "'.$user_id.'" ) AS START_COMPANY_PERMISSION,
                      (SELECT COUNT(1) FROM MV_REGI AS MR where MR.MV_REGI_DO = END_AREA_SI AND MR.MV_REGI_SI = END_AREA_GU AND MR.MV_REGI_FK_LIST = "'.$user_id.'" ) AS END_COMPANY_PERMISSION,
                      (SELECT MV_LIST_TYPE FROM MV_LIST WHERE MV_LIST_ID="'.$user_id.'") AS TYPE
                  FROM
                  (
                    select
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1, DE.DV_ESTIMATE_LIST_OPT_2, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = DE.DV_ESTIMATE_LIST_START_ADDR_SI AND PARENT_CODE IS NULL) AS START_AREA_SI,
                        (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = DE.DV_ESTIMATE_LIST_START_ADDR_GU AND PARENT_CODE = DE.DV_ESTIMATE_LIST_START_ADDR_SI) AS START_AREA_GU,
                        (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = DE.DV_ESTIMATE_LIST_END_ADDR_SI AND PARENT_CODE IS NULL) AS END_AREA_SI,
                        (SELECT CODE_NAME FROM CODE_AREA WHERE CODE_VALUE = DE.DV_ESTIMATE_LIST_END_ADDR_GU AND PARENT_CODE = DE.DV_ESTIMATE_LIST_END_ADDR_SI) AS END_AREA_GU,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL, DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_ADDRESS, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_ADDRESS
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y"
                  ) AS T order by T.DV_ESTIMATE_LIST_ID desc;';

        $query = DB::select( DB::raw($query) );

        if(!empty($query)){
            $form = array();
            $result = array();
            for($i=0; $i<count($query); $i++){
                $form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
                $form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
                $form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
                $form['worker'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
                $form['narrowAlley'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
                $form['ladder'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;

                if(empty($query[$i]->DV_ESTIMATE_LIST_ETC_INFO)){
                    $form['detail'] = "";
                } else {
                    $form['detail'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
                }

                $form['user_id'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
                $form['finish'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
                $form['use_yn'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;
                $form['startAddress'] = $query[$i]->START_ADDRESS;
                $form['endAddress'] = $query[$i]->END_ADDRESS;
                $form['phone'] = $query[$i]->PHONE;

                if($query[$i]->START_COMPANY_PERMISSION == '0' && $query[$i]->END_COMPANY_PERMISSION == '0'){
                    $form['permission'] = "0";
                } else {
                    if($query[$i]->TYPE == 0 || $query[$i]->TYPE == 1){
                        $form['permission'] = "0";
                    } else {
                        $form['permission'] = "1";
                    }
                }

                $form['type'] = $query[$i]->TYPE;
                $form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;

                array_push($result, $form);
            }

            return response()->json(array('data' => $query));
        } else {
            return response()->json(array('data' => false));
        }
    }

    public function user_call(Request $request){
        $user_id = $request->user_id;
        $phone = $request->phone;
        $company_id = $request->company_id;
        $priority = $request->priority;

        if($priority == 0 || $priority == 1){
            $query = 'INSERT INTO POINT_TOTAL(ID, UserID, Category, outPoint, Content, MV_CL, RegDate, MngCode, Money)
                          VALUES(null, "'.$company_id.'", "PO_OUT01", 0, "소비자 콜/추천업체/'.$phone.'/'.$user_id.'", "DV", NOW(), 0, 0);';
        } else {
            $query = 'INSERT INTO POINT_TOTAL(ID, UserID, Category, outPoint, Content, MV_CL, RegDate, MngCode, Money)
                          VALUES(null, "'.$company_id.'", "PO_OUT01", 0, "소비자 콜/'.$phone.'/'.$user_id.'", "DV", NOW(), 0, 0);';
        }

        $query = DB::statement( DB::raw($query) );

        if($query){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }

    function CompanyList(Request $request){
        $doName = $request->doName;
        $siName = $request->siName;
    }

    function EstimateDetail(Request $request){
        $listId = $request->listId;

        $query = 'SELECT
                        DE.DV_ESTIMATE_LIST_ID, DE.DV_ESTIMATE_LIST_MOVE_DATE, DE.DV_ESTIMATE_LIST_REG_DATE,
                        DE.DV_ESTIMATE_LIST_NEED_PEOPLE, DE.DV_ESTIMATE_LIST_OPT_1 AS NARROW, DE.DV_ESTIMATE_LIST_OPT_2 AS LADDER, DE.DV_ESTIMATE_LIST_ETC_INFO, DE.DV_ESTIMATE_LIST_FK_USER, DE.DV_ESTIMATE_LIST_FINISH, DE.DV_ESTIMATE_LIST_USEYN,
                        (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DE.DV_ESTIMATE_LIST_FK_USER) AS PHONE,
                        DE.DV_ESTIMATE_LIST_CALL, DE.DV_ESTIMATE_LIST_START_ADDR_KR AS START_A, DE.DV_ESTIMATE_LIST_END_ADDR_KR AS END_A
                    from DV_ESTIMATE_LIST AS DE WHERE DE.DV_ESTIMATE_LIST_USEYN="Y" AND DE.DV_ESTIMATE_LIST_ID=' . $listId;

        $query = DB::select( DB::raw($query) );

        if(!empty($query)){
            return response()->json(array('data' => $query));
        } else {
            return response()->json(array('data' => false));
        }



//        if(!empty($query)){
//            $form = array();
//            $data = array();
//            for($i=0; $i<count($query); $i++){
//                $form['id'] = $query[$i]->DV_ESTIMATE_LIST_ID;
//                $form['date'] = $query[$i]->DV_ESTIMATE_LIST_MOVE_DATE;
//                $form['reg_date'] = $query[$i]->DV_ESTIMATE_LIST_REG_DATE;
//                $form['worker'] = $query[$i]->DV_ESTIMATE_LIST_NEED_PEOPLE;
//                $form['narrowAlley'] = $query[$i]->DV_ESTIMATE_LIST_OPT_1;
//                $form['ladder'] = $query[$i]->DV_ESTIMATE_LIST_OPT_2;
//
//                if (empty($query[$i]->DV_ESTIMATE_LIST_ETC_INFO)) {
//                    $form['detail'] = "";
//                } else {
//                    $form['detail'] = $query[$i]->DV_ESTIMATE_LIST_ETC_INFO;
//                }
//
//                $form['user_id'] = $query[$i]->DV_ESTIMATE_LIST_FK_USER;
//                $form['finish'] = $query[$i]->DV_ESTIMATE_LIST_FINISH;
//                $form['use_yn'] = $query[$i]->DV_ESTIMATE_LIST_USEYN;
//
//                $form['phone'] = $query[$i]->PHONE;
//                $form['call'] = $query[$i]->DV_ESTIMATE_LIST_CALL;
//                $form['startAddress'] = $query[$i]->DV_ESTIMATE_LIST_START_ADDR_KR;
//                $form['endAddress'] = $query[$i]->DV_ESTIMATE_LIST_END_ADDR_KR;
//
//                array_push($data, $form);
//            }
//
//            return response()->json(array('data' => $data));
//
//        } else {
//            return response()->json(array('data' => false));
//        }
    }

    function GradeCheck(Request $request){
        $companyId = $request->companyId;
        $listId = $request->listId;

        $query = 'SELECT MV_LIST_GRADE FROM MV_LIST WHERE MV_LIST_ID='.$companyId.';';
        $query = DB::select( DB::raw( $query ) );

        $query2 = 'SELECT DV_ESTIMATE_LIST_GRADE FROM DV_ESTIMATE_LIST WHERE DV_ESTIMATE_LIST_ID='.$listId;
        $query2 = DB::select( DB::raw( $query2 ) );

        if($query[0]->MV_LIST_GRADE >= $query2[0]->DV_ESTIMATE_LIST_GRADE){
            return response()->json(array('data' => true));
        } else {
            return response()->json(array('data' => false));
        }
    }
}
