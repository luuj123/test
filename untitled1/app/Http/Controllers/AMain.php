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
use App\NOTICE_BOARD_COMPANY;


class AMain extends Controller
{

    public function MainLoading(Request $request)
    {
        $do = $request->do;
        $si = $request->si;
        $MV_DV = $request->MV_DV;

        $form = array();

        if ($MV_DV === "MV") {
            $result['words'] = array();

            $query = 'SELECT SENTENCE_ID, SENTENCE_TEXT1, SENTENCE_TEXT2, SENTENCE_TEXT3, SENTENCE_GUBUN, SENTENCE_REGDATE
                      FROM SENTENCE WHERE SENTENCE_MV_DV="MV" AND SENTENCE_USEYN="Y" ORDER BY rand() limit 1;';
            $query = DB::select(DB::raw($query));

            if (!empty($query)) {

                $form['id'] = $query[0]->SENTENCE_ID;
                $form['text1'] = $query[0]->SENTENCE_TEXT1;

                if ($query[0]->SENTENCE_GUBUN == "0") {
                    $form['text2'] = $query[0]->SENTENCE_TEXT2 . $query[0]->SENTENCE_TEXT3;
                } else {
                    $count = $this->Company_Count($do, $si);

                    if ($count == 0) {
                        $form['text2'] = 0;
                    } else {
                        $form['text2'] = $query[0]->SENTENCE_TEXT2 . " " . $count . $query[0]->SENTENCE_TEXT3;
                    }

                }

                $form['reg_date'] = $query[0]->SENTENCE_REGDATE;

                array_push($result['words'], $form);

                $form2 = array();
                $result['img'] = array();

                $query = 'SELECT EVENT_TAB_IMG_ID, EVENT_TAB_IMG_CHILD, EVENT_TAB_IMG_URL, EVENT_TAB_IMG_EVENT_URL, EVENT_TAB_IMG_REGDATE
                          FROM EVENT_TAB_IMG WHERE EVENT_TAB_IMG_USEYN="Y" ORDER BY EVENT_TAB_IMG_CHILD ASC;';
                $query = DB::select(DB::raw($query));

                if (!empty($query)) {
                    for ($i = 0; $i < count($query); $i++) {

                        $form2['id'] = $query[0]->EVENT_TAB_IMG_ID;
                        $form2['child'] = $query[0]->EVENT_TAB_IMG_CHILD;
                        $form2['img_url'] = $query[0]->EVENT_TAB_IMG_URL;
                        $form2['event_url'] = $query[0]->EVENT_TAB_IMG_EVENT_URL;
                        $form2['reg_date'] = $query[0]->EVENT_TAB_IMG_REGDATE;

                        array_push($result['img'], $form2);

                    }

                    $query = 'select count(ESTIMATE_ADDR_ID) AS COUNT from ESTIMATE_ADDR where (ESTIMATE_ADDR_START like "' . $do . '%" OR ESTIMATE_ADDR_END like "' . $do . '%");';
                    $query = DB::select(DB::raw($query));

                    if (!empty($query)) {
                        $result['order'] = array();
                        $order = $query[0]->COUNT;

                        array_push($result['order'], $order);
                    }
                }

                return response()->json(array('data' => $result));

            } else {
                return false;
            }
        } else {
            $result = array();

            $query = 'SELECT SENTENCE_ID, SENTENCE_TEXT1, SENTENCE_TEXT2, SENTENCE_TEXT3, SENTENCE_GUBUN, SENTENCE_REGDATE
                      FROM SENTENCE WHERE SENTENCE_MV_DV="DV" AND SENTENCE_USEYN="Y";';
            $query = DB::select(DB::raw($query));

            if (!empty($query)) {

                $form['id'] = $query[0]->SENTENCE_ID;
                $form['text1'] = $query[0]->SENTENCE_TEXT1;

                if ($query[0]->SENTENCE_GUBUN == "0") {
                    $form['text2'] = $query[0]->SENTENCE_TEXT2 . $query[0]->SENTENCE_TEXT3;
                } else {
                    $count = $this->Company_Count($do, $si);


                    if ($count == 0) {
                        $form['text2'] = 0;
                    } else {
                        $form['text2'] = $query[0]->SENTENCE_TEXT2 . " " . $count . $query[0]->SENTENCE_TEXT3;
                    }
                }

                $form['reg_date'] = $query[0]->SENTENCE_REGDATE;

                array_push($result, $form);

                return response()->json(array('data' => $result));

            } else {

                return response()->json(array('data' => false));
            }
        }


    }

    function Company_Count($doName, $siName){

//        $query = "select count(ML.MV_LIST_ID)
//                  from MV_LIST AS ML
//                  LEFT JOIN MV_LIST_OTHER_INFO ON ML.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
//                  LEFT OUTER JOIN
//                  (SELECT MV_REGI_FK_LIST, MV_REGI_CHOOSE FROM MV_REGI WHERE (MV_REGI_CHOOSE = 0 or MV_REGI_CHOOSE = 1) GROUP BY MV_REGI_FK_LIST) AS T
//                  ON T.MV_REGI_FK_LIST = ML.MV_LIST_ID
//                  WHERE ML.USEYN = 'Y' AND ML.MV_LIST_SI_NAME='".$doName."' AND ML.MV_LIST_GU_NAME='".$siName."'
//                  AND T.MV_REGI_FK_LIST IS NOT NULL ;";

        $query = "SELECT COUNT(1) AS COUNT
from (
select ML.MV_LIST_ID from MV_LIST AS ML
LEFT OUTER JOIN MV_REGI AS MR ON ML.MV_LIST_ID = MR.MV_REGI_FK_LIST
WHERE MR.MV_REGI_DO = '".$doName."'
AND MR.MV_REGI_SI = '".$siName."'
AND MR.MV_REGI_CHOOSE < 4
AND ML.USEYN = 'Y'
AND ( ML.MV_LIST_TYPE = 0 OR ML.MV_LIST_TYPE = 1 OR ML.MV_LIST_TYPE = 3 )
GROUP BY ML.MV_LIST_ID
ORDER BY ML.MV_LIST_NAME ) AS T;";

        $query = DB::select( DB::raw( $query ) );

        if(count($query)){
            $count = $query[0]->COUNT;

            return $count;
        }
    }

    function GetLocation(Request $request){
        $do = $request->do;
        $si = $request->si;

        $result['status'] = array();
        $result['list'] = array();

        $form = array();

        if(empty($do) && empty($listsi)){
            $query = "select MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_ICON, MV_LIST_PRIVATE_PHONE, ML.MV_LIST_ID
                            ,MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_HOMEPAGE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD, T.MV_REGI_CHOOSE
                  from MV_LIST AS ML
                  LEFT JOIN MV_LIST_OTHER_INFO ON ML.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                  LEFT OUTER JOIN
                  (SELECT MV_REGI_FK_LIST, MV_REGI_CHOOSE FROM MV_REGI WHERE (MV_REGI_CHOOSE = 0 or MV_REGI_CHOOSE = 1) GROUP BY MV_REGI_FK_LIST) AS T
                  ON T.MV_REGI_FK_LIST = ML.MV_LIST_ID
                  WHERE ML.USEYN = 'Y'
                  AND T.MV_REGI_FK_LIST IS NOT NULL ;";

            $query = DB::select( DB::raw($query) );

            if(!empty($query)){
                for($i=0; $i<count($query); $i++){
                    $form['name'] = $query[$i]->MV_LIST_NAME;
                    $form['address'] = $query[$i]->MV_LIST_ADDRESS;
                    $form['latitude'] = $query[$i]->MV_LIST_LATITUDE;
                    $form['longitude'] = $query[$i]->MV_LIST_LONGGITUDE;
                    if(!empty($query[$i]->MV_LIST_ICON)){
                        $form['icon'] = "www.gae8.com/24moa/upload/".$query[$i]->MV_LIST_ICON;
                    } else {
                        $form['icon'] = "";
                    }
                    $form['phone'] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                    $form['id'] = $query[$i]->MV_LIST_ID;
                    $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
                    $form["grade"] = $query[$i]->MV_LIST_GRADE;
                    $form["license"] = $query[$i]->MV_LIST_LICENSE;
                    $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;
                    $form['type'] = $query[$i]->MV_LIST_TYPE;

                    if(count($query[$i]) > 11) {

                        if(!($query[$i]->SS_CARD_PAY == 1)){
                            $form["samsung"] = "0";
                        } else {
                            $form["samsung"] = $query[$i]->SS_CARD_PAY;
                        }

                        if(!($query[$i]->NM_CARD_PAY == 1)){
                            $form["card"] = "0";
                        } else {
                            $form["card"] = $query[$i]->NM_CARD_PAY;
                        }

                        if((!$query[$i]->EVENT_ADD == 1)){
                            $form["event"] = "0";
                        } else {
                            $form["event"] = $query[$i]->EVENT_ADD;
                        }

                    }

                    $form["priority"] = $query[$i]->MV_REGI_CHOOSE;

                    array_push($result['list'], $form);
                }
                array_push($result['status'], "success");

                return response()->json(array('data' => $result));
            }
        } else {
            $query = "select MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_ICON, MV_LIST_PRIVATE_PHONE, ML.MV_LIST_ID, MV_LIST_SI_NAME, MV_LIST_GU_NAME,
            MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_HOMEPAGE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD, T.MV_REGI_CHOOSE
                  from MV_LIST AS ML
                  LEFT JOIN MV_LIST_OTHER_INFO ON ML.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                  LEFT OUTER JOIN
                  (SELECT MV_REGI_FK_LIST, MV_REGI_CHOOSE FROM MV_REGI WHERE (MV_REGI_CHOOSE = 0 or MV_REGI_CHOOSE = 1) GROUP BY MV_REGI_FK_LIST) AS T
                  ON T.MV_REGI_FK_LIST = ML.MV_LIST_ID
                  WHERE ML.USEYN = 'Y' AND ML.MV_LIST_SI_NAME='".$do."' AND ML.MV_LIST_GU_NAME='".$si."'
                  AND T.MV_REGI_FK_LIST IS NOT NULL ;";
            $query = DB::select( DB::raw($query) );

            if(!empty($query)){
                for($i=0; $i<count($query); $i++){
                    $form['name'] = $query[$i]->MV_LIST_NAME;
                    $form['address'] = $query[$i]->MV_LIST_ADDRESS;
                    $form['latitude'] = $query[$i]->MV_LIST_LATITUDE;
                    $form['longitude'] = $query[$i]->MV_LIST_LONGITUDE;
                    if(!empty($query[$i]->MV_LIST_ICON)){
                        $form['icon'] = "www.gae8.com/24moa/upload/".$query[$i]->MV_LIST_ICON;
                    } else {
                        $form['icon'] = "";
                    }
                    $form['phone'] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                    $form['id'] = $query[$i]->MV_LIST_ID;

                    $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
                    $form["grade"] = $query[$i]->MV_LIST_GRADE;
                    $form["license"] = $query[$i]->MV_LIST_LICENSE;
                    $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;
                    $form['type'] = $query[$i]->MV_LIST_TYPE;

                    if(count($query[$i]) > 13) {

                        if(!($query[$i]->SS_CARD_PAY == 1)){
                            $form["samsung"] = "0";
                        } else {
                            $form["samsung"] = $query[$i]->SS_CARD_PAY;
                        }

                        if(!($query[$i]->NM_CARD_PAY == 1)){
                            $form["card"] = "0";
                        } else {
                            $form["card"] = $query[$i]->NM_CARD_PAY;
                        }

                        if((!$query[$i]->EVENT_ADD == 1)){
                            $form["event"] = "0";
                        } else {
                            $form["event"] = $query[$i]->EVENT_ADD;
                        }

                    }

                    $form["priority"] = $query[$i]->MV_REGI_CHOOSE;

                    array_push($result['list'], $form);
                }
                array_push($result['status'], "success");

                return response()->json(array('data' => $result));
            } else {

                $query = "select MV_LIST_NAME, MV_LIST_ADDRESS, MV_LIST_LATITUDE, MV_LIST_LONGITUDE, MV_LIST_ICON, MV_LIST_PRIVATE_PHONE, ML.MV_LIST_ID, MV_LIST_SI_NAME, MV_LIST_GU_NAME,
                MV_LIST_DESCRIPTION, MV_LIST_GRADE, MV_LIST_LICENSE, MV_LIST_HOMEPAGE, MV_LIST_TYPE, SS_CARD_PAY, NM_CARD_PAY, EVENT_ADD, T.MV_REGI_CHOOSE
                  from MV_LIST AS ML
                  LEFT JOIN MV_LIST_OTHER_INFO ON ML.MV_LIST_ID = MV_LIST_OTHER_INFO.MV_LIST_ID
                  LEFT OUTER JOIN
                  (SELECT MV_REGI_FK_LIST, MV_REGI_CHOOSE FROM MV_REGI WHERE (MV_REGI_CHOOSE = 0 or MV_REGI_CHOOSE = 1) GROUP BY MV_REGI_FK_LIST) AS T
                  ON T.MV_REGI_FK_LIST = ML.MV_LIST_ID
                  WHERE ML.USEYN = 'Y' AND ML.MV_LIST_SI_NAME='".$do."'
                  AND T.MV_REGI_FK_LIST IS NOT NULL ;";
                $query = DB::select( DB::raw($query) );

                if(!empty($query)){
                    for($i=0; $i<count($query); $i++){
                        $form['name'] = $query[$i]->MV_LIST_NAME;
                        $form['address'] = $query[$i]->MV_LIST_ADDRESS;
                        $form['latitude'] = $query[$i]->MV_LIST_LATITUDE;
                        $form['longitude'] = $query[$i]->MV_LIST_LONGITUDE;
                        if(!empty($query[$i]->MV_LIST_ICON)){
                            $form['icon'] = "www.gae8.com/24moa/upload/".$query[$i]->MV_LIST_ICON;
                        } else {
                            $form['icon'] = "";
                        }
                        $form['phone'] = $query[$i]->MV_LIST_PRIVATE_PHONE;
                        $form['id'] = $query[$i]->MV_LIST_ID;

                        $form["description"] = $query[$i]->MV_LIST_DESCRIPTION;
                        $form["grade"] = $query[$i]->MV_LIST_GRADE;
                        $form["license"] = $query[$i]->MV_LIST_LICENSE;
                        $form["homepage"] = $query[$i]->MV_LIST_HOMEPAGE;
                        $form['type'] = $query[$i]->MV_LIST_TYPE;

                        if(count($query[$i]) > 13) {

                            if(!($query[$i]->SS_CARD_PAY == 1)){
                                $form["samsung"] = "0";
                            } else {
                                $form["samsung"] = $query[$i]->SS_CARD_PAY;
                            }

                            if(!($query[$i]->NM_CARD_PAY == 1)){
                                $form["card"] = "0";
                            } else {
                                $form["card"] = $query[$i]->NM_CARD_PAY;
                            }

                            if((!$query[$i]->EVENT_ADD == 1)){
                                $form["event"] = "0";
                            } else {
                                $form["event"] = $query[$i]->EVENT_ADD;
                            }

                        }

                        $form["priority"] = $query[$i]->MV_REGI_CHOOSE;

                        array_push($result['list'], $form);
                    }
                    array_push($result['status'], "empty");

                    return response()->json(array('data' => $result));
                } else {

                    array_push($result['status'], "none");

                    return response()->json(array('data' => $result));
                }


            }
        }
    }

    function AppVersionGet(Request $request){
        $query = 'SELECT APP_VERSION_NAME FROM APP_VERSION ORDER BY APP_VERSION_ID DESC LIMIT 1;';
        $query = DB::select( DB::raw( $query ) );

        $data = array();
//        $data["use"] = "true";
        $data["use"] = "false";
        $data["app_ver"] = $query[0]->APP_VERSION_NAME;
        $data["protocol_ver"] = "";
        $data["server_ver"] = "";

        return response()->json(array('data' => $data));
    }

    function Advertisement(Request $request){
        $action = $request->action;

        if($action == "get_new"){
            $folder = "../advertisement/use_new";
            $items = opendir($folder);
            $data = array();
            $link = array();

            $link[0] = "http://m.cafe.naver.com/ddabongzawon.cafe";
            $link[1] = "http://rentaldotcom.com/mobilev2/?appcode=24moa";
            $link[2] = "http://cafe.naver.com/valpoom";
            $link[3] = "https://play.google.com/store/apps/details?id=com.skt.logii.widget";
            $link[4] = "https://play.google.com/store/apps/details?id=kr.co.amusepark.weddingbyme";

            $counter = 0;
            while($file = readdir($items)){
                if($file != '.' && $file != '..' && is_dir($folder."/".$file) != '1'){
                    $form = array();
                    $form['img'] = 'http://www.gae8.com/24moa/advertisement/use_new/'.$file;
                    $form['event'] = $link[$counter];
                    $counter++;
                    array_push($data, $form);
                }
            }
            closedir($items);
            return response()->json(array('data' => $data));
        }
    }

    function Count(){
        $query = 'select sum(count) AS COUNT from
(
SELECT count(1) as count from ESTIMATE_SHORT where date(ESTIMATE_SHORT_REGDATE) = date(now()) AND ESTIMATE_SHORT_PUBLIC = 1
union
SELECT count(1) as count from DV_ESTIMATE_LIST where date(DV_ESTIMATE_LIST_REG_DATE) = date(now()) AND DV_ESTIMATE_LIST_USEYN = "Y"
) AS T;';

        $query = DB::select( DB::raw( $query ) );

        return response()->json(array('data' => $query[0]->COUNT));
    }

    function SiCount(){
        $form = array();
        $result = array();

        $query = 'SELECT AREA_NAME, LATITUDE, LONGITUDE, HOME_COUNT, ONE_COUNT, OFFICE_COUNT, KEEP_COUNT, DELIVERY_COUNT FROM AREA_GEO_COUNT WHERE ( LENGTH(AREA_NAME) - LENGTH(REPLACE(AREA_NAME, " ", "")) ) = 0;';
        $query = DB::select( DB::raw( $query ) );

        if(count($query)){
            for($i=0; $i<count($query); $i++){
                $form['area_name'] = $query[$i]->AREA_NAME;
                $form['latitude'] = $query[$i]->LATITUDE;
                $form['longitude'] = $query[$i]->LONGITUDE;
                $form['home_count'] = $query[$i]->HOME_COUNT;
                $form['one_count'] = $query[$i]->ONE_COUNT;
                $form['office_count'] = $query[$i]->OFFICE_COUNT;
                $form['keep_count'] = $query[$i]->KEEP_COUNT;
                $form['delivery_count'] = $query[$i]->DELIVERY_COUNT;
                $form['full_count'] = (string)($query[$i]->HOME_COUNT+$query[$i]->ONE_COUNT+$query[$i]->OFFICE_COUNT+$query[$i]->KEEP_COUNT+$query[$i]->DELIVERY_COUNT);

                array_push($result, $form);
            }

            return response()->json(array('data' => $result));
        } else {
            return response()->json(array('data' => false));
        }
    }

    function GuCount(){
        $form = array();
        $result = array();

        $query = 'SELECT AREA_NAME, LATITUDE, LONGITUDE, HOME_COUNT, ONE_COUNT, OFFICE_COUNT, KEEP_COUNT, DELIVERY_COUNT FROM AREA_GEO_COUNT WHERE ( LENGTH(AREA_NAME) - LENGTH(REPLACE(AREA_NAME, " ", "")) ) > 0;';
        $query = DB::select( DB::raw( $query ) );

        if(count($query)){
            for($i=0; $i<count($query); $i++){
                $form['area_name'] = $query[$i]->AREA_NAME;
                $form['latitude'] = $query[$i]->LATITUDE;
                $form['longitude'] = $query[$i]->LONGITUDE;
                $form['home_count'] = $query[$i]->HOME_COUNT;
                $form['one_count'] = $query[$i]->ONE_COUNT;
                $form['office_count'] = $query[$i]->OFFICE_COUNT;
                $form['keep_count'] = $query[$i]->KEEP_COUNT;
                $form['delivery_count'] = $query[$i]->DELIVERY_COUNT;
                $form['full_count'] = (string)($query[$i]->HOME_COUNT+$query[$i]->ONE_COUNT+$query[$i]->OFFICE_COUNT+$query[$i]->KEEP_COUNT+$query[$i]->DELIVERY_COUNT);

                array_push($result, $form);
            }

            return response()->json(array('data' => $result));
        } else {
            return response()->json(array('data' => false));
        }
    }

    function test(){
        $query = ESTIMATE_SHORT::leftJoin('ESTIMATE_LIST', 'ESTIMATE_LIST.ESTIMATE_LIST_ID', '=', 'ESTIMATE_SHORT.ESTIMATE_SHORT_FK_LIST')
            ->selectRaw('ESTIMATE_SHORT_REPLIES, ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_ID')
            ->where('ESTIMATE_SHORT_ID', '=', '36764')
            ->where('ESTIMATE_SHORT_MAX_REPLIES', '>', 'ESTIMATE_SHORT_REPLIES')->get();

        //var_dump(count($query));

        $replies = $query[0]->ESTIMATE_SHORT_REPLIES;

        var_dump($replies);

        if(isset($replies)){
            echo"dd";
        }else{
            echo "ww";
        }

        if(empty($replies)){
            echo"dd";
        }else{
            echo "ww";
        }
    }

}
