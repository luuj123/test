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
use App\MV_HISTORY;
use App\CL_HISTORY;
use App\POINT_TOTAL;
use App\MV_LIST;
use App\MV_REGI;
use App\MV_LIST_OTHER_INFO;


class ACompany extends Controller
{

    public function CompanyPoint(Request $request)
    {


        $data['info'] = array();
        $data['list'] = array();

        $companyId = $request->UserID;

        $query = "select
					(SELECT PRODUCT_NAME FROM POINT_PRODUCT WHERE PRODUCT_ID = MV_LIST_POINT_PRODUCT) AS NAME,
					(select (MV_POINT_FREE + MV_POINT_CASH ) from MV_POINT where MV_POINT_FK_MOVE = '" . $companyId . "') AS POINT
				from MV_LIST WHERE MV_LIST_ID = '" . $companyId . "'";

        $query = DB::select(DB::raw($query));

        for ($i = 0; $i < count($query); $i++) {
            $form = array();
            $form['PointProduct'] = $query[$i]->NAME;
            $form['NowPoint'] = $query[$i]->POINT;
            array_push($data['info'], $form);
        }

        $query = "SELECT
                  ID, GUBUN, CONTENT, TIME, POINT, CASH_AMOUNT, CATEGORY_NAME, Category
                FROM
                (
                    SELECT
                    MH.ID, 'M' AS GUBUN, '소진' AS CONTENT, date_add(MH.TIME, interval +7 hour) AS TIME, MH.POINT,
                    0 AS CASH_AMOUNT,
                    NULL AS CATEGORY_NAME, NULL AS Category
                    FROM MV_HISTORY AS MH
                    LEFT OUTER JOIN POINT_RETURN AS PR ON MH.ESTIMATE_SHORT_ID = PR.ESTIMATE_SHORT_ID AND MH.FK_MV_LIST = PR.USER_ID
                    LEFT OUTER JOIN POINT_TOTAL AS PT ON PT.UserID = '" . $companyId . "' AND MH.ESTIMATE_SHORT_ID = SUBSTRING_INDEX(PT.Content,'/',-1)
                    WHERE MH.FK_MV_LIST='" . $companyId . "'
                    UNION ALL
                    SELECT
                    MV_PAYMENT_ID,'P' AS GUBUN, MP.MV_PAYMENT_DESCRIPTION AS CONTENT, date_add(MP.MV_PAYMENT_DATE, interval +7 hour) AS TIME, MP.MV_PAYMENT_FREE_CHARGE+MP.MV_PAYMENT_CASH_CHARGE,
                    MP.MV_PAYMENT_PRICE, NULL AS CATEGORY_NAME, NULL AS Category
                    FROM MV_PAYMENT AS MP WHERE MV_PAYMENT_FK_MOVE = '" . $companyId . "'
                ) AS T
                WHERE DATE_FORMAT(TIME,'%Y-%m-%d') < '2015-05-01'

                UNION ALL

                select
                PT.ID AS ID, IF( LEFT(PT.Category,4) = 'PO_I', 'P', 'M') AS GUBUN, IF(PT.Category = 'PO_OUT05','소진',SUBSTRING_INDEX(PT.Content,'/',1)) AS CONTENT,
                RegDate AS TIME, IF( LEFT(Category,4) = 'PO_I', inPoint, outPoint) AS POINT, Money AS CASH_AMOUNT,
                ( SELECT CODE_NAME FROM CODE WHERE CODE_VALUE = PT.Category) AS CATEGORY_NAME,
                PT.Category AS CATEGORY
                from POINT_TOTAL AS PT
                LEFT OUTER JOIN POINT_RETURN AS PR ON SUBSTRING_INDEX(PT.Content,'/',-1) = PR.ESTIMATE_SHORT_ID AND PT.UserID = PR.USER_ID
                where RegDate > '2015-05-01'
                AND PT.UserID = '" . $companyId . "'
                order by time desc";

        $query = DB::select(DB::raw($query));
        $form = array();
        for ($i = 0; $i < count($query); $i++) {
            $form['ID'] = $query[$i]->ID;
            $form['GUBUN'] = $query[$i]->GUBUN;
            $form['CONTENT'] = $query[$i]->CONTENT;
            $form['TIME'] = $query[$i]->TIME;
            $form['POINT'] = $query[$i]->POINT;
            $form['CASH_AMOUNT'] = $query[$i]->CASH_AMOUNT;
            $form['CATEGORY_NAME'] = $query[$i]->CATEGORY_NAME;
            $form['Category'] = $query[$i]->Category;
            array_push($data['list'], $form);
        }

        return response()->json($data);
    }

    public function ProductList(Request $request)
    {
        $query = "SELECT
                    PRODUCT_ID, PRODUCT_CATEGORY, PRODUCT_NAME, PRODUCT_POINT, PRODUCT_AMOUNT, PRODUCT_DISCOUNT, PRODUCT_AREA_COUNT, PRODUCT_REGDATE, PRODUCT_REGMNG, PRODUCT_USEYN, DATE_FORMAT(PRODUCT_STARTDATE, '%Y-%m-%d'),
	                (SELECT ADMIN_R_NAME FROM ADMIN WHERE ADMIN_ID = PRODUCT_REGMNG) AS ADMIN_NAME,
	                CASE PRODUCT_CATEGORY WHEN 0 THEN '요금제' WHEN 1 THEN '추가상품' END AS PRODUCT_CATEGORY_NAME,
	                PRODUCT_FINAL_AMOUNT,
	                (SELECT COUNT(1) FROM MV_LIST WHERE MV_LIST_POINT_PRODUCT = PRODUCT_ID) AS ProductCount
                FROM POINT_PRODUCT WHERE PRODUCT_USEYN = 'Y' ORDER BY PRODUCT_ID";

        $query = DB::select(DB::raw($query));

        return response()->json(array('list' => $query));
//        return response()->json($query);
    }

    public function readPointDownContent(Request $request)
    {

        $companyId = $request->companyId;
        $companyType = $request->companyType;

        $data['call_history'] = array();
        $data['call_user'] = array();


        if ($companyType == "move") {
//            $query = MV_HISTORY::selectRaw('TIME, POINT, PHONE, ESTIMATE_LIST_ID, ESTIMATE_SHORT_ID')
//                ->where('FK_MV_LIST', '=', $companyId)
//                ->orderBy('ID', 'DESC')->get();
//            $form = array();
//            for($i=0; $i<count($query); $i++){
//                $form['TIME'] = $query[$i]->TIME;
//                $form['POINT'] = $query[$i]->POINT;
//                $form['PHONE'] = $query[$i]->PHONE;
//                $form['ESTIMATE_LIST_ID'] = $query[$i]->ESTIMATE_LIST_ID;
//                $form['ESTIMATE_SHORT_ID'] = $query[$i]->ESTIMATE_SHORT_ID;
//
//                array_push($data['call_history'], $form);
//            }
//
//            $query2 = POINT_TOTAL::selectRaw('RegDate, outPoint, Content')
//                ->where('MV_CL', '=', 'MV')
//                ->where('UserID', '=', $companyId)
//                ->where('Content', 'LIKE', '%소비자%')->get();
//
//            $form = array();
//            for($i=0; $i<count($query2); $i++){
//                $form['TIME'] = $query2[$i]->RegDate;
//                $form['OUT_POINT'] = $query2[$i]->outPoint;
//                $form['CONTENT'] = $query2[$i]->Content;
//
//                array_push($data['call_history'], $form);
//            }
//
//            foreach($data['call_history'] as $person){
//                foreach($person as $key=>$value){
//                    if(!isset($sortArray[$key])){
//                        $sortArray[$key] = array();
//                    }
//                    $sortArray[$key][] = $value;
//                }
//            }
//
//            array_multisort($sortArray['TIME'],SORT_DESC,$data['call_history']);

            $query = 'select * from
(
   select TIME, POINT, PHONE, ESTIMATE_LIST_ID, ESTIMATE_SHORT_ID from MV_HISTORY where FK_MV_LIST = "' . $companyId . '"
   union all
   select RegDate AS TIME, outPoint AS POINT, Content AS PHONE, null AS ESTIMATE_LIST_ID, null as ESTIMATE_SHORT_ID from POINT_TOTAL where MV_CL = "MV" AND UserID = "' . $companyId . '" and CONTENT like "소비자%"
) AS T order by TIME desc;';

            $query = DB::select(DB::raw($query));

        } else if ($companyType == "clean") {
//            $query = CL_HISTORY::selectRaw('TIME, POINT, PHONE, ESTIMATE_LIST_ID, ESTIMATE_SHORT_ID')
//                ->where('FK_CL_LIST', '=', $companyId)
//                ->orderBy('ID', 'DESC')->get();

            $query = 'select * from
(
   select TIME, POINT, PHONE, null as ESTIMATE_LIST_ID, ESTIMATE_SHORT_ID from CL_HISTORY where FK_CL_LIST = "' . $companyId . '"
   union all
   select RegDate AS TIME, outPoint AS POINT, Content AS PHONE, null AS ESTIMATE_LIST_ID, null as ESTIMATE_SHORT_ID from POINT_TOTAL where MV_CL = "CL" AND UserID = "' . $companyId . '" and CONTENT like "소비자%"
) AS T order by TIME desc;';
            $query = DB::select(DB::raw($query));
        }

//        return response()->json(array('call_history' => $array, 'call_user' => $query2));
        return response()->json(array('call_history' => $query));
    }

    function readPointBreakDownContent(Request $request)
    {
        $companyId = $request->companyId;
        $companyType = $request->companyType;

        $data['call_history'] = array();
        $tableName = $this->checkCompanyType($companyType);

        if ($companyType == "move") {
            $query = 'SELECT TIME, POINT, PHONE, ESTIMATE_LIST_ID, ESTIMATE_SHORT_ID FROM ' . $tableName . '_HISTORY WHERE FK_' . $tableName . '_LIST = ' . $companyId . ' ORDER BY ID DESC;';
            $query = DB::select( DB::raw( $query ) );

            if(count($query)){
                for($i=0; $i<count($query); $i++){
                    $history = array();
                    $history['date'] = explode(" ", $query[$i]->TIME)[$i];
                    $history['point'] = $query[$i]->POINT;
                    $history['phone'] = $query[$i]->PHONE;
                    $history['listId'] = $query[$i]->ESTIMATE_LIST_ID;
                    $history['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                    array_push($data['call_history'], $history);
                }
            }



        } elseif ($companyType == "clean") {
            $query = 'SELECT TIME, POINT, PHONE, ESTIMATE_SHORT_ID FROM ' . $tableName . '_HISTORY WHERE FK_' . $tableName . '_LIST = ' . $companyId . ' ORDER BY ID DESC;';
            $query = DB::select( DB::raw( $query ) );

            if(count($query)){
                for($i=0; $i<count($query); $i++){
                    $history = array();
                    $history['date'] = explode(" ", $query[$i]->TIME)[$i];
                    $history['point'] = $query[$i]->POINT;
                    $history['phone'] = $query[$i]->PHONE;
                    $history['listId'] = null;
                    $history['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                    array_push($data['call_history'], $history);
                }
            }
        }

        return response()->json(array('data' => $data));
    }

    public function MovingCompanyList(Request $request)
    {

        $siName = $request->siName;
        $doName = $request->doName;
        $MV_DV = $request->MV_DV;
        $table = "MV";
        $admin = false;

        $divideSiName = array();
        if (!($siName == null)) {

            $divideSiName = explode(" ", $siName);

            $compare = false;
            if (count($divideSiName) > 1) {
                $compare = true;
            } elseif (mb_substr($doName, -1, 1, "UTF-8") == "도") {
                $compare = true;
            }
        } else {
            $compare = false;
        }

        if ($MV_DV == "MV") {
            $mainTable = $table . '_LIST';
            $subTable = $table . '_REGI';
            $otherTable = $table . '_LIST_OTHER_INFO';
            $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $subTable . '_SI, ' . $subTable . '_CHOOSE, ' . $mainTable . '_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
                'FROM ' . $table . '_LIST LEFT JOIN ' . $table . '_REGI ON ' . $table . '_REGI.' . $table . '_REGI_FK_LIST = ' . $table . '_LIST.' . $table . '_LIST_ID LEFT JOIN ' . $otherTable . ' ON ' . $mainTable . '.MV_LIST_ID = ' . $otherTable . '.MV_LIST_ID WHERE ' . $table . '_REGI_DO = "' . $doName . '" AND MV_LIST.USEYN="Y" order by MV_REGI.MV_REGI_CHOOSE asc;';

            if ($siName != null) {
                $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $subTable . '_SI, ' . $subTable . '_CHOOSE, ' . $mainTable . '_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
                    'FROM ' . $table . '_LIST LEFT JOIN ' . $table . '_REGI ON ' . $table . '_REGI.' . $table . '_REGI_FK_LIST = ' . $table . '_LIST.' . $table . '_LIST_ID LEFT JOIN ' . $otherTable . ' ON ' . $mainTable . '.MV_LIST_ID = ' . $otherTable . '.MV_LIST_ID WHERE ' . $table . '_REGI_DO = "' . $doName . '" AND ' . $table . '_REGI_SI = "' . $siName . '" AND MV_LIST_TYPE!=2 AND MV_LIST.USEYN="Y" order by MV_REGI.MV_REGI_CHOOSE asc;';
            }
        } else {
            $query = 'SELECT MV_LIST.MV_LIST_ID, MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_DESCRIPTION, MV_LIST_GRADE,
                MV_LIST_LICENSE, MV_LIST_ICON, MV_LIST_HOMEPAGE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_HOME_PHONE, MV_LIST_ETC_PHONE,
                MV_LIST_CHOOSE_PHONE, MV_REGI_SI, MV_REGI_CHOOSE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD
                FROM MV_LIST
                LEFT JOIN MV_REGI ON MV_REGI.MV_REGI_FK_LIST = MV_LIST.MV_LIST_ID
                LEFT JOIN MV_LIST_OTHER_INFO ON MV_LIST.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                WHERE MV_REGI_DO = "' . $doName . '" AND MV_REGI_SI = "' . $siName . '" AND (MV_LIST_TYPE=2 OR MV_LIST_TYPE=3 or MV_LIST_TYPE=4) AND MV_LIST.USEYN="Y" order by MV_REGI_CHOOSE asc;';
        }


        $query = DB::select(DB::raw($query));

        $data = array();
        $form = array();

        for ($i = 0; $i < count($query); $i++) {
            $form["id"] = $query[$i]->MV_LIST_ID;
            $form["name"] = $query[$i]->MV_LIST_NAME;
            $form["address"] = $query[$i]->MV_LIST_ADDRESS;
            $form["latitude"] = $query[$i]->MV_LIST_LATITUDE;
            $form["longitude"] = $query[$i]->MV_LIST_LONGITUDE;
            $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
            $form["grade"] = $query[$i]->MV_LIST_GRADE;
            $form["license"] = $query[$i]->MV_LIST_LICENSE;
            if ($query[$i]->MV_LIST_ICON == NULL || $query[$i]->MV_LIST_ICON == '' || $query[$i]->MV_LIST_ICON == 'NULL' || $query[$i]->MV_LIST_ICON == 'null') {
                $form["icon"] = "";
            } else {
                $form["icon"] = "http://www.gae8.com/24moa/upload/" . $query[$i]->MV_LIST_ICON;
            }
            if ($query[$i]->MV_LIST_HOMEPAGE == NULL || $query[$i]->MV_LIST_HOMEPAGE == '' || $query[$i]->MV_LIST_HOMEPAGE == 'NULL' || $query[$i]->MV_LIST_HOMEPAGE == 'null') {
                $form["homepage"] = "";
            } else {
                $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;
            }


            if ($query[$i]->MV_LIST_CHOOSE_PHONE == 0) {
                if ($query[$i]->MV_LIST_COMPANY_PHONE == null) {
                    $form["phone"] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                } else {
                    $form["phone"] = $query[$i]->MV_LIST_COMPANY_PHONE;
                }
            } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 1) {
                $form["phone"] = $query[$i]->MV_LIST_PRIVATE_PHONE;
            } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 2) {
                $form["phone"] = $query[$i]->MV_LIST_HOME_PHONE;
            }

            if ($query[$i]->MV_LIST_TYPE == 3) {
                $form["type"] = 0;
            } else {
                $form["type"] = $query[$i]->MV_LIST_TYPE;
            }


            if (!($query[$i]->SS_CARD_PAY == 1)) {
                $form["samsung"] = "0";
            } else {
                $form["samsung"] = $query[$i]->SS_CARD_PAY;
            }

            if (!($query[$i]->NM_CARD_PAY == 1)) {
                $form["card"] = "0";
            } else {
                $form["card"] = $query[$i]->NM_CARD_PAY;
            }

            if (!($query[$i]->EVENT_ADD == 1)) {
                $form["event"] = "0";
            } else {
                $form["event"] = $query[$i]->EVENT_ADD;
            }


            if ($admin) {
                $form['estimate_phone'] = $query[$i]->MV_LIST_ETC_PHONE;
                if ($siName == "All" && $query[$i]->MV_REGI_CHOOSE == 0) {
                    array_push($data, $form);
                } elseif ($siName != "All") {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 0) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($compare) {
                    if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                        array_push($data, $form);
                    }
                } else {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 1 || $query[$i]->MV_REGI_CHOOSE == 3) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 2) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            }


        }

//        $queries = DB::getQueryLog();
//        var_dump($queries);

        return response()->json(array('data' => $data));
//        return response()->json($query);
    }

    public function DeliverCompanyList(Request $request)
    {

        $siName = $request->siName;
        $doName = $request->doName;

        $admin = false;

        $divideSiName = array();
        if (!($siName == null)) {

            $divideSiName = explode(" ", $siName);

            $compare = false;
            if (count($divideSiName) > 1) {
                $compare = true;
            } elseif (mb_substr($doName, -1, 1, "UTF-8") == "도") {
                $compare = true;
            }
        } else {
            $compare = false;
        }


        $query = 'SELECT MV_LIST.MV_LIST_ID, MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_DESCRIPTION, MV_LIST_GRADE,
                MV_LIST_LICENSE, MV_LIST_ICON, MV_LIST_HOMEPAGE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_HOME_PHONE, MV_LIST_ETC_PHONE,
                MV_LIST_CHOOSE_PHONE, MV_REGI_SI, MV_REGI_CHOOSE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD
                FROM MV_LIST
                LEFT JOIN MV_REGI ON MV_REGI.MV_REGI_FK_LIST = MV_LIST.MV_LIST_ID
                LEFT JOIN MV_LIST_OTHER_INFO ON MV_LIST.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                WHERE MV_REGI_DO = "' . $doName . '" AND MV_REGI_SI = "' . $siName . '" AND (MV_LIST_TYPE=2 OR MV_LIST_TYPE=3 or MV_LIST_TYPE=4) AND MV_LIST.USEYN="Y" order by MV_REGI_CHOOSE asc;';


        $query = DB::select(DB::raw($query));

        $data = array();
        $form = array();

        for ($i = 0; $i < count($query); $i++) {
            $form["id"] = $query[$i]->MV_LIST_ID;
            $form["name"] = $query[$i]->MV_LIST_NAME;
            $form["address"] = $query[$i]->MV_LIST_ADDRESS;
            $form["latitude"] = $query[$i]->MV_LIST_LATITUDE;
            $form["longitude"] = $query[$i]->MV_LIST_LONGITUDE;
            $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
            $form["grade"] = $query[$i]->MV_LIST_GRADE;
            $form["license"] = $query[$i]->MV_LIST_LICENSE;
            if ($query[$i]->MV_LIST_ICON == NULL || $query[$i]->MV_LIST_ICON == '' || $query[$i]->MV_LIST_ICON == 'NULL' || $query[$i]->MV_LIST_ICON == 'null') {
                $form["icon"] = "";
            } else {
                $form["icon"] = "http://www.gae8.com/24moa/upload/" . $query[$i]->MV_LIST_ICON;
            }
            if ($query[$i]->MV_LIST_HOMEPAGE == NULL || $query[$i]->MV_LIST_HOMEPAGE == '' || $query[$i]->MV_LIST_HOMEPAGE == 'NULL' || $query[$i]->MV_LIST_HOMEPAGE == 'null') {
                $form["homepage"] = "";
            } else {
                $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;
            }


            if ($query[$i]->MV_LIST_CHOOSE_PHONE == 0) {
                if ($query[$i]->MV_LIST_COMPANY_PHONE == null) {
                    $form["phone"] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                } else {
                    $form["phone"] = $query[$i]->MV_LIST_COMPANY_PHONE;
                }
            } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 1) {
                $form["phone"] = $query[$i]->MV_LIST_PRIVATE_PHONE;
            } elseif ($query[$i]->MV_LIST_CHOOSE_PHONE == 2) {
                $form["phone"] = $query[$i]->MV_LIST_HOME_PHONE;
            }

            if ($query[$i]->MV_LIST_TYPE == 3) {
                $form["type"] = 0;
            } else {
                $form["type"] = $query[$i]->MV_LIST_TYPE;
            }


            if (!($query[$i]->SS_CARD_PAY == 1)) {
                $form["samsung"] = "0";
            } else {
                $form["samsung"] = $query[$i]->SS_CARD_PAY;
            }

            if (!($query[$i]->NM_CARD_PAY == 1)) {
                $form["card"] = "0";
            } else {
                $form["card"] = $query[$i]->NM_CARD_PAY;
            }

            if (!($query[$i]->EVENT_ADD == 1)) {
                $form["event"] = "0";
            } else {
                $form["event"] = $query[$i]->EVENT_ADD;
            }


            if ($admin) {
                $form['estimate_phone'] = $query[$i]->MV_LIST_ETC_PHONE;
                if ($siName == "All" && $query[$i]->MV_REGI_CHOOSE == 0) {
                    array_push($data, $form);
                } elseif ($siName != "All") {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 0) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($compare) {
                    if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                        array_push($data, $form);
                    }
                } else {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 1 || $query[$i]->MV_REGI_CHOOSE == 3) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            } elseif ($query[$i]->MV_REGI_CHOOSE == 2) {
                $form["priority"] = $query[$i]->MV_REGI_CHOOSE;
                if ($this->checkAddress($query[$i]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            }


        }

//        $queries = DB::getQueryLog();
//        var_dump($queries);

        return response()->json(array('data' => $data));
//        return response()->json($query);
    }

    public function companyInfo(Request $request)
    {

        $companyId = $request->companyId;

        $query = MV_LIST::leftJoin('MV_LIST_OTHER_INFO AS MO', 'MV_LIST.MV_LIST_ID', '=', 'MO.MV_LIST_ID')
            ->leftJoin('POINT_CHARGE_INFO AS PC', 'PC.USER_ID', '=', 'MV_LIST.MV_LIST_ID')
            ->selectRaw('MV_LIST_TYPE, MV_LIST_NAME, MV_LIST_ETC_PHONE, MV_LIST_ADDRESS, MV_LIST_BIZNO, MV_LIST_AGENCY, (SELECT PRODUCT_NAME FROM POINT_PRODUCT WHERE PRODUCT_ID = MV_LIST_POINT_PRODUCT) AS PRODUCT_NAME,
                        MV_LIST_ICON, MV_LIST_HOMEPAGE, NM_CARD_PAY, SS_CARD_PAY, WOMAN_WORKER, CARD_COMMISSION, TRUCK_WEIGHT,
                        (SELECT CODE_NAME FROM CODE WHERE CODE_VALUE=CB_CODE) AS PAY_NAME, CB_NUMBER, BANK_OWNER, BANK_OWNER_RELATION, CARD_MONTH, CARD_YEAR, PAYMENT_DATE')
            ->where('MV_LIST.MV_LIST_ID', '=', $companyId)
            ->orderBy('PC.PAYMENT_INSERT', 'DESC')
            ->skip(0)->take(1)->get();

        $query2 = MV_REGI::selectRaw('MV_REGI_DO, MV_REGI_SI, MV_REGI_CHOOSE')
            ->where('MV_REGI_FK_LIST', '=', $companyId)->get();


        return response()->json(array('info' => $query, 'area' => $query2));
    }

    public function version(Request $request)
    {
        DB::beginTransaction();

        $UserID = $request->UserID;
        $UserPhone = $request->UserPhone;
        $AppVersion = $request->AppVersion;
        $OsVersion = $request->OsVersion;
        $WhichUser = $request->WhichUser;

//        $query = MV_LIST_OTHER_INFO::selectRaw('VERSION')
//            ->where('MV_LIST_ID', '=', $companyId)
//            ->where('VERSION', '=', $version)->get();

        $query = "SELECT COUNT(1) AS COUNT FROM USER_APP_VERSION WHERE USERID = '" . $UserID . "'";
        $query = DB::select(DB::raw($query));

        if ($query[0]->COUNT > 0) {
            $query = 'UPDATE USER_APP_VERSION SET APPVERSION = "' . $AppVersion . '", OS_VERSION = "' . $OsVersion . '", REGDATE = NOW() WHERE USERID = "' . $UserID . '"';
        } elseif ($query[0]->COUNT < 1) {
            $query = 'INSERT INTO USER_APP_VERSION (USERID, USER_PHONE, APPVERSION, REGDATE, WHICH_APP, OS_VERSION, WHICH_USER) VALUES ("' . $UserID . '", "' . $UserPhone . '", "' . $AppVersion . '", NOW(), "0",  "' . $OsVersion . '", "' . $WhichUser . '")';
        }
        $query = DB::statement(DB::raw($query));

        if ($query) {
            DB::commit();
            return response()->json(array('data' => $query));
        } else {
            DB::rollback();
            return response()->json(array('data' => false));
        }


//        if(count($query) > 0){
//            DB::commit();
//            return response()->json(array('data' => 'same'));
//        } else {
//            $query = 'UPDATE MV_LIST_OTHER_INFO SET VERSION="'.$version.'" WHERE MV_LIST_ID="'.$companyId.'";';
//            $query = DB::statement( DB::raw($query) );
//
//            if($query){
//                DB::commit();
//                return response()->json(array('data' => true));
//            } else {
//                DB::rollback();
//                return response()->json(array('data' => false));
//            }
//        }
    }

    public function nearbycompany(Request $request)
    {
        $doName = $request->doName;
        $siName = $request->siName;
        $admin = false;

        $divideSiName = array();
        if (!($siName == null)) {

            $divideSiName = explode(" ", $siName);

            $compare = false;
            if (count($divideSiName) > 1) {
                $compare = true;
            } elseif (mb_substr($doName, -1, 1, "UTF-8") == "도") {
                $compare = true;
            }
        } else {
            $compare = false;
        }

        $query = 'SELECT MV_LIST.MV_LIST_ID, MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_DESCRIPTION, MV_LIST_GRADE,
                MV_LIST_LICENSE, MV_LIST_ICON, MV_LIST_HOMEPAGE, MV_LIST_COMPANY_PHONE, MV_LIST_PRIVATE_PHONE, MV_LIST_HOME_PHONE, MV_LIST_ETC_PHONE,
                MV_LIST_CHOOSE_PHONE, MV_REGI_SI, MV_REGI_CHOOSE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD
                FROM MV_LIST
                LEFT JOIN MV_REGI ON MV_REGI.MV_REGI_FK_LIST = MV_LIST.MV_LIST_ID
                LEFT JOIN MV_LIST_OTHER_INFO ON MV_LIST.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                WHERE MV_REGI_DO = "' . $doName . '" AND MV_REGI_SI = "' . $siName . '" AND (MV_LIST_TYPE=2 OR MV_LIST_TYPE=3) ORDER BY rand() limit 1;';

        $query = DB::select(DB::raw($query));

        $data = array();
        $form = array();


        $form["id"] = $query[0]->MV_LIST_ID;
        $form["name"] = $query[0]->MV_LIST_NAME;
        $form["address"] = $query[0]->MV_LIST_ADDRESS;
        $form["latitude"] = $query[0]->MV_LIST_LATITUDE;
        $form["longitude"] = $query[0]->MV_LIST_LONGITUDE;
        $form["description"] = $query[0]->MV_LIST_DESCRIPTION;
        $form["grade"] = $query[0]->MV_LIST_GRADE;
        $form["license"] = $query[0]->MV_LIST_LICENSE;
        if ($query[0]->MV_LIST_ICON == NULL || $query[0]->MV_LIST_ICON == '' || $query[0]->MV_LIST_ICON == 'NULL' || $query[0]->MV_LIST_ICON == 'null') {
            $form["icon"] = "";
        } else {
            $form["icon"] = "http://www.gae8.com/24moa/upload/" . $query[0]->MV_LIST_ICON;
        }
        if ($query[0]->MV_LIST_HOMEPAGE == NULL || $query[0]->MV_LIST_HOMEPAGE == '' || $query[0]->MV_LIST_HOMEPAGE == 'NULL' || $query[0]->MV_LIST_HOMEPAGE == 'null') {
            $form["homepage"] = "";
        } else {
            $form["homepage"] = $query[0]->MV_LIST_HOMEPAGE;
        }


        if ($query[0]->MV_LIST_CHOOSE_PHONE == 0) {
            if ($query[0]->MV_LIST_COMPANY_PHONE == null) {
                $form["phone"] = $query[0]->MV_LIST_PRIVATE_PHONE;
            } else {
                $form["phone"] = $query[0]->MV_LIST_COMPANY_PHONE;
            }
        } elseif ($query[0]->MV_LIST_CHOOSE_PHONE == 1) {
            $form["phone"] = $query[0]->MV_LIST_PRIVATE_PHONE;
        } elseif ($query[0]->MV_LIST_CHOOSE_PHONE == 2) {
            $form["phone"] = $query[0]->MV_LIST_HOME_PHONE;
        }

        if ($query[0]->MV_LIST_TYPE == 3) {
            $form["type"] = 0;
        } else {
            $form["type"] = $query[0]->MV_LIST_TYPE;
        }


        if (!($query[0]->SS_CARD_PAY == 1)) {
            $form["samsung"] = "0";
        } else {
            $form["samsung"] = $query[0]->SS_CARD_PAY;
        }

        if (!($query[0]->NM_CARD_PAY == 1)) {
            $form["card"] = "0";
        } else {
            $form["card"] = $query[0]->NM_CARD_PAY;
        }

        if (!($query[0]->EVENT_ADD == 1)) {
            $form["event"] = "0";
        } else {
            $form["event"] = $query[0]->EVENT_ADD;
        }


        if ($admin) {
            $form['estimate_phone'] = $query[0]->MV_LIST_ETC_PHONE;
            if ($siName == "All" && $query[0]->MV_REGI_CHOOSE == 0) {
                array_push($data, $form);
            } elseif ($siName != "All") {
                array_push($data, $form);
            }
        } elseif ($query[0]->MV_REGI_CHOOSE == 0) {
            $form["priority"] = $query[0]->MV_REGI_CHOOSE;
            if ($compare) {
                if ($this->checkAddress($query[0]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            } else {
                array_push($data, $form);
            }
        } elseif ($query[0]->MV_REGI_CHOOSE == 1 || $query[0]->MV_REGI_CHOOSE == 3) {
            $form["priority"] = $query[0]->MV_REGI_CHOOSE;
            if ($this->checkAddress($query[0]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                array_push($data, $form);
            }
        } elseif ($query[0]->MV_REGI_CHOOSE == 2) {
            $form["priority"] = $query[0]->MV_REGI_CHOOSE;
            if ($this->checkAddress($query[0]->MV_REGI_SI, $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                array_push($data, $form);
            }
        }


//        $queries = DB::getQueryLog();
//        var_dump($queries);

        return response()->json(array('data' => $data));
    }

    function AroundCompany(Request $request)
    {
        $doName = $request->doName;
        $siName = $request->siName;
        $admin = false;
        $table = "MV";

        $divideSiName = array();
        if (!($siName == null)) {

            $divideSiName = explode(" ", $siName);

            $compare = false;
            if (count($divideSiName) > 1) {
                $compare = true;
            } elseif (mb_substr($doName, -1, 1, "UTF-8") == "도") {
                $compare = true;
            }
        } else {
            $compare = false;
        }

        $mainTable = $table . '_LIST';
        $subTable = $table . '_REGI';
        $otherTable = $table . '_LIST_OTHER_INFO';
        $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $subTable . '_SI, ' . $subTable . '_CHOOSE, ' . $mainTable . '_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
            'FROM ' . $table . '_LIST LEFT JOIN ' . $table . '_REGI ON ' . $table . '_REGI.' . $table . '_REGI_FK_LIST = ' . $table . '_LIST.' . $table . '_LIST_ID LEFT JOIN ' . $otherTable . ' ON ' . $mainTable . '.MV_LIST_ID = ' . $otherTable . '.MV_LIST_ID WHERE ' . $table . '_REGI_DO = "' . $doName . '";';

//        if($siName != "All" && $admin){
//            $query = 'SELECT '.$mainTable.'_ID, '.$mainTable.'_NAME, '.$mainTable.'_ADDRESS, '.$mainTable.'_LATITUDE, '.$mainTable.'_LONGITUDE, '.$mainTable.'_DESCRIPTION, '.$mainTable.'_GRADE,
//        '.$mainTable.'_LICENSE, '.$mainTable.'_ICON, '.$mainTable.'_HOMEPAGE, '.$mainTable.'_COMPANY_PHONE, '.$mainTable.'_PRIVATE_PHONE, '.$mainTable.'_HOME_PHONE, '.$mainTable.'_ETC_PHONE,
//        '.$mainTable.'_CHOOSE_PHONE, '.$subTable.'_SI, '.$subTable.'_CHOOSE, '.$mainTable.'_TYPE
//         FROM '.$table.'_LIST LEFT JOIN '.$table.'_REGI ON '.$table.'_REGI.'.$table.'_REGI_FK_LIST = '.$table.'_LIST.'.$table.'_LIST_ID WHERE '.$table.'_REGI_DO = "'.$doName.'" AND '.$table.'_REGI_SI = "'.$siName.'" ORDER BY MV_LIST_NAME;';
//        }

        if ($siName != null) {
            $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $subTable . '_SI, ' . $subTable . '_CHOOSE, ' . $mainTable . '_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
                'FROM ' . $table . '_LIST LEFT JOIN ' . $table . '_REGI ON ' . $table . '_REGI.' . $table . '_REGI_FK_LIST = ' . $table . '_LIST.' . $table . '_LIST_ID LEFT JOIN ' . $otherTable . ' ON ' . $mainTable . '.MV_LIST_ID = ' . $otherTable . '.MV_LIST_ID WHERE ' . $table . '_REGI_DO = "' . $doName . '" AND ' . $table . '_REGI_SI = "' . $siName . '" AND MV_LIST_TYPE!=2;';
        }

        $query = DB::select(DB::raw($query));

        if (empty($query)) {
            return response()->json(array('data' => false));
        }

        $data = array();
        $form = array();
        for ($i = 0; $i < count($query); $i++) {
            $form["id"] = $query[$i]->$mainTable . '_ID';
            $form["name"] = $query[$i]->$mainTable . '_NAME';
            $form["address"] = $query[$i]->$mainTable . '_ADDRESS';
            $form["latitude"] = $query[$i]->$mainTable . '_LATITUDE';
            $form["longitude"] = $query[$i]->$mainTable . '_LONGITUDE';
            $form["description"] = $query[$i]->$mainTable . '_DESCRIPTION';
            $form["grade"] = $query[$i]->$mainTable . '_GRADE';
            $form["license"] = $query[$i]->$mainTable . 'LICENSE';
            $icon = $query[$i]->$mainTable . '_ICON';
            if (!empty($icon)) {
                $form["icon"] = "www.gae8.com/24moa/upload/" . $query[$i]->$mainTable . '_ICON';
            } else {
                $form["icon"] = "";
            }
            $form["homepage"] = $query[$i]->$mainTable . '_HOMEPAGE';
            $choosePhone = $query[$i]->$mainTable . '_CHOOSE_PHONE';
            $companyPhone = $query[$i]->$mainTable . '_COMPANY_PHONE';
            $privatePhone = $query[$i]->$mainTable . '_PRIVATE_PHONE';
            $etcPhone = $query[$i]->$mainTable . '_ETC_PHONE';
            if ($choosePhone == 0) {
                if ($companyPhone == null) {
                    $form["phone"] = $privatePhone;
                } else {
                    $form["phone"] = $companyPhone;
                }
            } elseif ($choosePhone == 1) {
                $form["phone"] = $privatePhone;
            } elseif ($choosePhone == 2) {
                $form["phone"] = $etcPhone;
            }

            $type = $query[$i]->$mainTable . '_TYPE';
            if ($type == 3) {
                $form["type"] = 0;
            } else {
                $form["type"] = $type;
            }


            if (count($query[$i]) > 17) {

                if (!($query[$i]->SS_CARD_PAY == 1)) {
                    $form["samsung"] = "0";
                } else {
                    $form["samsung"] = $query[$i]->SS_CARD_PAY;
                }

                if (!($query[$i]->NM_CARD_PAY == 1)) {
                    $form["card"] = "0";
                } else {
                    $form["card"] = $query[$i]->NM_CARD_PAY;
                }

                if ((!$query[$i]->EVENT_ADD == 1)) {
                    $form["event"] = "0";
                } else {
                    $form["event"] = $query[$i]->EVENT_ADD;
                }

            }
            $choose = $query[$i]->$subTable . '_CHOOSE';
            if ($admin) {
                $form['estimate_phone'] = $query[$i]->$etcPhone;
                $choose = $query[$i]->$subTable . '_CHOOSE';
                if ($siName == "All" && $choose == 0) {
                    array_push($data, $form);
                } elseif ($siName != "All") {
                    array_push($data, $form);
                }
            } elseif ($choose == 0) {
                $form["priority"] = $choose;
                if ($compare) {
                    if ($this->checkAddress($query[$i]->$subTable . '_SI', $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                        array_push($data, $form);
                    }
                } else {
                    array_push($data, $form);
                }
            } elseif ($choose == 1 || $choose == 3) {
                $form["priority"] = $choose;
                if ($this->checkAddress($query[$i]->$subTable . '_SI', $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            } elseif ($choose == 2) {
                $form["priority"] = $choose;
                if ($this->checkAddress($query[$i]->$subTable . '_SI', $this->checkNull($divideSiName, count($divideSiName), 0), $this->checkNull($divideSiName, count($divideSiName), 1))) {
                    array_push($data, $form);
                }
            }
        }

        return response()->json(array('data' => $data));
    }

    function mybiddingData(Request $request)
    {
        $phone = $request->phone;
        $admin = false;
        $table = "MV";

        $mainTable = $table . '_LIST';
        $subTable = $table . '_REGI';
        $otherTable = $table . '_LIST_OTHER_INFO';

        if ($table == "MV") {
            $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $mainTable . '_TYPE, ' . 'SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD ' .
                'FROM ' . $table . '_LIST LEFT JOIN ' . $otherTable . ' ON ' . $mainTable . '.MV_LIST_ID = ' . $otherTable . '.MV_LIST_ID
             LEFT OUTER JOIN MV_HISTORY ON MV_HISTORY.PHONE=REPLACE("' . $phone . '", "+82", "0")
             WHERE ' . $table . '_HISTORY.FK_' . $table . '_LIST = ' . $mainTable . '.' . $mainTable . '_ID ORDER BY MV_HISTORY.TIME DESC;';

        } else if ($table == "CL") {

            $query = 'SELECT ' . $mainTable . '.' . $mainTable . '_ID, ' . $mainTable . '_NAME, ' . $mainTable . '_ADDRESS, ' . $mainTable . '_LATITUDE, ' . $mainTable . '_LONGITUDE, ' . $mainTable . '_DESCRIPTION, ' . $mainTable . '_GRADE,
        ' . $mainTable . '_LICENSE, ' . $mainTable . '_ICON, ' . $mainTable . '_HOMEPAGE, ' . $mainTable . '_COMPANY_PHONE, ' . $mainTable . '_PRIVATE_PHONE, ' . $mainTable . '_HOME_PHONE, ' . $mainTable . '_ETC_PHONE,
        ' . $mainTable . '_CHOOSE_PHONE, ' . $mainTable . '_TYPE
        FROM ' . $table . '_LIST
         LEFT OUTER JOIN CL_HISTORY ON CL_HISTORY.PHONE=REPLACE("' . $phone . '", "+82", "0")
         WHERE ' . $table . '_HISTORY.FK_' . $table . '_LIST = ' . $mainTable . '.' . $mainTable . '_ID;';

        }

        $query = DB::select(DB::raw($query));

        if (empty($query)) {
            return response()->json(array('data' => false));
        }

        $data = array();
        $form = array();
        for ($i = 0; $i < count($query); $i++) {
            $form["id"] = $query[$i]->$mainTable . _ID;
            $form["name"] = $query[$i]->$mainTable . _NAME;
            $form["address"] = $query[$i]->$mainTable . _ADDRESS;
            $form["latitude"] = $query[$i]->$mainTable . _LATITUDE;
            $form["longitude"] = $query[$i]->$mainTable . _LONGITUDE;
            $form["description"] = $query[$i]->$mainTable . _DESCRIPTION;
            $form["grade"] = $query[$i]->$mainTable . _GRADE;
            $form["license"] = $query[$i]->$mainTable . _LICENSE;
            $icon = $query[$i]->$mainTable . _ICON;
            if (!empty($icon)) {
                $form["icon"] = "www.gae8.com/24moa/upload/" . $icon;
            } else {
                $form["icon"] = "";
            }
            $form["homepage"] = $query[$i]->$mainTable . _HOMEPAGE;

            $choosePhone = $query[$i]->$mainTable . _CHOOSE_PHONE;
            if ($choosePhone == 0) {
                $form["phone"] = $query[$i]->$mainTable . _COMPANY_PHONE;
            } elseif ($choosePhone == 1) {
                $form["phone"] = $query[$i]->$mainTable . _PRIVATE_PHONE;
            } elseif ($choosePhone == 2) {
                $form["phone"] = $query[$i]->$mainTable . _HOME_PHONE;
            }
            $form["type"] = $query[$i]->$mainTable . _TYPE;

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

            if ($admin) {
                $form['estimate_phone'] = $query[$i]->$mainTable . _ETC_PHONE;

            }

            array_push($data, $form);
        }

        return $data;
    }

    function CompanyApprove(Request $request)
    {
        $name = $request->name;
        $phone = $request->phone;

//        $query = 'INSERT INTO COMPANY_ACCESS(COMPANY_ACCESS_ID, COMPANY_ACCESS_NAME, COMPANY_ACCESS_PHONE, COMPANY_ACCESS_REGDATE, COMPANY_ACCESS_APPROVE)
//                  VALUES(null, "'.$name.'", "'.$phone.'", now(), "N");';

        $phone = str_replace("+82", "0", $phone);
        $phone = str_replace("-", "", $phone);

        $query = 'INSERT INTO COMPANY_JOIN_FORM(ID, COMPANY_NAME, COMPANY_PHONE, CHK) VALUES(null, "' . $name . '", "' . $phone . '", "N");';

        $query = DB::statement(DB::raw($query));

//        if($query){
//            return response()->json(array('data' => true));
//        } else {
//            return response()->json(array('data' => false));
//        }
        return response()->json(array('data' => $query));

    }

    function QNA()
    {
        $query = 'SELECT COMPANY_QNA_QUESTION AS question, COMPANY_QNA_ANWSER AS answer FROM COMPANY_QNA WHERE COMPANY_QNA_USEYN="Y" order by COMPANY_QNA_NO asc;';
        $query = DB::select(DB::raw($query));

        return response()->json(array('data' => $query));
    }

    function Assign_date(Request $request)
    {
        $companyId = $request->companyId;
        $date1 = $request->date1;
        $date2 = $request->date2;
        $date3 = $request->date3;
        $date4 = $request->date4;
        $date5 = $request->date5;

        $query = "SELECT MV_LIST_POINT_PRODUCT, (SELECT ASSIGN_CHANGE FROM MV_LIST_OTHER_INFO WHERE MV_LIST_ID='" . $companyId . "') AS ASSIGN FROM MV_LIST WHERE MV_LIST_ID='" . $companyId . "'";
        $queryResult = DB::select(DB::raw($query));

        if ($queryResult[0]->MV_LIST_POINT_PRODUCT == 17) {
            if ($queryResult[0]->ASSIGN == "3") {
                $query = 'UPDATE MV_LIST_OTHER_INFO AS T SET ASSIGN_DATE1="' . $date1 . '", ASSIGN_DATE2="' . $date2 . '", ASSIGN_DATE3="' . $date3 . '", ASSIGN_DATE4="' . $date4 . '", ASSIGN_DATE5="' . $date5 . '", ASSIGN_CHANGE=3 WHERE MV_LIST_ID=' . $companyId;
                $query = DB::statement(DB::raw($query));

                if ($query) {
                    return response()->json(array('data' => true));
                } else {
                    return response()->json(array('data' => false));
                }
            } elseif ($queryResult[0]->ASSIGN < "4") {
                $query = "SELECT COUNT(1) AS COUNT FROM MV_LIST_OTHER_INFO WHERE MV_LIST_ID ='" . $companyId . "'";
                $CountResult = DB::select(DB::raw($query));

                if ($CountResult[0]->COUNT < 1) {
                    $query = "INSERT INTO MV_LIST_OTHER_INFO (MV_LIST_ID, ASSIGN_DATE1, ASSIGN_DATE2, ASSIGN_DATE3, ASSIGN_DATE4, ASSIGN_DATE5, ASSIGN_CHANGE)
								VALUES ('" . $companyId . "','" . $date1 . "','" . $date2 . "','" . $date3 . "','" . $date4 . "','" . $date5 . "',1)";
                    $query = DB::statement(DB::raw($query));
                } else {
                    $query = 'UPDATE MV_LIST_OTHER_INFO AS T SET ASSIGN_DATE1="' . $date1 . '", ASSIGN_DATE2="' . $date2 . '", ASSIGN_DATE3="' . $date3 . '", ASSIGN_DATE4="' . $date4 . '", ASSIGN_DATE5="' . $date5 . '", ASSIGN_CHANGE=(IF(T.ASSIGN_CHANGE IS NULL, 0, 1))+1 WHERE MV_LIST_ID=' . $companyId;
                    $query = DB::statement(DB::raw($query));
                }

                if ($query) {
                    return response()->json(array('data' => true));
                } else {
                    return response()->json(array('data' => false));
                }
            } else {
                return response()->json(array('data' => false));
            }
        } else {
            return response()->json(array('data' => false));
        }
    }

    function Assign_date_return(Request $request)
    {
        $companyId = $request->companyId;

        $query = "SELECT COUNT(1) AS COUNT FROM MV_LIST_OTHER_INFO WHERE MV_LIST_ID ='" . $companyId . "'";
        $CountResult = DB::select(DB::raw($query));

        if ($CountResult[0]->COUNT < 1) {
            $query = "INSERT INTO MV_LIST_OTHER_INFO (MV_LIST_ID, ASSIGN_DATE1, ASSIGN_DATE2, ASSIGN_DATE3, ASSIGN_DATE4, ASSIGN_DATE5, ASSIGN_CHANGE)
					VALUES ( '" . $companyId . "',NULL,NULL,NULL,NULL,NULL,0 )";
            $query = DB::statement(DB::raw($query));
        } else {

        }

        $query = "SELECT ASSIGN_DATE1, ASSIGN_DATE2, ASSIGN_DATE3, ASSIGN_DATE4, ASSIGN_DATE5, ASSIGN_CHANGE FROM MV_LIST_OTHER_INFO WHERE MV_LIST_ID ='" . $companyId . "'";
        $query = DB::select(DB::raw($query));

        return response()->json(array('data' => $query));
    }

    function checkPointContent(Request $request)
    {
        $companyId = $request->companyId;
        $companyType = $request->companyType;
        $shortId = $request->short_id;


        $hasPoint = false;
        $maxCount = 15;

        if ($companyType == "move") {
            $tableName = "MV";
        } elseif ($companyType == "clean") {
            $tableName = "CL";
        }

        $grade = 1;
        if ($shortId != null) {
            $query = 'SELECT ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_GRADE FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
            $query = DB::select(DB::raw($query));

            $grade = $query[0]->ESTIMATE_SHORT_GRADE;
            switch ($query[0]->ESTIMATE_SHORT_MAX_REPLIES) {
                case 1:
                    $maxCount = 15;
                    break;
                case 2:
                    $maxCount = 11;
                    break;
                case 3:
                    $maxCount = 9;
                    break;
                case 5:
                    $maxCount = 3;
            }
        }

        $query = "SELECT MV_LIST_POINT_PRODUCT, MV_LIST_GRADE
                                  FROM MV_LIST WHERE MV_LIST_ID='$companyId'";
        $query = DB::select(DB::raw($query));

        if ($query[0]->MV_LIST_GRADE >= $grade) {
            if ($query[0]->MV_LIST_POINT_PRODUCT > 15) {

                return response()->json(array('data' => true));
            }
        } else {

            return response()->json(array('data' => 'permission denied'));
        }


        $query = 'SELECT ' . $tableName . '_POINT_FREE, ' . $tableName . '_POINT_CASH, ' . $tableName . '_POINT_USING FROM ' . $tableName . '_POINT WHERE ' . $tableName . '_POINT_FK_' . strtoupper($companyType) . ' = ' . $companyId . ';';
        $query = DB::select(DB::raw($query));
        if (count($query)) {
            if (($query[0]->MV_POINT_FREE + $query[0]->MV_POINT_CASH) > $query[0]->MV_POINT_USING) {
                if ($query[0]->MV_POINT_FREE + $query[0]->MV_POINT_CASH >= $maxCount) {
                    $hasPoint = true;
                }
            }
        }
        $query = 'SELECT COUNT(*) AS COUNT FROM ' . $tableName . '_NEW_COMPANY WHERE FK_COMPANY_ID = ' . $companyId . ';';
        $query = DB::select(DB::raw($query));
        if (count($query)) {
            if ($query[0]->COUNT == 1) {
                $hasPoint = true;
            }
        }

        return response()->json(array('data' => $hasPoint));
    }

    function CeoCheck(Request $request)
    {
        $UserPhone = $request->UserPhone;

        $result = false;

        $UserPhone = str_replace("+82", "0", $UserPhone);

        $query = "SELECT COUNT(1) AS COUNT FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = '" . $UserPhone . "' AND USER_DEVICE_REGISTERS_COMPANY = 4";
        $query = DB::select(DB::raw($query));

        if ($query[0]->COUNT > 0) {
            $result = true;
        } elseif ($query[0]->COUNT < 1) {
            $result = false;
        }

        return response()->json(array('data' => $result));
    }

    function CeoStateChange(Request $request)
    {
        $UserPhone = $request->UserPhone;

        $UserPhone = str_replace("+82", "0", $UserPhone);

        $CeoIDQuery = "SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = '" . $UserPhone . "' AND USER_DEVICE_REGISTERS_COMPANY = 4";
        $CeoIDQuery = DB::select(DB::raw($CeoIDQuery));

        $CeoIDResult = $CeoIDQuery[0]->USER_DEVICE_REGISTERS_ID;

        $CompanyIDQuery = "SELECT USER_DEVICE_REGISTERS_ID FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = '" . $UserPhone . "' AND USER_DEVICE_REGISTERS_COMPANY = 1";
        $CompanyIDQuery = DB::select(DB::raw($CompanyIDQuery));

        $CompanyIDResult = $CompanyIDQuery[0]->USER_DEVICE_REGISTERS_ID;

        $AlarmQuery = "DELETE FROM ALARM_SWITCH WHERE FK_USER_DEVICE_ID = '" . $CeoIDResult . "'";
        $AlarmQuery = DB::statement(DB::raw($AlarmQuery));

        if ($AlarmQuery) {
            $UpdateEstimateCompanyQuery = "UPDATE ESTIMATE_COMPANY SET ESTIMATE_COMPANY_FK_USER = " . $CompanyIDResult . " WHERE ESTIMATE_COMPANY_FK_USER = " . $CeoIDResult . "";
            $UpdateEstimateCompanyQuery = DB::statement(DB::raw($UpdateEstimateCompanyQuery));

            if ($UpdateEstimateCompanyQuery) {
                $UpdateUserState = "UPDATE USER_DEVICE_REGISTERS SET USER_DEVICE_REGISTERS_COMPANY = 2 WHERE USER_DEVICE_REGISTERS_PHONE = '" . $UserPhone . "' AND USER_DEVICE_REGISTERS_COMPANY = 1";
                $UpdateUserState = DB::statement(DB::raw($UpdateUserState));

                if ($UpdateUserState) {
                    $DeleteUser = "DELETE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_PHONE = '" . $UserPhone . "' AND USER_DEVICE_REGISTERS_COMPANY = 4";
                    $DeleteUser = DB::statement(DB::raw($DeleteUser));

                    if ($DeleteUser) {
                        return response()->json(array('data' => true));
                    } else {
                        return response()->json(array('data' => false));
                    }
                } else {
                    return response()->json(array('data' => "UserDevice Delete Error"));
                }
            } else {
                return response()->json(array('data' => "UserDevice Update Error"));
            }
        } else {
            return response()->json(array('data' => "Estimate Company Error"));
        }
    }

    function CompanyPointUpdate(Request $request)
    {
        $companyId = $request->companyId;
        $pointId = $request->point_id;
        $companyType = $request->companyType;

        $tableName = $this->checkCompanyType($companyType);

        if ($pointId == "point_0001") {
            $point = 50;
            $price = 49500;
        } elseif ($pointId == "point_0002") {
            $point = 100;
            $price = 99000;
        } elseif ($pointId == "point_0003") {
            $point = 150;
            $price = 148500;
        } elseif ($pointId == "point_0004") {
            $point = 200;
            $price = 198000;
        }

        $query = 'UPDATE ' . $tableName . '_POINT SET ' . $tableName . '_POINT_CASH = ' . $tableName . '_POINT.' . $tableName . '_POINT_CASH+' . $point . ' WHERE ' . $tableName . '_POINT_FK_MOVE = ' . $companyId . ';';
        $query = DB::statement( DB::raw( $query ) );
        $query = 'INSERT INTO ' . $tableName . '_PAYMENT VALUES (NULL, 0, ' . $point . ',' . $price . ', "google", ' . $point . ', "' . date("YmdHis") . '", ' . $companyId . ', (SELECT ' . $tableName . '_POINT_ID FROM ' . $tableName . '_POINT WHERE ' . $tableName . '_POINT_FK_MOVE = ' . $companyId . '), NOW())';
        $query = DB::statement( DB::raw( $query ) );
        $query = "INSERT INTO POINT_TOTAL (UserID,Category,inPoint,Money,Gubun,Content,MV_CL,RegDate,MngCode) VALUES (" . $companyId . ", 'PO_IN01', " . $point . ", " . $price . ", 'S', 'google', 'mv', NOW(), 0);";
        $query = DB::statement( DB::raw( $query ) );

        //$query = 'INSERT INTO POINT_TOTAL (UserID,Category,inPoint,Money,Gubun,Content,MV_CL,RegDate,MngCode) VALUES ('.$companyId.', "PO_IN01", '.$point.', '.$price.', "S", "google", "mv", NOW(), 0);';
        //$this->sendQuery($mySql, $query);
        return response()->json(array('data' => true));
    }

    function MovingCompanyReplyList(Request $request){

        $companyId = $request->company_id;

        $query = 'SELECT ESTIMATE_SHORT_FK_LIST, ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES,
        ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, date_format(ESTIMATE_SHORT_REGDATE, "%y.%m.%d") AS ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PHONE, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END
        FROM ESTIMATE_REPLY
        LEFT JOIN ESTIMATE_SHORT ON ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID
        LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_ADDR.ESTIMATE_ADDR_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID WHERE ESTIMATE_REPLY_FK_MOVE = ' . $companyId . ';';

        $query = DB::select( DB::raw( $query ) );

        if(count($query)){
            $data['info'] = array();
            $data['estimate'] = array();
            $form = array();
            for($i=0; $i<count($query); $i++){
                $form['listId'] = $query[$i]->ESTIMATE_SHORT_FK_LIST;
                $form['shortId'] = $query[$i]->ESTIMATE_SHORT_ID;
                $form['count'] = $query[$i]->ESTIMATE_SHORT_COUNT;
                $form['replies'] = $query[$i]->ESTIMATE_SHORT_REPLIES;
                $form['max_replies'] = $query[$i]->ESTIMATE_SHORT_MAX_REPLIES;
                $form['kind'] = $query[$i]->ESTIMATE_SHORT_KIND;
                $form['date'] = $query[$i]->ESTIMATE_SHORT_REGDATE;
                $form['move_date'] = $query[$i]->ESTIMATE_SHORT_MOVE_DATE;
                $form['hasPhone'] = $query[$i]->ESTIMATE_SHORT_PHONE;
                $form['start_address'] = $query[$i]->ESTIMATE_ADDR_START;
                $form['end_address'] = $query[$i]->ESTIMATE_ADDR_END;
                array_push($data['estimate'], $form);
            }

            return response()->json(array('data' => $data));

        } else {
            return response()->json(array('data' => false));
        }
    }

    function NewMovingCompanyReplyList(Request $request){

        $companyId = $request->company_id;

        $query = 'SELECT ESTIMATE_SHORT_FK_LIST AS ESTIMATE_ADDR_FK_LIST, ESTIMATE_SHORT_ID, ESTIMATE_SHORT_COUNT, ESTIMATE_SHORT_REPLIES,
        ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND, date_format(ESTIMATE_SHORT_REGDATE, "%y.%m.%d") AS ESTIMATE_SHORT_REGDATE, ESTIMATE_SHORT_MOVE_DATE, ESTIMATE_SHORT_PHONE, ESTIMATE_ADDR_START, ESTIMATE_ADDR_END
        FROM ESTIMATE_REPLY
        LEFT JOIN ESTIMATE_SHORT ON ESTIMATE_REPLY.ESTIMATE_REPLY_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID
        LEFT JOIN ESTIMATE_ADDR ON ESTIMATE_ADDR.ESTIMATE_ADDR_FK_SHORT = ESTIMATE_SHORT.ESTIMATE_SHORT_ID WHERE ESTIMATE_REPLY_FK_MOVE = ' . $companyId . ';';

        $query = DB::select( DB::raw( $query ) );

        if(count($query)){

            return response()->json(array('data' => $query));

        } else {
            return response()->json(array('data' => false));
        }
    }

    function checkNull($array, $total, $position)
    {
        if ($total > $position) {
            return $array[$position];
        }
        return "";
    }

    function checkAddress($full, $first, $second)
    {
        $temp = explode(" ", $full);
        if (count($temp) > 1) {
            if ($temp[1] == $second) {
                return true;
            }
        } elseif ($temp[0] == $first) {
            return true;
        }
        return false;
    }

    function checkCompanyType($companyType)
    {
        if ($companyType == "move") {
            return "MV";
        } elseif ($companyType == "clean") {
            return "CL";
        }
    }
}
