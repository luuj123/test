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
use App\MV_LIST;

class AMovingEstimateList extends Controller
{

    private $senderKey = '643222627340';
    private $apiKey = 'AIzaSyDs5xZ6Qn2CC3XJvF8bDaT4foBP-hUydhs';
    private $newSenderKey = '480942233848';
    private $newApiKey = 'AIzaSyB9NJW4-HInkcgkjagB3oq-nQ5qUmONsGs';
    private $masterSenderKey = '466875261460';
    private $masterApiKey = 'AIzaSyDE49HqOF7g8cQnqaggLkOTKYqAL0-gQAk';

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

        if ($page == '' || $page == null || $page == 0) {
            $page = 1;
        }
        $skip = ($page - 1) * 25;
        $take = 25;

        $query = 'select MV_LIST_TYPE, MV_LIST_GRADE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
            $grade = $query[0]->MV_LIST_GRADE;
        }

        $query = 'SELECT ESTIMATE_CLICK_FK_SHORT, ESTIMATE_CLICK_OPEN, ESTIMATE_CLICK_BIDDING_CALL, ESTIMATE_CLICK_BIDDING_REPLY FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY=' . $companyId . ' AND ESTIMATE_CLICK_MV_DV="MV";';
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

//        $query = "SELECT
//					T.*,
//					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
//				FROM
//				(
//					select
//						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
//						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
//						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
//						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE
//					from ESTIMATE_SHORT AS ES
//					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
//					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date_format(ES.ESTIMATE_SHORT_REGDATE, '%y.%m.%d') > '15.12.31' AND (ES.ESTIMATE_SHORT_ALLIANCE != 'T' OR ES.ESTIMATE_SHORT_ALLIANCE != 'TE' OR ES.ESTIMATE_SHORT_ALLIANCE IS NULL)
//				) AS T
//				WHERE 1=1 order by ESTIMATE_SHORT_ID desc LIMIT ".$skip.",".$take;

        $query = "SELECT
					T.*,
					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='" . $companyId . "' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
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
				WHERE 1=1 ";

        if ($start_area == "" && $end_area == "") {

        } else if (!$start_area == "" && $end_area == "") {
            $query .= "AND ESTIMATE_ADDR_START LIKE '%" . $start_area . "%'";
        } else if ($start_area == "" && !$end_area == "") {
            $query .= "AND ESTIMATE_ADDR_END LIKE '%" . $end_area . "%'";
        } else if (!$start_area == "" && !$end_area == "") {
            $query .= "AND ESTIMATE_ADDR_START LIKE '%" . $start_area . "%' AND ESTIMATE_ADDR_END LIKE '%" . $end_area . "%'";
        }

        if ($start_regdate == "" && $end_regdate == "") {

        } else if (!$start_regdate == "" && $end_regdate == "") {
            $query .= "AND date(ESTIMATE_SHORT_REGDATE) >= date('" . $start_regdate . "') ";
        } else if ($start_regdate == "" && !$end_regdate == "") {
            $query .= "AND date(ESTIMATE_SHORT_REGDATE) <= date('" . $end_regdate . "') ";
        } else if (!$start_regdate == "" && !$end_regdate == "") {
            $query .= "AND date(ESTIMATE_SHORT_REGDATE) >= date('" . $start_regdate . "') AND date(ESTIMATE_SHORT_REGDATE) <= date('" . $end_regdate . "') ";
        }


        if (!$start_movedate == "" && $end_movedate == "") {
            $query .= "AND date(ESTIMATE_SHORT_MOVE_DATE) >= date('" . $start_movedate . "') ";
        } else if ($start_movedate == "" && !$end_movedate == "") {
            $query .= "AND date(ESTIMATE_SHORT_MOVE_DATE) <= date('" . $end_movedate . "') ";
        } else if (!$start_movedate == "" && $end_movedate == "") {
            $query .= "AND date(ESTIMATE_SHORT_MOVE_DATE) >= date('" . $start_movedate . "') ";
        } else if (!$start_movedate == "" && !$end_movedate == "") {
            $query .= "AND date(ESTIMATE_SHORT_MOVE_DATE) >= date('" . $start_movedate . "') AND date(ESTIMATE_SHORT_MOVE_DATE) <= date('" . $end_movedate . "') ";
        }

        if ($nobiz == 'true') {
            $query .= "AND ESTIMATE_SHORT_REPLIES = 0 AND ESTIMATE_SHORT_PHONE != '3' AND " . $grade . " >= ESTIMATE_SHORT_GRADE";
        }

        $query .= " ORDER BY ESTIMATE_SHORT_ID DESC LIMIT " . $skip . "," . $take;

        $query = DB::select(DB::raw($query));

        if (!isset($query)) {
            return response()->json(array("success" => true, "data" => $query, "empty" => true));
        } else {
            $data = array();
            $form2 = array();
            for ($i = 0; $i < count($query); $i++) {
//                if ($query[$i]->CHK > 0) {
//                    $query[$i]->CHK = 1;
//                }
//
//                if (!($nobiz == 'true')) {
//                    if ($grade < $query[$i]->ESTIMATE_SHORT_GRADE) {
//                        $query[$i]->ESTIMATE_SHORT_REPLIES = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
//                    }
//                }
//
//                if ($query[$i]->CLICKED == null || $query[$i]->CLICKED == 0) {
//                    $query[$i]->CLICKED = false;
//                } else if ($query[$i]->CLICKED == 1) {
//                    $query[$i]->CLICKED = true;
//                }
//
//                if ($query[$i]->BIDDING_CALL == null || $query[$i]->BIDDING_CALL == 0) {
//                    $query[$i]->BIDDING_CALL = false;
//                } else if ($query[$i]->BIDDING_CALL == 1) {
//                    $query[$i]->BIDDING_CALL = true;
//                }
//
//                if ($query[$i]->BIDDING_REPLY == null || $query[$i]->BIDDING_REPLY == 0) {
//                    $query[$i]->BIDDING_REPLY = false;
//                } else if ($query[$i]->BIDDING_REPLY == 1) {
//                    $query[$i]->BIDDING_REPLY = true;
//                }

                $form2['ESTIMATE_ADDR_FK_LIST'] = $query[$i]->ESTIMATE_ADDR_FK_LIST;
                $form2['ESTIMATE_SHORT_ID'] = $query[$i]->ESTIMATE_SHORT_ID;
                $form2['ESTIMATE_SHORT_COUNT'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                $form2['ESTIMATE_SHORT_MAX_REPLIES'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                $form2['ESTIMATE_SHORT_KIND'] = $query[$i]->ESTIMATE_SHORT_KIND;
                $form2['ESTIMATE_SHORT_DATE'] = $query[$i]->ESTIMATE_SHORT_DATE;
                $form2['ESTIMATE_SHORT_MOVE_DATE'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                $form2['ESTIMATE_SHORT_PHONE'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                $form2['ESTIMATE_ADDR_START'] = $query[$i]->ESTIMATE_ADDR_START;
                $form2['ESTIMATE_ADDR_END'] = $query[$i]->ESTIMATE_ADDR_END;
                $form2['ESTIMATE_SHORT_REGDATE'] = $query[$i]->ESTIMATE_SHORT_REGDATE;
                $form2['ESTIMATE_SHORT_GRADE'] = $query[$i]->ESTIMATE_SHORT_GRADE;

                if($query[$i]->CHK > 0){
                    $form2['CHK'] = 1;
                } else {
                    $form2['CHK'] = $query[$i]->CHK;
                }

                if (!($nobiz == 'true')) {
                    if ($grade < $query[$i]->ESTIMATE_SHORT_GRADE) {
                        $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                    }
                }

                if(count($result)){
                    for($j=0; $j<count($result); $j++){

                        if($result[$j]['shortId'] == $query[$i]->ESTIMATE_SHORT_ID){
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
                } else {
                    $form2['CLICKED'] = false;
                    $form2['BIDDING_CALL'] =false;
                    $form2['BIDDING_REPLY'] = false;
                }
                array_push($data, $form2);

            }

            return response()->json(array("success" => true, "data" => $data, "empty" => false));
        }


//        if(!empty($query)){
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => false ) );
//        } else {
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
//        }

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
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1
				) AS T WHERE 1=1 ";

        if ($type == "0") { //����
            $queryCount .= " AND ESTIMATE_SHORT_KIND != 4";
        } elseif ($type == "1") { //����
            $queryCount .= " AND ESTIMATE_SHORT_KIND = 1";
        } elseif ($type == "2") { //���
            $queryCount .= " AND ESTIMATE_SHORT_KIND = 4";
        } elseif ($type == "3") { //�̻���

        } elseif ($type == "4") { //������
            $queryCount .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
        }

        if ($start_area == "" && $end_area == "") {

        } else if (!$start_area == "" && $end_area == "") {
            $queryCount .= "AND ESTIMATE_ADDR_START LIKE '%" . $start_area . "%'";
        } else if ($start_area == "" && !$end_area == "") {
            $queryCount .= "AND ESTIMATE_ADDR_END LIKE '%" . $end_area . "%'";
        } else if (!$start_area == "" && !$end_area == "") {
            $queryCount .= "AND ESTIMATE_ADDR_START LIKE '%" . $start_area . "%' AND ESTIMATE_ADDR_END LIKE '%" . $end_area . "%'";
        }

        if ($start_regdate == "" && $end_regdate == "") {

        } else if (!$start_regdate == "" && $end_regdate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_REGDATE) >= date('" . $start_regdate . "') ";
        } else if ($start_regdate == "" && !$end_regdate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_REGDATE) <= date('" . $end_regdate . "') ";
        } else if (!$start_regdate == "" && !$end_regdate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_REGDATE) >= date('" . $start_regdate . "') AND date(ESTIMATE_SHORT_REGDATE) <= date('" . $end_regdate . "') ";
        }


        if (!$start_movedate == "" && $end_movedate == "") {

        } else if ($start_movedate == "" && !$end_movedate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_MOVE_DATE) <= date('" . $end_movedate . "') ";
        } else if (!$start_movedate == "" && $end_movedate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_MOVE_DATE) >= date('" . $start_movedate . "') ";
        } else if (!$start_movedate == "" && !$end_movedate == "") {
            $queryCount .= "AND date(ESTIMATE_SHORT_MOVE_DATE) >= date('" . $start_movedate . "') AND date(ESTIMATE_SHORT_MOVE_DATE) <= date('" . $end_movedate . "') ";
        }

        if ($nobiz == 'true') {
            $queryCount .= "AND ESTIMATE_SHORT_REPLIES = 0 AND ESTIMATE_SHORT_PHONE != '3' AND " . $grade . " >= ESTIMATE_SHORT_GRADE";
        }

        $resultCount = DB::select(DB::raw($queryCount));

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT / 25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json(array("success" => true, "data" => $query, "page" => $Count));

//        for ($i = 0; $i < count($query); $i++) {
//
//            if ($type == 2) {
//
//                $query[$i]->START_REGI = 0;
//                $query[$i]->END_REGI = 0;
//            } else if ($type == 1 || $type == 4) {
//                if ($query[$i]->ESTIMATE_SHORT_KIND == 1) {
//
//                } else {
//                    $query[$i]->START_REGI = 0;
//                    $query[$i]->END_REGI = 0;
//                }
//            }
//
//            if ($query[$i]->START_REGI == 0 && $query[$i]->END_REGI == 0) {
//
//            } else {
//                array_push($result, $query[$i]);
//            }
//
//
//        }

//        for($i=0; $i<count($query); $i++){
//            var_dump("1");
//        }

//        $queries = DB::getQueryLog();
//        var_dump($queries);

        return response()->json(array('data' => $query));
//        return response()->json($query);
    }

    public function EstimateList(Request $request)
    {

        $companyId = $request->companyId;
        $nobiz = $request->nobiz;
        $page = $request->PageNo;

        if ($page == '' || $page == null || $page == 0) {
            $page = 1;
        }
        $skip = ($page - 1) * 25;
        $take = 25;

        $query = 'select MV_LIST_TYPE, MV_LIST_GRADE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
            $grade = $query[0]->MV_LIST_GRADE;
        }

        $query = 'SELECT ESTIMATE_CLICK_FK_SHORT, ESTIMATE_CLICK_OPEN, ESTIMATE_CLICK_BIDDING_CALL, ESTIMATE_CLICK_BIDDING_REPLY FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY=' . $companyId . ' AND ESTIMATE_CLICK_MV_DV="MV";';
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

        $query = "SELECT
					T.*,
					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
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
				WHERE (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='".$companyId."' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) > 0 ";

//        $query = "SELECT
//					T.*,
//					(SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='" . $companyId . "' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) AS CHK
//				FROM
//				(
//					select
//						EA.ESTIMATE_ADDR_FK_LIST, ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_COUNT,
//						ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
//						date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PHONE,
//						TRIM(EA.ESTIMATE_ADDR_START) AS ESTIMATE_ADDR_START, TRIM(EA.ESTIMATE_ADDR_END) AS ESTIMATE_ADDR_END, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE,
//						ESTIMATE_CLICK_OPEN AS CLICKED, ESTIMATE_CLICK_BIDDING_CALL AS BIDDING_CALL, ESTIMATE_CLICK_BIDDING_REPLY AS BIDDING_REPLY
//					from ESTIMATE_SHORT AS ES
//					LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
//					LEFT OUTER JOIN ESTIMATE_CLICK ON ESTIMATE_CLICK_FK_COMPANY=" . $companyId . " AND ESTIMATE_CLICK_FK_SHORT=ESTIMATE_SHORT_ID AND ESTIMATE_CLICK_MV_DV='MV'
//					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1 AND date_format(ES.ESTIMATE_SHORT_REGDATE, '%y.%m.%d') > '15.12.31'
//				) AS T
//				WHERE (SELECT COUNT(1) FROM MV_REGI WHERE MV_REGI_FK_LIST='" . $companyId . "' AND ( ESTIMATE_ADDR_START Like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MV_REGI_DO, ' ', MV_REGI_SI, '%') ) ) > 0 ";



        if ($type == "0") { //����
            $query .= " AND ESTIMATE_SHORT_KIND != 4";
        } elseif ($type == "1") { //����
            $query .= " AND ESTIMATE_SHORT_KIND = 1";
        } elseif ($type == "2") { //���
            $query .= " AND ESTIMATE_SHORT_KIND = 4";
        } elseif ($type == "3") { //�̻���

        } elseif ($type == "4") { //������
            $query .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
        }

        if ($nobiz == 'true') {
            $query .= "AND ESTIMATE_SHORT_REPLIES = 0 AND ESTIMATE_SHORT_PHONE != '3' AND " . $grade . " >= ESTIMATE_SHORT_GRADE";
        }

        $query .= "  ORDER BY ESTIMATE_SHORT_ID DESC LIMIT " . $skip . "," . $take;

        $query = DB::select(DB::raw($query));

        if (!isset($query)) {
            return response()->json(array("success" => true, "data" => $query, "empty" => true));
        } else {
            $form2 = array();
            $data = array();
            for ($i = 0; $i < count($query); $i++) {

                $form2['ESTIMATE_ADDR_FK_LIST'] = $query[$i]->ESTIMATE_ADDR_FK_LIST;
                $form2['ESTIMATE_SHORT_ID'] = $query[$i]->ESTIMATE_SHORT_ID;
                $form2['ESTIMATE_SHORT_COUNT'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                $form2['ESTIMATE_SHORT_MAX_REPLIES'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                $form2['ESTIMATE_SHORT_KIND'] = $query[$i]->ESTIMATE_SHORT_KIND;
                $form2['ESTIMATE_SHORT_DATE'] = $query[$i]->ESTIMATE_SHORT_DATE;
                $form2['ESTIMATE_SHORT_MOVE_DATE'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                $form2['ESTIMATE_SHORT_PHONE'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                $form2['ESTIMATE_ADDR_START'] = $query[$i]->ESTIMATE_ADDR_START;
                $form2['ESTIMATE_ADDR_END'] = $query[$i]->ESTIMATE_ADDR_END;
                $form2['ESTIMATE_SHORT_REGDATE'] = $query[$i]->ESTIMATE_SHORT_REGDATE;
                $form2['ESTIMATE_SHORT_GRADE'] = $query[$i]->ESTIMATE_SHORT_GRADE;

                if($query[$i]->CHK > 0){
                    $form2['CHK'] = 1;
                } else {
                    $form2['CHK'] = $query[$i]->CHK;
                }

                if (!($nobiz == 'true')) {
                    if ($grade < $query[$i]->ESTIMATE_SHORT_GRADE) {
                        $form2['ESTIMATE_SHORT_REPLIES'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                    }
                }

//                if ($query[$i]->CHK > 0) {
//                    $query[$i]->CHK = 1;
//                }
//
//                if (!($nobiz == 'true')) {
//                    if ($grade < $query[$i]->ESTIMATE_SHORT_GRADE) {
//                        $query[$i]->ESTIMATE_SHORT_REPLIES = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
//                    }
//                }

                if(count($result)){
                    for($j=0; $j<count($result); $j++){

                        if($result[$j]['shortId'] == $query[$i]->ESTIMATE_SHORT_ID){
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

                } else {
                    $form2['CLICKED'] = false;
                    $form2['BIDDING_CALL'] =false;
                    $form2['BIDDING_REPLY'] = false;
                }

                array_push($data, $form2);

//                if ($query[$i]->CLICKED == null || $query[$i]->CLICKED == 0) {
//                    $query[$i]->CLICKED = false;
//                } else if ($query[$i]->CLICKED == 1) {
//                    $query[$i]->CLICKED = true;
//                }
//
//                if ($query[$i]->BIDDING_CALL == null || $query[$i]->BIDDING_CALL == 0) {
//                    $query[$i]->BIDDING_CALL = false;
//                } else if ($query[$i]->BIDDING_CALL == 1) {
//                    $query[$i]->BIDDING_CALL = true;
//                }
//
//                if ($query[$i]->BIDDING_REPLY == null || $query[$i]->BIDDING_REPLY == 0) {
//                    $query[$i]->BIDDING_REPLY = false;
//                } else if ($query[$i]->BIDDING_REPLY == 1) {
//                    $query[$i]->BIDDING_REPLY = true;
//                }
            }

            return response()->json(array("success" => true, "data" => $data, "empty" => false));
        }

//        if(!empty($query)){
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => false ) );
//        } else {
//            return response()->json( array( "success"=>true, "data" => $query, "empty" => true ) );
//        }


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
					WHERE ES.ESTIMATE_SHORT_PUBLIC = 1
				) AS T
				LEFT OUTER JOIN MV_REGI AS MR ON MR.MV_REGI_FK_LIST = '" . $companyId . "' AND ( ESTIMATE_ADDR_START Like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%')  OR ESTIMATE_ADDR_END like CONCAT(MR.MV_REGI_DO, ' ', MR.MV_REGI_SI, '%') )
				WHERE 1=1 AND IF(MR.MV_REGI_ID IS NULL, '0', '1') = '1'";
        if ($type == "0") { //����
            $queryCount .= " AND ESTIMATE_SHORT_KIND != 4";
        } elseif ($type == "1") { //����
            $queryCount .= " AND ESTIMATE_SHORT_KIND = 1";
        } elseif ($type == "2") { //���
            $queryCount .= " AND ESTIMATE_SHORT_KIND = 4";
        } elseif ($type == "3") { //�̻���

        } elseif ($type == "4") { //������
            $queryCount .= " AND ( ESTIMATE_SHORT_KIND = 1 OR ESTIMATE_SHORT_KIND = 4 )";
        }

        if ($nobiz == 'true') {
            $queryCount .= "AND ESTIMATE_SHORT_REPLIES = 0 AND ESTIMATE_SHORT_PHONE != '3'";
        }

        $resultCount = DB::select(DB::raw($queryCount));

        $Count["pageNo"] = $page;
        $Count["pageCount"] = ceil($resultCount[0]->COUNT / 25);
        $Count["listCount"] = $resultCount[0]->COUNT;

        return response()->json(array("success" => true, "data" => $query, "page" => $Count));


        if (count($query) == 0) {
            return response()->json(array('data' => false));
        } else {
            return response()->json(array('data' => $query));
        }

//        return response()->json(array('data' => $result));
    }

    public function EstimateInsert(Request $request)
    {
        $id = $request->id;
        $phone = $request->phone;
        $title = $request->title;
        $content = $request->input('content');
        $start_add = $request->start_add;
        $end_add = $request->end_add;
        $start_ladder = $request->start_ladder;
        $end_ladder = $request->end_ladder;
        $kind = $request->kind;
        $people = $request->people;
        $room_size = $request->room_size;
        $air = $request->air;
        $bed = $request->stone_bed;
        $tv = $request->wall_tv;
        $piano = $request->piano;
        $wardrobe = $request->piece_wardrobe;
        $bidding = $request->bidding;
        $moving_date = $request->moving_date;
        $biddingType = $request->biddingType;
        $doName = $request->doName;
        $siName = $request->siName;
        $dongName = $request->dongName;
        $alliance = $request->alliance;

        $img_count = $request->img_count;
        $img_count = (int)$img_count;
        $images = array();
        for ($i = 0; $i < $img_count; $i++) {
            $images[$i]['img'] = (isset($_FILES["image_" . $i])) ? $_FILES["image_" . $i] : null;
        }

        DB::beginTransaction();

        $time = date("YmdHis");


        $year = substr($moving_date, 2, 2);
        $month = substr($moving_date, 5, 2);
        $date = substr($moving_date, 8, 2);
        $movingDate = $year . "." . $month . "." . $date;

        $query = 'INSERT INTO ESTIMATE_LIST VALUES (NULL, "' . $phone . '/' . $time . '", "' . $content . '",
        ' . $start_ladder . ', ' . $end_ladder . ', ' . $kind . ', ' . $people . ',
        ' . $room_size . ', ' . $air . ', ' . $bed . ', ' . $tv . ', ' . $piano . ',
        ' . $wardrobe . ', ' . $bidding . ', "' . $movingDate . '", ' . $biddingType . ', ' . $id . ', NOW());';

        $query = DB::statement(DB::raw($query));

        $query = 'SELECT ESTIMATE_LIST_ID FROM ESTIMATE_LIST WHERE ESTIMATE_LIST_FK_USER = ' . $id . ' ORDER BY ESTIMATE_LIST_ID DESC LIMIT 0, 1;';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $listId = $query[0]->ESTIMATE_LIST_ID;
        }

        $year = substr($time, 2, 2);
        $month = substr($time, 4, 2);
        $date = substr($time, 6, 2);
        $shortTime = $year . "." . $month . "." . $date;

        if ($bidding == 0) {
            $maxReplies = 2;
        } else if ($bidding == 1) {
            $maxReplies = 3;
        } else if ($bidding == 2) {
            $maxReplies = 5;
        }

        $grade = rand(1, 10);

        if (strpos($start_add, "울릉군") && strpos($end_add, "울릉군")) {

            if (empty($list['alliance'])) {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 0, ' . $biddingType . ', ' . $id . ', ' . $listId . ', NULL, NOW(), ' . $grade . ');';
            } else {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 0, ' . $biddingType . ', ' . $id . ', ' . $listId . ', "' . $alliance . '", NOW(), ' . $grade . ');';
            }

            $query = DB::statement(DB::raw($query));
        } else if (strpos($start_add, "울릉군") || strpos($end_add, "울릉군")) {

            if (empty($list['alliance'])) {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 0, ' . $biddingType . ', ' . $id . ', ' . $listId . ', NULL, NOW(), 11);';
            } else {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 0, ' . $biddingType . ', ' . $id . ', ' . $listId . ', "' . $alliance . '", NOW(), 11);';
            }

            $query = DB::statement(DB::raw($query));
        } else {
            if (empty($alliance)) {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 1, ' . $biddingType . ', ' . $id . ', ' . $listId . ', NULL, NOW(), ' . $grade . ');';
            } else {
                $query = 'INSERT INTO ESTIMATE_SHORT(ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_USER, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ALLIANCE, ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_GRADE)
                          VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 1, ' . $biddingType . ', ' . $id . ', ' . $listId . ', "' . $alliance . '", NOW(), ' . $grade . ');';
            }

//            $query = 'INSERT INTO ESTIMATE_SHORT VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $list['kind'] . ', "' . $shortTime . '", "' . $movingDate . '", 1, ' . $list['biddingType'] . ', ' . $list['id'] . ', ' . $listId . ', NULL, NOW());';
            $query = DB::statement(DB::raw($query));
        }

//        $query = 'INSERT INTO ESTIMATE_SHORT VALUES (NULL, 0, 0, ' . $maxReplies . ', ' . $kind . ', "' . $shortTime . '", "' . $movingDate . '", 1, ' . $biddingType . ', ' . $id . ', ' . $listId . ', NULL, NOW());';
//        DB::statement(DB::raw($query));

        $query = 'SELECT ESTIMATE_SHORT_ID, ESTIMATE_SHORT_ALLIANCE FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_FK_USER = ' . $id . ' ORDER BY ESTIMATE_SHORT_ID DESC LIMIT 0, 1;';
        $query = DB::select(DB::raw($query));

        if (count($query)) {
            $shortId = $query[0]->ESTIMATE_SHORT_ID;
            $alliance = $query[0]->ESTIMATE_SHORT_ALLIANCE;
        }

        $query = 'INSERT INTO ESTIMATE_REGI VALUES (NULL, "' . $doName . '", "' . $siName . '", "' . $dongName . '", 2, ' . $shortId . ');';
        DB::statement(DB::raw($query));

        $query = 'INSERT INTO ESTIMATE_ADDR VALUES (NULL, "' . trim($start_add) . '", "' . trim($end_add) . '", ' . $shortId . ', ' . $listId . ');';
        DB::statement(DB::raw($query));

        $this->makeUserFolder($phone);

        foreach ($images as $index => $value) {
            if ($value != null && isset($value)) {
                $name = $this->makeImage($value, $time, $phone);
                $query = 'INSERT INTO ESTIMATE_IMG(ESTIMATE_IMG_ID, ESTIMATE_IMG_ITEM, ESTIMATE_IMG_FK_LIST, ESTIMATE_IMG_URL) VALUES (NULL,"' . $name . '", ' . $listId . ', "http://vdt004.venditz.com/");';
                DB::statement(DB::raw($query));
            }
        }

        $startarray = explode(" ", trim($start_add));
        $startDo = $startarray[0];
        if (count($startarray) > 2) {
            $startSi = $startarray[1] . " " . $startarray[2];
        } else {
            $startSi = $startarray[1];
        }


        $endarray = explode(" ", trim($end_add));
        $endDo = $endarray[0];
        if (count($endarray) > 2) {
            $endSi = $endarray[1] . " " . $endarray[2];
        } else {
            $endSi = $endarray[1];
        }

        if ($kind == 0) {
            $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . '";';
            DB::statement(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startSi . '";';
            DB::statement(DB::raw($query));

            if (!$query) {
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set HOME_COUNT = HOME_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startarray[0] . '";';
                DB::statement(DB::raw($query));
            }
        } else if ($kind == 1) {
            $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . '";';
            DB::statement(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startSi . '";';
            DB::statement(DB::raw($query));

            if (!$query) {
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set ONE_COUNT = ONE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startarray[0] . '";';
                DB::statement(DB::raw($query));
            }
        } else if ($kind == 2) {
            $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . '";';
            DB::statement(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startSi . '";';
            DB::statement(DB::raw($query));

            if (!$query) {
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set OFFICE_COUNT = OFFICE_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startarray[0] . '";';
                DB::statement(DB::raw($query));
            }
        } else if ($kind == 3) {
            $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . '";';
            DB::statement(DB::raw($query));

            $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startSi . '";';
            DB::statement(DB::raw($query));

            if (!$query) {
                $startarray = explode(" ", trim($startSi));
                $query = 'update AREA_GEO_COUNT set KEEP_COUNT = KEEP_COUNT+1, UPDATE_DATE=now() where AREA_NAME = "' . $startDo . " " . $startarray[0] . '";';
                DB::statement(DB::raw($query));
            }
        }

        if ($alliance == "T" || $alliance == "TE") {
            CommonController::EstimateEnd($list['phone']);
        } else {

            $company = array();
            $companyNew = array();
            $companyMaster = array();

//        $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_COMPANY
//        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
//        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
//        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
//        LEFT JOIN USER_APP_VERSION ON MV_LIST.MV_LIST_ID = USER_APP_VERSION.USERID
//        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=2 or MV_LIST.MV_LIST_TYPE=3) AND USER_APP_VERSION.APPVERSION="2.0.3"
//        GROUP BY MV_LIST.MV_LIST_ID;';

            if ($kind == 1) {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                $query = DB::select(DB::raw($query));
            } else {
                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                $query = DB::select(DB::raw($query));
            }

//        $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_COMPANY
//        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
//        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
//        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
//        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y"
//        GROUP BY MV_LIST.MV_LIST_ID;';
//        $mySql->query($query);

            if (count($query)) {

                for ($i = 0; $i < count($query); $i++) {
                    if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->newSenderKey){
                        array_push($companyNew, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->masterSenderKey){
                        array_push($companyMaster, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                    } else {
                        array_push($company, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                    }
                }
            } else {
                $endarray = explode(" ", trim($endSi));

                if ($kind == 1) {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                    $query = DB::select(DB::raw($query));
                } else {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                    $query = DB::select(DB::raw($query));
                }

//            $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_COMPANY
//        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
//        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
//        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
//        WHERE MV_REGI_DO = "' . $endDo . '" AND MV_REGI_SI="' . $endarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=2 or MV_LIST.MV_LIST_TYPE=3)
//        GROUP BY MV_LIST.MV_LIST_ID;';
//            $mySql->query($query);

                if (count($query)) {

                    for ($i = 0; $i < count($query); $i++) {
                        if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->newSenderKey){
                            array_push($companyNew, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->masterSenderKey){
                            array_push($companyMaster, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else {
                            array_push($company, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        }
                    }
                }
            }


            if (!(($endDo . " " . $endSi) == ($startDo . " " . $startSi))) {

                if ($kind == 1) {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                    $query = DB::select(DB::raw($query));
                } else {
                    $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                    $query = DB::select(DB::raw($query));
                }

//            $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_COMPANY
//        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
//        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
//        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
//        WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startSi . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y"
//        GROUP BY MV_LIST.MV_LIST_ID;';
//            $mySql->query($query);

                if (count($query)) {

                    for ($i = 0; $i < count($query); $i++) {
                        if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->newSenderKey){
                            array_push($companyNew, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->masterSenderKey){
                            array_push($companyMaster, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        } else {
                            array_push($company, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                        }
                    }

                    $company = array_unique($company);
                    $companyNew = array_unique($companyNew);
                    $companyMaster = array_unique($companyMaster);

                    CommonController::startPushContent($shortId, $company);
                    CommonController::startPushContentNew($shortId, $companyNew);
                    CommonController::startPushContentMaster($shortId, $listId, $companyMaster);

                } else {
                    $startarray = explode(" ", trim($startSi));

                    if ($kind == 1) {
                        $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3 or MV_LIST.MV_LIST_TYPE=1 or MV_LIST.MV_LIST_TYPE=4) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                        $query = DB::select(DB::raw($query));
                    } else {
                        $query = 'SELECT USER_DEVICE_REGISTERS_VALUE, USER_DEVICE_REGISTERS_SENDER FROM ESTIMATE_COMPANY
        LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
        LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
        WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y" AND (MV_LIST.MV_LIST_TYPE=0 or MV_LIST.MV_LIST_TYPE=3) AND (USER_DEVICE_REGISTERS_COMPANY=2 or USER_DEVICE_REGISTERS_COMPANY=4) AND USER_DEVICE_REGISTERS_ID != 55541
        GROUP BY MV_LIST.MV_LIST_ID;';
                        $query = DB::select(DB::raw($query));
                    }

//                $query = 'SELECT USER_DEVICE_REGISTERS_VALUE FROM ESTIMATE_COMPANY
//                          LEFT JOIN MV_LIST ON ESTIMATE_COMPANY.ESTIMATE_COMPANY_FK_LIST = MV_LIST.MV_LIST_ID
//                          LEFT JOIN MV_REGI ON MV_LIST.MV_LIST_ID = MV_REGI.MV_REGI_FK_LIST
//                          LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_COMPANY_FK_USER=USER_DEVICE_REGISTERS_ID
//                          WHERE MV_REGI_DO = "' . $startDo . '" AND MV_REGI_SI="' . $startarray[0] . '" AND MV_REGI_CHOOSE < 3 AND MV_LIST.USEYN = "Y"
//                          GROUP BY MV_LIST.MV_LIST_ID;';
//                $mySql->query($query);

                    if (count($query)) {

                        for ($i = 0; $i < count($query); $i++) {
                            if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->newSenderKey){
                                array_push($companyNew, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                            } else if($query[$i]->USER_DEVICE_REGISTERS_SENDER == $this->masterSenderKey){
                                array_push($companyMaster, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                            } else {
                                array_push($company, $query[$i]->USER_DEVICE_REGISTERS_VALUE);
                            }
                        }

                        $company = array_unique($company);
                        $companyNew = array_unique($companyNew);
                        $companyMaster = array_unique($companyMaster);

                        CommonController::startPushContent($shortId, $company);
                        CommonController::startPushContentNew($shortId, $companyNew);
                        CommonController::startPushContentMaster($shortId, $listId, $companyMaster);

                    }
                }
            } else {

                $company = array_unique($company);
                $companyNew = array_unique($companyNew);
                $companyMaster = array_unique($companyMaster);

                CommonController::startPushContent($shortId, $company);
                CommonController::startPushContentNew($shortId, $companyNew);
                CommonController::startPushContentMaster($shortId, $listId, $companyMaster);
            }

        CommonController::EstimateEnd($phone);
        }
        DB::commit();
        return response()->json(array('data' => true));
    }

    public function EstimateRead(Request $request)
    {
        $id = $request->id;
        $companyId = $request->companyId;
        $data = $request->data;
        $doName = $request->doName;
        $siName = $request->siName;
        $dongName = $request->dongName;
        $isMyLocal = 0;
        $lastId = -1;
        $category = -1;
        $itemSize = 0;
        $totalSize = 0;
        $noBidding = 0;
        $nextSize = $itemSize + $this->maxItems;

        $data['info'] = array();
        $data['estimate'] = array();

        if ($data == 'company') {
            $query = 'SELECT MV_REGI_DO, MV_REGI_SI, MV_REGI_CHOOSE, MV_LIST_TYPE FROM MV_LIST LEFT JOIN MV_REGI ON MV_REGI.MV_REGI_FK_LIST = MV_LIST.MV_LIST_ID WHERE MV_LIST_ID = ' . $companyId . ' AND MV_REGI_CHOOSE < 4;';
            $query = DB::select(DB::raw($query));
            $temp['register'] = array();

            for ($i = 0; $i < count($query); $i++) {
                $form = array();
                $form['doName'] = $query[$i]->MV_REGI_DO;
                $form['siName'] = $query[$i]->MV_REGI_SI;
                $form['priority'] = $query[$i]->MV_REGI_CHOOSE;
                $type = $query[$i]->MV_LIST_TYPE;
                array_push($temp['register'], $form);
            }
            $size = count($temp['register']);
            $changedDoName = true;

            $fixedDoName = $temp['register'][0]['doName'];

            for ($i = 0; $i < $size; $i++) {
                if ($i > 0) {
                    if ($fixedDoName === $temp['register'][$i]['doName']) {
                        $changedDoName = false;
                    } else {
                        $fixedDoName = $temp['register'][$i]['doName'];
                        $changedDoName = false;
                    }
                }
                if ($changedDoName) {
                    $query = 'SELECT ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, date_format(ESTIMATE_SHORT_REGDATE, "%y.%m.%d") AS REGDATE,
                    ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PHONE, ESTIMATE_REGI_DO, ESTIMATE_REGI_SI, ESTIMATE_REGI_DONG, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END FROM ESTIMATE_REGI
                    LEFT JOIN ESTIMATE_SHORT ON ESTIMATE_REGI.ESTIMATE_REGI_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_SHORT.ESTIMATE_SHORT_ID = ESTIMATE_ADDR.ESTIMATE_ADDR_FK_SHORT WHERE ';
                    if ($this->authority) {
                        $query .= 'ESTIMATE_REGI_DO = "' . $temp['register'][$i]['doName'] . '" AND';
                    }
                    if ($noBidding == 1) {
                        $query .= 'ESTIMATE_SHORT_REPLIES = 0 AND ESTIMATE_SHORT_PHONE != 3 AND';
                    }
                    $query .= ' ESTIMATE_SHORT_PUBLIC = 1';
                    if ($category != -1 && $category != -2) {
                        $query .= ' AND ESTIMATE_SHORT_KIND = ' . $category . '';
                    } elseif ($category == -2) {
                        $query .= ' AND ESTIMATE_SHORT_KIND != 1';
                    }
                    if ($isMyLocal == 1 && $lastId != -1) {
                        $query .= ' AND ESTIMATE_SHORT_ID < ' . $lastId . ' ';
                    }
                    if ($isMyLocal == 0) {
                        $query .= ' ORDER BY ESTIMATE_SHORT_ID DESC LIMIT ' . $itemSize . ', ' . $nextSize . ';';
                    } elseif ($isMyLocal == 1) {
                        $query .= ' ORDER BY ESTIMATE_SHORT_ID DESC;';
                    }
//                    var_dump($query);
                    $query = DB::select(DB::raw($query));
                    for ($i = 0; $i < count($query); $i++) {
                        $form = array();
                        $form['listId'] = $query[$i]->ESTIMATE_SHORT_FK_LIST;
                        $form['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                        $form['count'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                        $form['replies'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                        $form['max_replies'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                        $form['kind'] = $query[$i]->ESTIMATE_SHORT_KIND;
                        $form['date'] = $query[$i]->REGDATE;
                        $form['move_date'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                        $form['hasPhone'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                        $form['start_address'] = trim($query[$i]->ESTIMATE_ADDR_START);
                        $form['end_address'] = trim($query[$i]->ESTIMATE_ADDR_END);
                        $form['authority'] = false;
                        $startarray = explode(" ", $form['start_address']);
                        $endarray = explode(" ", $form['end_address']);
                        $startCount = count($startarray);
                        $endCount = count($endarray);
                        $form['authority'] = false;
                        for ($j = 0; $j < $size; $j++) {
//                            $form['authority'] = $this->checkAddress($temp, $row[9], $row[10], $j);
                            if (!$form['authority']) {
                                $startAddr = $this->checkNull($startarray, $startCount, 1);
                                if (mb_substr($startAddr, -1, 1, "UTF-8") == "시") {
                                    $firstAddr = $this->checkNull($startarray, $startCount, 2);
                                    $startAddr = $startAddr . "" . ((mb_substr($firstAddr, -1, 1, "UTF-8") == "구") ? " " . $firstAddr : "");
                                }
                                $form['authority'] = $this->checkAddress($temp, $this->checkNull($startarray, $startCount, 0), $startAddr, $j);
                            }
                            $secondAddr = $this->checkNull($endarray, $endCount, 1);
                            if (mb_substr($secondAddr, -1, 1, "UTF-8") == "시") {
                                $lastAddr = $this->checkNull($endarray, $endCount, 2);
                                $secondAddr = $secondAddr . "" . ((mb_substr($lastAddr, -1, 1, "UTF-8") == "구") ? " " . $lastAddr : "");
                            }
                            if (!$form['authority']) {
                                $form['authority'] = $this->checkAddress($temp, $this->checkNull($endarray, $endCount, 0), $secondAddr, $j);
                            }
                        }

//                        if($type == 2){
//                            $form['authority'] = false;
//                        } else {
//                            $form['authority'] = true;
//                        }


                        if ($isMyLocal == 1) {
                            if ($form['authority']) {
                                if ($type == 2) {
                                    $form['authority'] = false;
                                }
                                array_push($data['estimate'], $form);
                            }
                        } else {
                            array_push($data['estimate'], $form);
                        }
                    }
                }
            }
        } else if ($data == 'user') {
            $query = 'SELECT ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, date_format(ESTIMATE_SHORT_REGDATE, "%y.%m.%d") AS REGDATE,
            ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PHONE, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END FROM ESTIMATE_REGI
            LEFT JOIN ESTIMATE_SHORT ON ESTIMATE_REGI.ESTIMATE_REGI_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_SHORT.ESTIMATE_SHORT_ID = ESTIMATE_ADDR.ESTIMATE_ADDR_FK_SHORT WHERE';
            if ($this->authority) {
                $query .= 'ESTIMATE_REGI_DO = "' . $doName . '" AND ESTIMATE_REGI_SI = "' . $siName . '" AND ESTIMATE_REGI_DONG = "' . $dongName . '" AND';
            }
            $query .= ' ESTIMATE_SHORT_PUBLIC = 1';
            if ($category != -1 && $category != -2) {
                $query .= ' AND ESTIMATE_SHORT_KIND = ' . $category . '';
            } elseif ($category == -2) {
                $query .= ' AND ESTIMATE_SHORT_KIND != 1';
            }
            $query .= ' ORDER BY ESTIMATE_SHORT_ID DESC LIMIT ' . $itemSize . ', ' . $nextSize . ';';
            $query = DB::select(DB::raw($query));
            for ($i = 0; $i < count($query); $i++) {
                $form = array();
                $form['listId'] = $query[$i]->ESTIMATE_SHORT_FK_LIST;
                $form['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                $form['count'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                $form['replies'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                $form['max_replies'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                $form['kind'] = $query[$i]->ESTIMATE_SHORT_KIND;
                $form['date'] = $query[$i]->REGDATE;
                $form['move_date'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                $form['hasPhone'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                $form['start_address'] = $query[$i]->ESTIMATE_ADDR_START;
                $form['end_address'] = $query[$i]->ESTIMATE_ADDR_END;
                $form['authority'] = true;
                array_push($data['estimate'], $form);
            }
        }

        return response()->json(array('data' => $data));
    }

    function EstimateModify(Request $request)
    {
        $id = $request->id;

        $start = false;

        $query = 'SELECT ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_CONTENT, ESTIMATE_LIST_START_LADDER, ESTIMATE_LIST_END_LADDER, ESTIMATE_LIST_KIND, ESTIMATE_LIST_PEOPLE, ESTIMATE_LIST_ROOM_SIZE,
        ESTIMATE_LIST_AIR, ESTIMATE_LIST_BED, ESTIMATE_LIST_TV, ESTIMATE_LIST_PIANO, ESTIMATE_LIST_WARDROBE, ESTIMATE_LIST_BIDDING, ESTIMATE_LIST_DATE, ESTIMATE_LIST_PHONE, ESTIMATE_ADDR_START,
        ESTIMATE_ADDR_END, USER_DEVICE_REGISTERS_PHONE, ESTIMATE_IMG_ID, ESTIMATE_IMG_ITEM
        FROM ESTIMATE_LIST LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_LIST.ESTIMATE_LIST_ID = ESTIMATE_ADDR.ESTIMATE_ADDR_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_LIST.ESTIMATE_LIST_FK_USER = USER_DEVICE_REGISTERS.USER_DEVICE_REGISTERS_ID
        LEFT JOIN ESTIMATE_IMG ON ESTIMATE_IMG.ESTIMATE_IMG_FK_LIST = ESTIMATE_LIST.ESTIMATE_LIST_ID  WHERE ESTIMATE_LIST_ID = ' . $id . ';';
        $query = DB::select(DB::raw($query));

        $data['info'] = array();
        $data['imgs'] = array();
        $img = array();
        $count = 0;
        for ($i = 0; $i < count($query); $i++) {
            $form = array();
            if (!$start) {
                $form['folder'] = $query[$i]->ESTIMATE_LIST_FOLDER;
                $form['content'] = $query[$i]->ESTIMATE_LIST_CONTENT;
                $form['start_ladder'] = $query[$i]->ESTIMATE_LIST_START_LADDER;
                $form['end_ladder'] = $query[$i]->ESTIMATE_LIST_END_LADDER;
                $form['kind'] = $query[$i]->ESTIMATE_LIST_KIND;
                $form['people'] = $query[$i]->ESTIMATE_LIST_PEOPLE;
                $form['room_size'] = $query[$i]->ESTIMATE_LIST_ROOM_SIZE;
                $form['air'] = $query[$i]->ESTIMATE_LIST_AIR;
                $form['bed'] = $query[$i]->ESTIMATE_LIST_BED;
                $form['tv'] = $query[$i]->ESTIMATE_LIST_TV;
                $form['piano'] = $query[$i]->ESTIMATE_LIST_PIANO;
                $form['wardrobe'] = $query[$i]->ESTIMATE_LIST_WARDROBE;
                $form['bidding'] = $query[$i]->ESTIMATE_LIST_BIDDING;
                $form['date'] = $query[$i]->ESTIMATE_LIST_DATE;
                $form['hasPhone'] = $query[$i]->ESTIMATE_LIST_PHONE;
                $form['start_address'] = $query[$i]->ESTIMATE_ADDR_START;
                $form['end_address'] = $query[$i]->ESTIMATE_ADDR_END;
                $form['phone'] = $query[$i]->USER_DEVICE_REGISTERS_PHONE;
                if (isset($query[$i]->ESTIMATE_IMG_ID) && $query[$i]->ESTIMATE_IMG_ID != null) {
                    $img[$count]['id'] = $query[$i]->ESTIMATE_IMG_ID;
                    $img[$count]['img'] = $query[$i]->ESTIMATE_IMG_ITEM;
                }
                $start = true;
                array_push($data['info'], $form);
            } else {
                $img[$count]['id'] = $query[$i]->ESTIMATE_IMG_ID;
                $img[$count]['img'] = $query[$i]->ESTIMATE_IMG_ITEM;
            }
            $count++;
        }
        $query = 'SELECT ESTIMATE_REGI_DO, ESTIMATE_REGI_SI, ESTIMATE_REGI_DONG FROM ESTIMATE_SHORT LEFT JOIN ESTIMATE_REGI ON ESTIMATE_SHORT.ESTIMATE_SHORT_ID = ESTIMATE_REGI.ESTIMATE_REGI_FK_SHORT WHERE ESTIMATE_SHORT_FK_LIST = ' . $id . ';';
        $query = DB::select(DB::raw($query));
        if (!empty($query)) {
            $form = array();
            $form['doName'] = $query[0]->ESTIMATE_REGI_DO;
            $form['siName'] = $query[0]->ESTIMATE_REGI_SI;
            $form['dongName'] = $query[0]->ESTIMATE_REGI_DONG;
            $data['register'] = $form;
        }
        array_push($data['imgs'], $img);

        return response()->json(array('data' => $data));
    }

    function EstimateUpdate(Request $request)
    {
        $id = $request->id;
        $phone = $request->phone;
        $title = $request->title;
        $content = $request->input('content');
        $start_add = $request->start_add;
        $end_add = $request->end_add;
        $start_ladder = $request->start_ladder;
        $end_ladder = $request->end_ladder;
        $kind = $request->kind;
        $people = $request->people;
        $room_size = $request->room_size;
        $air = $request->air;
        $bed = $request->stone_bed;
        $tv = $request->wall_tv;
        $piano = $request->piano;
        $wardrobe = $request->piece_wardrobe;
        $bidding = $request->bidding;
        $moving_date = $request->moving_date;
        $biddingType = $request->biddingType;
        $doName = $request->doName;
        $siName = $request->siName;
        $dongName = $request->dongName;

        $shortId = $request->shortId;
        $folder = $request->folder;
        $delete_count = $request->delete_count;
        $delete_count = (int)$delete_count;
        $list['deleted'] = array();
        $deletedImg = array();
        for ($i = 0; $i < $delete_count; $i++) {
            $deletedImg[$i]['id'] = $request->img_id_ . $i;
            $deletedImg[$i]['name'] = $request->img_name_ . $i;
        }


        $img_count = $request->img_count;
        $img_count = (int)$img_count;
        $images = array();
        for ($i = 0; $i < $img_count; $i++) {
            $images[$i]['img'] = (isset($_FILES["image_" . $i])) ? $_FILES["image_" . $i] : null;
        }

        $temp = explode("/", $folder);
        $phone = $temp[0];
        $folders = $temp[1];
        $dateList = explode(".", $moving_date);
        $year = substr($dateList[0], -2, 2);
        $month = $dateList[1];
        $date = $dateList[2];
        $movingDate = $year . "." . $month . "." . $date;
        $replies = null;

        DB::beginTransaction();

        $query = 'UPDATE ESTIMATE_REGI SET ESTIMATE_REGI_DO = "' . $doName . '", ESTIMATE_REGI_SI = "' . $siName . '", ESTIMATE_REGI_DONG = "' . $dongName . '" WHERE ESTIMATE_REGI_FK_SHORT = ' . $shortId . ';';
        $query = DB::statement(DB::raw($query));

        if ($bidding == 0) {
            $bidding = 2;
        } else if ($bidding == 1) {
            $bidding = 3;
        } else if ($bidding == 2) {
            $bidding = 5;
        }

        $query = 'SELECT ESTIMATE_SHORT_REPLIES FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
        $query = DB::select(DB::raw($query));

        if (count($query)) {
            $replies = $query[0]->ESTIMATE_SHORT_REPLIES;
        }

        $query = 'UPDATE ESTIMATE_SHORT SET ESTIMATE_SHORT_PHONE = ' . $biddingType . ', ESTIMATE_SHORT_MOVE_DATE = "' . $movingDate . '", ESTIMATE_SHORT_KIND = ' . $kind . '';


        if (!$replies == null) {
            if ($replies <= $bidding) {
                $query .= ', ESTIMATE_SHORT_MAX_REPLIES = ' . $bidding . '';
            }
        } else {
            $query .= ', ESTIMATE_SHORT_MAX_REPLIES = ' . $bidding . '';
        }

        $query .= ' WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
        $query = DB::statement(DB::raw($query));

        $query = 'UPDATE ESTIMATE_LIST SET ESTIMATE_LIST_CONTENT = "' . $content . '", ESTIMATE_LIST_START_LADDER = ' . $start_ladder . ', ESTIMATE_LIST_END_LADDER = ' . $end_ladder . ',
        ESTIMATE_LIST_KIND = ' . $kind . ', ESTIMATE_LIST_PEOPLE = ' . $people . ', ESTIMATE_LIST_ROOM_SIZE = ' . $room_size . ', ESTIMATE_LIST_AIR = ' . $air . ',
        ESTIMATE_LIST_BED = ' . $bed . ', ESTIMATE_LIST_TV = ' . $tv . ', ESTIMATE_LIST_PIANO = ' . $piano . ', ESTIMATE_LIST_WARDROBE = ' . $wardrobe . ', ESTIMATE_LIST_PHONE = ' . $biddingType . ', ';

        if (!$replies == null) {
            if ($replies <= $bidding) {
                $query .= 'ESTIMATE_LIST_BIDDING = ' . $bidding . ',';
            }
        } else {
            $query .= 'ESTIMATE_LIST_BIDDING = ' . $bidding . ',';
        }

//        if($replies <= $bidding){
//            $query .= 'ESTIMATE_LIST_BIDDING = '.$list['bidding'].',';
//        }
        $query .= 'ESTIMATE_LIST_DATE = "' . $movingDate . '" WHERE ESTIMATE_LIST_ID = ' . $id . ';';
        $query = DB::statement(DB::raw($query));

        $query = 'UPDATE ESTIMATE_ADDR SET ESTIMATE_ADDR_START = "' . $start_add . '", ESTIMATE_ADDR_END = "' . $end_add . '" WHERE ESTIMATE_ADDR_FK_LIST = ' . $id . ';';
        $query = DB::statement(DB::raw($query));



        foreach ($list['deleted'] as $index => $value) {
            unlink("../storage/estimateBoard/" . $phone . "/" . $folder . "/img/" . $value['name']);
            unlink("../storage/estimateBoard/" . $phone . "/" . $folder . "/thumb/" . $value['name']);
            $query = 'DELETE FROM ESTIMATE_IMG WHERE ESTIMATE_IMG_ID = ' . $value['id'] . ';';
            $query = DB::statement(DB::raw($query));
        }


        if(!empty($list['images'])){
            foreach ($list['images'] as $index => $value) {
                if ($value != null && isset($value)) {
                    $name = $this->makeImage($value, $folder, $phone);
                    $query = 'INSERT INTO ESTIMATE_IMG VALUES (NULL,"' . $name . '", ' . $list['id'] . ');';
                    $query = DB::statement(DB::raw($query));
                }
            }
        }


        DB::commit();

        return response()->json(array('data' => true));
    }

    function EstimateDetailForLatest(Request $request)
    {
        $companyId = $request->companyId;
        $userState = $request->userState;
        $id = $request->id;
        $click = $request->click;

        $start = false;

        $query = 'SELECT ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_CONTENT, ESTIMATE_LIST_START_LADDER, ESTIMATE_LIST_END_LADDER, ESTIMATE_LIST_KIND, ESTIMATE_LIST_PEOPLE, ESTIMATE_LIST_ROOM_SIZE,
        ESTIMATE_LIST_AIR, ESTIMATE_LIST_BED, ESTIMATE_LIST_TV, ESTIMATE_LIST_PIANO, ESTIMATE_LIST_WARDROBE, ESTIMATE_LIST_BIDDING, ESTIMATE_LIST_DATE, ESTIMATE_LIST_PHONE, ESTIMATE_ADDR_START,
        ESTIMATE_ADDR_END, USER_DEVICE_REGISTERS_PHONE, ESTIMATE_IMG_ITEM
        FROM ESTIMATE_LIST
        LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_LIST.ESTIMATE_LIST_ID = ESTIMATE_ADDR.ESTIMATE_ADDR_FK_LIST
        LEFT JOIN USER_DEVICE_REGISTERS ON ESTIMATE_LIST.ESTIMATE_LIST_FK_USER = USER_DEVICE_REGISTERS.USER_DEVICE_REGISTERS_ID
        LEFT JOIN ESTIMATE_IMG ON ESTIMATE_IMG.ESTIMATE_IMG_FK_LIST = ESTIMATE_LIST.ESTIMATE_LIST_ID  WHERE ESTIMATE_LIST_ID = ' . $id . ';';

        $query = DB::select(DB::raw($query));

        $list['success'] = $start;
        $list['data'] = array();

        $data['info'] = array();
        $data['imgs'] = array();
        $data['replies'] = array();
        $img = array();
        $count = 0;
        for ($i = 0; $i < count($query); $i++) {
            $form = array();
            if (!$start) {
                $form['folder'] = $query[$i]->ESTIMATE_LIST_FOLDER;
                $form['content'] = $query[$i]->ESTIMATE_LIST_CONTENT;
                $form['start_ladder'] = $query[$i]->ESTIMATE_LIST_START_LADDER;
                $form['end_ladder'] = $query[$i]->ESTIMATE_LIST_END_LADDER;
                $form['kind'] = $query[$i]->ESTIMATE_LIST_KIND;
                $form['people'] = $query[$i]->ESTIMATE_LIST_PEOPLE;
                $form['room_size'] = $query[$i]->ESTIMATE_LIST_ROOM_SIZE;
                $form['air'] = $query[$i]->ESTIMATE_LIST_AIR;
                $form['bed'] = $query[$i]->ESTIMATE_LIST_BED;
                $form['tv'] = $query[$i]->ESTIMATE_LIST_TV;
                $form['piano'] = $query[$i]->ESTIMATE_LIST_PIANO;
                $form['wardrobe'] = $query[$i]->ESTIMATE_LIST_WARDROBE;
                $form['bidding'] = $query[$i]->ESTIMATE_LIST_BIDDING;
                $form['date'] = $this->changeDate($query[$i]->ESTIMATE_LIST_DATE);
                $form['hasPhone'] = $query[$i]->ESTIMATE_LIST_PHONE;
                $form['start_address'] = $query[$i]->ESTIMATE_ADDR_START;
                $form['end_address'] = $query[$i]->ESTIMATE_ADDR_END;
                $form['phone'] = $query[$i]->USER_DEVICE_REGISTERS_PHONE;
                if (substr($form['phone'], 0, 1) == '+') {
                    $form['phone'] = '0' . substr($form['phone'], 3);
                }
                if (isset($query[$i]->ESTIMATE_IMG_ITEM) && $query[$i]->ESTIMATE_IMG_ITEM != null) {
                    $img[$count]['img'] = $query[$i]->ESTIMATE_IMG_ITEM;
                }
                $start = true;
                array_push($data['info'], $form);
            } else {
                $img[$count]['img'] = $query[$i]->ESTIMATE_IMG_ITEM;
            }
            $count++;
        }
        array_push($data['imgs'], $img);

        $query = 'SELECT ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_ID, ESTIMATE_REPLY_ID, MV_LIST_ID, ESTIMATE_REPLY_CONTENT, ESTIMATE_REPLY_PRICE, MV_LIST_NAME,
        MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_CHOOSE_PHONE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_ICON, ESTIMATE_REPLY_PACKING_PRICE,
        ESTIMATE_REPLY_HALF_PACKING_PRICE, ESTIMATE_REPLY_CAR, ESTIMATE_REPLY_MAN, ESTIMATE_REPLY_GIRL, ESTIMATE_REPLY_LADDER_START, ESTIMATE_REPLY_LADDER_END
        FROM ESTIMATE_SHORT
        LEFT JOIN ESTIMATE_REPLY ON ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID
        LEFT JOIN MV_LIST ON ESTIMATE_REPLY.ESTIMATE_REPLY_FK_MOVE = MV_LIST.MV_LIST_ID WHERE ESTIMATE_SHORT_FK_LIST = ' . $id . '';
        if ($userState != -1) {
            $query .= ' AND MV_LIST_ID = ' . $companyId . '';
        }
        $query .= ' ORDER BY ESTIMATE_REPLY_PRICE ASC;';
        $query = DB::select(DB::raw($query));
        $clickedCount = 0;
        $replies = array();
        for ($i = 0; $i < count($query); $i++) {
            $clickedCount = $query[$i]->ESTIMATE_SHORT_COUNT;
            if ($query[$i]->ESTIMATE_REPLY_ID != null) {
                $form = array();
                $form['id'] = $query[$i]->ESTIMATE_SHORT_ID;
                $form['replyId'] = $query[$i]->ESTIMATE_REPLY_ID;
                $form['companyId'] = $query[$i]->MV_LIST_ID;
                $form['content'] = $query[$i]->ESTIMATE_REPLY_CONTENT;
                $form['price'] = $query[$i]->ESTIMATE_REPLY_PRICE;
                $form['company'] = $query[$i]->MV_LIST_NAME;
                $form['description'] = $query[$i]->MV_LIST_DESCRIPTION;
                $form['grade'] = $query[$i]->MV_LIST_GRADE;
                $form['license'] = $query[$i]->MV_LIST_LICENSE;
                if ($query[$i]->MV_LIST_CHOOSE_PHONE == 0) {
                    $form['phone'] = $query[$i]->MV_LIST_COMPANY_PHONE;
                } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 1) {
                    $form['phone'] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                }
                if (!empty($query[$i]->MV_LIST_ICON)) {
                    $form['icon'] = "http://www.gae8.com/24moa/upload/" . $query[$i]->MV_LIST_ICON;
                } else {
                    $form['icon'] = "";
                }
                $form['packingPrice'] = $query[$i]->ESTIMATE_REPLY_PACKING_PRICE;
                $form['halfPackingPrice'] = $query[$i]->ESTIMATE_REPLY_HALF_PACKING_PRICE;
                $form['car'] = $query[$i]->ESTIMATE_REPLY_CAR;
                $form['man'] = $query[$i]->ESTIMATE_REPLY_MAN;
                $form['girl'] = $query[$i]->ESTIMATE_REPLY_GIRL;
                $form['ladderStart'] = $query[$i]->ESTIMATE_REPLY_LADDER_START;
                $form['ladderEnd'] = $query[$i]->ESTIMATE_REPLY_LADDER_END;;
                array_push($replies, $form);
            }
        }

        $data['replies'] = $replies;
        $data['click'] = $clickedCount;
        $query = 'UPDATE ESTIMATE_SHORT SET ESTIMATE_SHORT_COUNT = ESTIMATE_SHORT_COUNT + 1 WHERE ESTIMATE_SHORT_FK_LIST = ' . $id . ';';
        $query = DB::statement(DB::raw($query));

        $list['success'] = $start;
        $list['data'] = $data;

        return response()->json(array('data' => $list));
    }

    function EstimateComplete(Request $request)
    {
        $shortId = $request->shortId;
        $listId = $request->listId;
        $hasPhone = $request->hasPhone;

        $complete = $hasPhone;
        if ($hasPhone < 2) {
            $complete = $hasPhone + 2;
        } else {
            $complete = $hasPhone - 2;
        }
        $query = 'UPDATE ESTIMATE_SHORT SET ESTIMATE_SHORT_PHONE = ' . $complete . ' WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
        $query = DB::statement(DB::raw($query));
        if (!$query) {
            return response()->json(array('data' => false));
        }
        $query = 'UPDATE ESTIMATE_LIST SET ESTIMATE_LIST_PHONE = ' . $complete . ' WHERE ESTIMATE_LIST_ID = ' . $listId . '';
        $query = DB::statement(DB::raw($query));
        if (!$query) {
            return response()->json(array('data' => false));
        }
        return response()->json(array('data' => true));
    }

    function changeDate($date)
    {
        $customDate = explode(".", $date);

        if (mb_ereg("20[0-9][0-9]", $customDate[0])) {
            return explode("20", $customDate[0])[1] . "." . $customDate[1] . "." . $customDate[2];
        } else {
            return $date;
        }
    }

    function checkAddress($temp, $first, $second, $position)
    {
        $values = array();
        $values = explode(" ", $temp['register'][$position]['doName']);
        $doOrSi = mb_substr($values[0], -1, 1, "UTF-8");

        if ($temp['register'][$position]['doName'] == $first && $doOrSi == "시") {
            if ($temp['register'][$position]['priority'] == 0) {
                return true;
            } elseif ($temp['register'][$position]['siName'] == $second) {
                return true;
            }
        }

        if ($temp['register'][$position]['siName'] == $second && $doOrSi == "도") {
            if ($temp['register'][$position]['priority'] == 0) {
                return true;
            } elseif ($temp['register'][$position]['priority'] == 1 || $temp['register'][$position]['priority'] == 2 || $temp['register'][$position]['priority'] == 3) {
                return true;
            }
        } else if ($temp['register'][$position]['siName'] == explode(" ", $second)[0] && $doOrSi == "도") {
            if ($temp['register'][$position]['priority'] == 0) {
                return true;
            }
        }
        return false;
    }

    function checkNull($array, $total, $position)
    {
        if ($total > $position) {
            return $array[$position];
        }
        return "";
    }

    function makeUserFolder($id)
    {
        if (!is_dir("../storage/estimateBoard/" . $id)) {
            mkdir("../storage/estimateBoard/" . $id);
        }
    }

    public function EstimateList1()
    {

        $companyId = 13580;
        $nobiz = 'false';

        $query = 'select MV_LIST_TYPE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
        }


        $query = "SELECT
ESTIMATE_SHORT_ID, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND,
ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END, USER_DEVICE_REGISTERS_PHONE
,IF(MR.MV_REGI_FK_LIST IS NULL, '0', '1') AS REGI_STATE
FROM
(
   SELECT
   ES.ESTIMATE_SHORT_ID, ES.ESTIMATE_SHORT_FK_LIST, ES.ESTIMATE_SHORT_COUNT, ES.ESTIMATE_SHORT_REPLIES, ES.ESTIMATE_SHORT_MAX_REPLIES, ES.ESTIMATE_SHORT_KIND,
   date_format(ESTIMATE_SHORT_REGDATE, '%y.%m.%d') AS ESTIMATE_SHORT_DATE, ES.ESTIMATE_SHORT_MOVE_DATE, ES.ESTIMATE_SHORT_PUBLIC,
   EA.ESTIMATE_ADDR_START, EA.ESTIMATE_ADDR_END, REPLACE(UDR.USER_DEVICE_REGISTERS_PHONE,'+82','0') AS USER_DEVICE_REGISTERS_PHONE,
   SUBSTRING_INDEX( TRIM( EA.ESTIMATE_ADDR_START ),' ',1 ) AS START_SI,
   SUBSTRING_INDEX( SUBSTRING_INDEX( TRIM( EA.ESTIMATE_ADDR_START ),' ',2 ),' ',-1 ) AS START_GU,
   SUBSTRING_INDEX( TRIM( EA.ESTIMATE_ADDR_END ),' ',1 ) AS END_SI,
   SUBSTRING_INDEX( SUBSTRING_INDEX( TRIM( EA.ESTIMATE_ADDR_END ),' ',2 ),' ',-1 ) AS END_GU,
   ES.ESTIMATE_SHORT_REGDATE
   FROM ESTIMATE_SHORT AS ES
   LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
   LEFT OUTER JOIN USER_DEVICE_REGISTERS AS UDR ON ES.ESTIMATE_SHORT_FK_USER = UDR.USER_DEVICE_REGISTERS_ID
   WHERE 1=1 AND ES.ESTIMATE_SHORT_PUBLIC = 1 ";

        if ($nobiz == 'true') {
            $query .= "AND ES.ESTIMATE_SHORT_REPLIES = 0 ";
        }

        $query .= ") AS T
LEFT OUTER JOIN MV_REGI AS MR
ON MR.MV_REGI_FK_LIST = '13580' AND (T.START_SI = MR.MV_REGI_DO OR T.END_SI = MR.MV_REGI_DO) AND (T.START_GU LIKE CONCAT('%', MR.MV_REGI_SI ,'%') OR T.END_GU LIKE CONCAT('%', MR.MV_REGI_SI ,'%'))
WHERE MR.MV_REGI_FK_LIST IS NOT NULL GROUP BY ESTIMATE_SHORT_ID;";


        $query = DB::select(DB::raw($query));

        $form = array();
        $result = array();

        for ($i = 0; $i < count($query); $i++) {

            if ($type == 2) {

                $query[$i]->REGI_STATE = 0;
            } else if ($type == 1 || $type == 4) {
                if ($query[$i]->ESTIMATE_SHORT_KIND == 1) {

                } else {
                    $query[$i]->REGI_STATE = 0;
                }
            }

            if ($query[$i]->REGI_STATE == 0) {

            } else {
                array_push($result, $query[$i]);
            }


        }

        return response()->json(array('data' => $result));
    }

    public function EstimateList2(Request $request)
    {

        $companyId = 13580;
        $nobiz = false;

        $query = 'select MV_LIST_TYPE from MV_LIST WHERE MV_LIST_ID=' . $companyId . ';';
        $query = DB::select(DB::raw($query));

        if (!empty($query)) {
            $type = $query[0]->MV_LIST_TYPE;
        }

        $query = "SELECT
                    ESTIMATE_SHORT_ID, ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND,
                    ESTIMATE_SHORT_DATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PUBLIC, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END, REPLACE(UDR.USER_DEVICE_REGISTERS_PHONE,'+82','0') AS USER_DEVICE_REGISTERS_PHONE,
                    (select count(1) from MV_REGI where MV_REGI_DO = SUBSTRING_INDEX( ESTIMATE_ADDR_START ,' ',1 ) AND if(LENGTH(MV_REGI_SI) - LENGTH(REPLACE(MV_REGI_SI, ' ', '')) > 0, MV_REGI_SI = substring_index(substring_index(ESTIMATE_ADDR_START, ' ', 3), ' ', -2), MV_REGI_SI = substring_index(ESTIMATE_ADDR_START, ' ', -1)) AND MV_REGI_FK_LIST='" . $companyId . "') AS START_REGI,
                    (select count(1) from MV_REGI where MV_REGI_DO = SUBSTRING_INDEX( ESTIMATE_ADDR_END,' ',1 ) AND if(LENGTH(MV_REGI_SI) - LENGTH(REPLACE(MV_REGI_SI, ' ', '')) > 0, MV_REGI_SI = substring_index(substring_index(ESTIMATE_ADDR_END, ' ', 3), ' ', -2), MV_REGI_SI = substring_index(ESTIMATE_ADDR_END, ' ', -1)) AND MV_REGI_FK_LIST='" . $companyId . "') AS END_REGI
                    FROM ESTIMATE_SHORT AS ES
                        LEFT OUTER JOIN ESTIMATE_ADDR AS EA ON ES.ESTIMATE_SHORT_ID = EA.ESTIMATE_ADDR_FK_SHORT
                        LEFT OUTER JOIN USER_DEVICE_REGISTERS AS UDR ON ES.ESTIMATE_SHORT_FK_USER = UDR.USER_DEVICE_REGISTERS_ID
					WHERE date(ESTIMATE_SHORT_REGDATE) >= date(subdate(now(), INTERVAL 10 DAY)) ";

        if ($nobiz == 'true') {
            $query .= "AND ES.ESTIMATE_SHORT_REPLIES = 0 ";
        }

        $query = DB::select(DB::raw($query));

        $form = array();
        $result = array();

        for ($i = 0; $i < count($query); $i++) {

            if ($type == 2) {

                $query[$i]->START_REGI = 0;
                $query[$i]->END_REGI = 0;
            } else if ($type == 1 || $type == 4) {
                if ($query[$i]->ESTIMATE_SHORT_KIND == 1) {

                } else {
                    $query[$i]->START_REGI = 0;
                    $query[$i]->END_REGI = 0;
                }
            }

            if ($query[$i]->START_REGI == 0 && $query[$i]->END_REGI == 0) {

            } else {
                array_push($result, $query[$i]);
            }


        }

        return response()->json(array('data' => $result));
    }

    function EstimateClick(Request $request)
    {
        $companyId = $request->companyId;
        $shortId = $request->shortId;
        $MV_DV = $request->MV_DV;

        $query = 'SELECT ESTIMATE_CLICK_OPEN FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY=' . $companyId . ' AND ESTIMATE_CLICK_FK_SHORT=' . $shortId . ' AND ESTIMATE_CLICK_MV_DV="' . $MV_DV . '";';
        $query = DB::select(DB::raw($query));

        if (empty($query)) {
            $query = 'INSERT INTO ESTIMATE_CLICK(ESTIMATE_CLICK_ID, ESTIMATE_CLICK_FK_COMPANY, ESTIMATE_CLICK_FK_SHORT, ESTIMATE_CLICK_OPEN, ESTIMATE_CLICK_OPEN_DATE, ESTIMATE_CLICK_MV_DV)
                      VALUES(null, ' . $companyId . ', ' . $shortId . ', 1, now(), "' . $MV_DV . '");';
            $query = DB::statement(DB::raw($query));

            if ($query) {
                return response()->json(array('data' => true));
            } else {
                return response()->json(array('data' => false));
            }

        } else {

            return response()->json(array('data' => false));
        }
    }

    function UserMyEstimate(Request $request){
        $id = $request->id;

        $data['info'] = array();
        $form = array();
        if ($id != null) {
            $query = 'SELECT ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES, ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, date_format(ESTIMATE_SHORT_REGDATE, "%y.%m.%d") AS ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_MOVE_DATE,
             ESTIMATE_SHORT_PUBLIC, ESTIMATE_SHORT_PHONE, ESTIMATE_SHORT_FK_LIST, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END FROM ESTIMATE_SHORT
        LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_SHORT.ESTIMATE_SHORT_ID = ESTIMATE_ADDR.ESTIMATE_ADDR_FK_SHORT
        WHERE ESTIMATE_SHORT_FK_USER = ' . $id . ';';

            $query = DB::select( DB::raw( $query ) );

            if(count($query)){
                for($i=0; $i<count($query); $i++){

                    $form['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                    $form['id'] = $query[$i]->ESTIMATE_SHORT_FK_LIST;
                    $form['count'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                    $form['replies'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                    $form['max_replies'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                    $form['kind'] = $query[$i]->ESTIMATE_SHORT_KIND;
                    $form['date'] = $query[$i]->ESTIMATE_SHORT_REGDATE;
                    $form['move_date'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                    $form['public'] = $query[$i]->ESTIMATE_SHORT_PUBLIC;
                    $form['hasPhone'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                    $form['start_address'] = $query[$i]->ESTIMATE_ADDR_START;
                    $form['end_address'] = $query[$i]->ESTIMATE_ADDR_END;
                    array_push($data['info'], $form);
                }

                return response()->json(array('data' => $data));
            }

        } else {
            return response()->json(array('data' => false));
        }
    }

    function MVCompanyCallToUser(Request $request)
    {

        $table = "MV";
        $admin = false;
        $phone = $request->phone;

        $mainTable = $table . '_LIST';
        $subTable = $table . '_REGI';
        $otherTable = $table . '_LIST_OTHER_INFO';

        if ($table == "MV") {
            $query = 'SELECT MV_LIST.MV_LIST_ID, MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_DESCRIPTION, MV_LIST_GRADE,
        MV_LIST_LICENSE, MV_LIST_ICON, MV_LIST_HOMEPAGE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_HOME_PHONE, MV_LIST_ETC_PHONE,
        MV_LIST_CHOOSE_PHONE, MV_LIST_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
                'FROM MV_LIST LEFT JOIN MV_LIST_OTHER_INFO ON MV_LIST.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
             LEFT OUTER JOIN MV_HISTORY ON MV_HISTORY.PHONE=REPLACE("' . $phone . '", "+82", "0")
             WHERE MV_HISTORY.FK_MV_LIST = MV_LIST.MV_LIST_ID ORDER BY MV_HISTORY.TIME DESC;';

        } else if ($table == "CL") {

            $query = 'SELECT CL_LIST.CL_LIST_ID, CL_LIST_NAME, CL_LIST_ADDRESS, CL_LIST_LATITUDE, CL_LIST_LONGITUDE, CL_LIST_DESCRIPTION, CL_LIST_GRADE,
        CL_LIST_LICENSE, CL_LIST_ICON, CL_LIST_HOMEPAGE, CL_LIST_COMPANY_PHONE, CL_LIST_PRIVATE_PHONE, CL_LIST_HOME_PHONE, CL_LIST_ETC_PHONE,
        CL_LIST_CHOOSE_PHONE, CL_LIST_TYPE
        FROM CL_LIST
         LEFT OUTER JOIN CL_HISTORY ON CL_HISTORY.PHONE=REPLACE("' . $phone . '", "+82", "0")
         WHERE CL_HISTORY.FK_CL_LIST = CL_LIST.CL_LIST_ID;';

        }

        $query = DB::select( DB::raw( $query ) );

        $data = array();
        $form = array();

        if(count($query)){
            for($i=0; $i<count($query); $i++){
                $form["id"] = $query[$i]->MV_LIST_ID;
                $form["name"] = $query[$i]->MV_LIST_NAME;
                $form["address"] = $query[$i]->MV_LIST_ADDRESS;
                $form["latitude"] = $query[$i]->MV_LIST_LATITUDE;
                $form["longitude"] = $query[$i]->MV_LIST_LONGITUDE;
                $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
                $form["grade"] = $query[$i]->MV_LIST_GRADE;
                $form["license"] = $query[$i]->MV_LIST_LICENSE;

                if(empty($query[$i]->MV_LIST_ICON)){
                    $form["icon"] = "";
                } else {
                    $form["icon"] = "www.gae8.com/24moa/upload/" . $query[$i]->MV_LIST_ICON;
                }

                $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;

                if ($query[$i]->MV_LIST_CHOOSE_PHONE == 0) {
                    $form["phone"] = $query[$i]->MV_LIST_COMPANY_PHONE;
                } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 1) {
                    $form["phone"] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 2) {
                    $form["phone"] = $query[$i]->MV_LIST_HOME_PHONE;
                }
                $form["type"] = $query[$i]->MV_LIST_TYPE;

                if ($table == "MV") {

                    if ($query[$i]->SS_CARD_PAY == null) {
                        $form["samsung"] = "2";
                    } else {
                        $form["samsung"] = $query[$i]->SS_CARD_PAY;
                    }

                    if ($query[$i]->NM_CARD_PAY == null) {
                        $form["card"] = "2";
                    } else {
                        $form["card"] = $query[$i]->NM_CARD_PAY;
                    }

                    if ($query[$i]->EVENT_ADD == null) {
                        $form["event"] = "2";
                    } else {
                        $form["event"] = $query[$i]->EVENT_ADD;
                    }

                }

                array_push($data, $form);
            }
        }

        return response()->json(array('data' => $data));
    }

    function makeImage($list, $time, $id)
    {
        $name = $list['img']['name'];
        if (!is_dir("../storage/estimateBoard/" . $id . "/" . $time)) {
            if (mkdir("../storage/estimateBoard/" . $id . "/" . $time)) {
                if (!is_dir("../storage/estimateBoard/" . $id . "/" . $time . "/img")) {
                    if (mkdir("../storage/estimateBoard/" . $id . "/" . $time . "/img")) {
                        move_uploaded_file($list['img']['tmp_name'], "../storage/estimateBoard/" . $id . "/" . $time . "/img/{$list['img']['name']}");
                    }
                }
            }
        } else {
            move_uploaded_file($list['img']['tmp_name'], "../storage/estimateBoard/" . $id . "/" . $time . "/img/{$list['img']['name']}");
        }
        if (file_exists($list["img"]["tmp_name"]) && is_file($list["img"]["tmp_name"])) {
            unlink($list["img"]["tmp_name"]);
        }
        $this->copyImage($id, $time, "../storage/estimateBoard/" . $id . "/" . $time . "/img/" . $name, $name);
        $this->resizeImage("../storage/estimateBoard/" . $id . "/" . $time . "/thumb/" . $name);
        return $name;
    }

    function copyImage($id, $time, $from, $name)
    {
        if (!is_dir("../storage/estimateBoard/" . $id . "/" . $time . "/thumb")) {
            if (mkdir("../storage/estimateBoard/" . $id . "/" . $time . "/thumb")) {
                copy($from, "../storage/estimateBoard/" . $id . "/" . $time . "/thumb/" . $name);
            }
        } else {
            copy($from, "../storage/estimateBoard/" . $id . "/" . $time . "/thumb/" . $name);
        }
    }

    function resizeImage($path)
    {

        ImageConvert::Image($path);
//        $image = new Image($path);
        ImageConvert::resize(20);
        ImageConvert::save();
    }
}
