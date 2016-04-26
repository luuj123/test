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
use App\ESTIMATE_LIST;
use App\ESTIMATE_SHORT;
use App\ESTIMATE_CALLED;
use App\MV_NEW_COMPANY;
use App\DV_ESTIMATE_LIST;
use App\DV_ESTIMATE_CALLED;
use App\MV_POINT;

class AEstimateCalling extends Controller
{

    public function MovingCallingDown(Request $request)
    {

        $companyId = $request->companyId;
        $shortId = $request->shortId;

        $query = 'SELECT USEYN FROM MV_LIST WHERE MV_LIST_ID='.$companyId;
        $query = DB::select( DB::raw( $query ) );

        if(empty($query)){
            return response()->json(array('data' => false));
        } else {
            if($query[0]->USEYN == "N"){
                return response()->json(array('data' => false));
            }
        }

        DB::beginTransaction();

        $query = 'SELECT ESTIMATE_CLICK_ID FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
        $query = DB::select( DB::raw( $query ) );

        if(!empty($query)){
            $query = 'UPDATE ESTIMATE_CLICK SET ESTIMATE_CLICK_BIDDING_CALL=1, ESTIMATE_CLICK_BIDDING_CALL_DATE=now() WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$shortId.' AND ESTIMATE_CLICK_MV_DV="MV";';
            $query = DB::statement( DB::raw( $query ) );
        }


        $query = ESTIMATE_SHORT::leftJoin('ESTIMATE_LIST', 'ESTIMATE_LIST.ESTIMATE_LIST_ID', '=', 'ESTIMATE_SHORT.ESTIMATE_SHORT_FK_LIST')
            ->selectRaw('ESTIMATE_SHORT_REPLIES, ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_ID, ESTIMATE_SHORT_GRADE')
            ->where('ESTIMATE_SHORT_ID', '=', $shortId)
            ->where('ESTIMATE_SHORT_MAX_REPLIES', '>', 'ESTIMATE_SHORT_REPLIES')->get();

//        return response()->json(array('data' => $query));



        $query = 'SELECT ESTIMATE_SHORT_REPLIES, ESTIMATE_LIST_FOLDER, ESTIMATE_LIST_ID, ESTIMATE_SHORT_GRADE
                  FROM ESTIMATE_SHORT
                  LEFT JOIN ESTIMATE_LIST ON ESTIMATE_LIST_ID=ESTIMATE_SHORT_FK_LIST
                  WHERE ESTIMATE_SHORT_ID='.$shortId.' AND ESTIMATE_SHORT_MAX_REPLIES > ESTIMATE_SHORT_REPLIES;';
        $query = DB::select( DB::raw( $query ) );

        if(empty($query)){
            return response()->json(array('data' => false));
        }

        $replies = $query[0]->ESTIMATE_SHORT_REPLIES;
        $grade = $query[0]->ESTIMATE_SHORT_GRADE;

        $phone = explode("/", $query[0]->ESTIMATE_LIST_FOLDER)[0];
        $phone = (substr($phone, 0, 1) == '+') ? '0' . substr($phone, 3) : $phone;
        $estimateId = $query[0]->ESTIMATE_LIST_ID;

        if (isset($replies)) {
            $query = ESTIMATE_CALLED::selectRaw('COUNT(ESTIMATE_CALLED_ID) AS CALL_COUNT')
                ->where('ESTIMATE_CALLED_FK_SHORT', '=', $shortId)
                ->where('ESTIMATE_CALLED_FK_LIST', '=', $companyId)->get();

            if ($query[0]->CALL_COUNT == 0) {
                $query = 'INSERT INTO ESTIMATE_CALLED VALUES (NULL, "' . $companyId . '", ' . $shortId . ', NOW());';
                $query = DB::statement(DB::raw($query));

                $query = 'SELECT ESTIMATE_SHORT_MAX_REPLIES, ESTIMATE_SHORT_KIND FROM ESTIMATE_SHORT WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
                $query = DB::select(DB::raw($query));

                $maxReplies = $query[0]->ESTIMATE_SHORT_MAX_REPLIES;
                $kind = $query[0]->ESTIMATE_SHORT_KIND;



                $query = "SELECT MV_LIST_POINT_PRODUCT, MV_LIST_GRADE
                                  FROM MV_LIST WHERE MV_LIST_ID='$companyId'";
                $query = DB::select( DB::raw($query) );

                if($query[0]->MV_LIST_POINT_PRODUCT > 15){

                    if($query[0]->MV_LIST_GRADE >= $grade){
                        $query = 'INSERT INTO MV_HISTORY VALUES (NULL, NOW(), 0, "' . $phone . '", ' . $companyId . ', ' . $estimateId . ', ' . $shortId . ');';
                        $query = DB::statement(DB::raw($query));

                        $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES (' . $companyId . ', "PO_OUT01", 0, 0, "' . $phone . '/' . $estimateId . '/' . $shortId . '", "MV", NOW(), 0);';
                        $query = DB::statement(DB::raw($query));

                        $query = 'UPDATE ESTIMATE_SHORT SET ESTIMATE_SHORT_REPLIES = ' . ($replies + 1) . ' WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
                        $query = DB::statement(DB::raw($query));
                    } else {

                    }

                } else {
                    $query = 'SELECT MV_POINT_FREE, MV_POINT_CASH FROM MV_POINT WHERE MV_POINT_FK_MOVE = ' . $companyId . ' AND (MV_POINT_FREE + MV_POINT_CASH) > 0;';
                    $query = DB::select(DB::raw($query));

//                $query = MV_POINT::selectRaw('MV_POINT_FREE, MV_POINT_CASH, (MV_POINT_FREE+MV_POINT_CASH) AS ALL')
//                    ->where('MV_POINT_FK_MOVE', '=', $companyId)
//                    ->where('ALL', '>', '0')->get();
//                    ->orwhere('MV_POINT_CASH', '>', '0')->get();

                    $queries = DB::getQueryLog();
//                var_dump($queries);

//                return response()->json(array('data' => $query));

                    $free = $query[0]->MV_POINT_FREE;
                    $cash = $query[0]->MV_POINT_CASH;
                    $freeNew = $query[0]->MV_POINT_FREE;
                    $cashNew = $query[0]->MV_POINT_CASH;

                    if ($kind == 5) {
                        $free -= 5;
                        $dePoint = 5;

                        if (!$this->checkFreeAndCashPoint($free, 0)) {
                            $cash += $free;
                            if (!$this->checkFreeAndCashPoint($cash, 0)) {
                                DB::rollback();
                                return response()->json(array('data' => false));
                            } else {
                                $free = 0;
                            }
                        }
                    } else {
                        switch ($maxReplies) {
                            case 1:
                                $free -= 15;
                                $dePoint = 15;
                                if (!$this->checkFreeAndCashPoint($free, 0)) {
                                    $cash += $free;
                                    if (!$this->checkFreeAndCashPoint($cash, 0)) {
                                        DB::rollback();
                                        return response()->json(array('data' => false));
                                    } else {
                                        $free = 0;
                                    }
                                }
                                break;
                            case 2:
                                $free -= 11;
                                $dePoint = 11;
                                if (!$this->checkFreeAndCashPoint($free, 0)) {
                                    $cash += $free;
                                    if (!$this->checkFreeAndCashPoint($cash, 0)) {
                                        DB::rollback();
                                        return response()->json(array('data' => false));
                                    } else {
                                        $free = 0;
                                    }
                                }
                                break;
                            case 3:
                                $free -= 9;
                                $dePoint = 9;
                                if (!$this->checkFreeAndCashPoint($free, 0)) {
                                    $cash += $free;
                                    if (!$this->checkFreeAndCashPoint($cash, 0)) {
                                        DB::rollback();
                                        return response()->json(array('data' => false));
                                    } else {
                                        $free = 0;
                                    }
                                }
                                break;
                            case 5:
                                $free -= 3;
                                $dePoint = 3;
                                if (!$this->checkFreeAndCashPoint($free, 0)) {
                                    $cash += $free;
                                    if (!$this->checkFreeAndCashPoint($cash, 0)) {
                                        DB::rollback();
                                        return response()->json(array('data' => false));
                                    } else {
                                        $free = 0;
                                    }
                                }
                        }
                    }

                    $query = 'INSERT INTO MV_HISTORY VALUES (NULL, NOW(), ' . $dePoint . ', "' . $phone . '", ' . $companyId . ', ' . $estimateId . ', ' . $shortId . ');';
                    $query = DB::statement(DB::raw($query));

                    $query = 'INSERT INTO POINT_TOTAL (UserID,Category,outPoint,Money,Content,MV_CL,RegDate,MngCode) VALUES (' . $companyId . ', "PO_OUT01", ' . $dePoint . ', 0, "' . $phone . '/' . $estimateId . '/' . $shortId . '", "MV", NOW(), 0);';
                    $query = DB::statement(DB::raw($query));

                    $query = 'UPDATE ESTIMATE_SHORT SET ESTIMATE_SHORT_REPLIES = ' . ($replies + 1) . ' WHERE ESTIMATE_SHORT_ID = ' . $shortId . ';';
                    $query = DB::statement(DB::raw($query));


                    $query = 'UPDATE MV_POINT SET MV_POINT_FREE = ' . $free . ', MV_POINT_CASH = ' . $cash . ' WHERE MV_POINT_FK_MOVE = ' . $companyId . ';';
                    $query = DB::statement(DB::raw($query));
                }

                DB::commit();

                return response()->json(array('data' => true));
            } else {
                DB::commit();

                return response()->json(array('data' => true));
            }

        }

        DB::rollback();
        return response()->json(array('data' => false));
    }

    function checkFreeAndCashPoint($value, $isNewCompany)
    {
        if ($value < 0 && $isNewCompany == 0) {
            return false;
        }
        return true;
    }

    public function DeliverCallingDown(Request $request)
    {
        $companyId = $request->companyId;
        $listId = $request->listId;


        DB::beginTransaction();

        $query = 'SELECT ESTIMATE_CLICK_ID FROM ESTIMATE_CLICK WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$listId.' AND ESTIMATE_CLICK_MV_DV="DV";';
        $query = DB::select( DB::raw( $query ) );

        if(!empty($query)){
            $query = 'UPDATE ESTIMATE_CLICK SET ESTIMATE_CLICK_BIDDING_CALL=1, ESTIMATE_CLICK_BIDDING_CALL_DATE=now() WHERE ESTIMATE_CLICK_FK_COMPANY='.$companyId.' AND ESTIMATE_CLICK_FK_SHORT='.$listId.' AND ESTIMATE_CLICK_MV_DV="DV";';
            $query = DB::statement( DB::raw( $query ) );
        }

        $query = 'SELECT DV_ESTIMATE_LIST_CALL, (SELECT USER_DEVICE_REGISTERS_PHONE FROM USER_DEVICE_REGISTERS WHERE USER_DEVICE_REGISTERS_ID=DV_ESTIMATE_LIST_FK_USER ORDER BY USER_DEVICE_REGISTERS_ID desc limit 1) AS PHONE FROM DV_ESTIMATE_LIST WHERE DV_ESTIMATE_LIST_ID=' . $listId . ';';
        $query = DB::select(DB::raw($query));

        $phone = $query[0]->PHONE;

        if ($query[0]->DV_ESTIMATE_LIST_CALL > 2) {
            DB::rollback();

            return response()->json(array('data' => false));
        }

        $query = 'SELECT count(DV_ESTIMATE_CALLED_ID) AS COUNT FROM DV_ESTIMATE_CALLED WHERE DV_ESTIMATE_CALLED_FK_MV_LIST="' . $companyId . '" AND DV_ESTIMATE_CALLED_FK_LIST="' . $listId . '";';
        $query = DB::select(DB::raw($query));

//        $query = DV_ESTIMATE_CALLED::selectRaw('count(DV_ESTIMATE_CALLED_ID) AS COUNT')
//            ->where('DV_ESTIMATE_CALLED_FK_MV_LIST', '=', $companyId)
//            ->where('DV_ESTIMATE_CALLED_FK_LIST', '=', $listId)->get();


        $count = $query[0]->COUNT;


        if ($count > 0) {

            $query = 'INSERT INTO DV_HISTORY(DV_HISTORY_ID, DV_HISTORY_TIME, DV_HISTORY_POINT, DV_HISTORY_PHONE, DV_HISTORY_FK_LIST, DV_HISTORY_FK_MV_LIST)
                          VALUES(null, NOW(), 0, "' . $phone . '", "' . $listId . '", "' . $companyId . '");';
//                $query = 'INSERT INTO DV_ESTIMATE_CALLED(DV_ESTIMATE_CALLED_ID, DV_ESTIMATE_CALLED_FK_LIST, DV_ESTIMATE_CALLED_FK_MV_LIST, DV_ESTIMATE_CALLED_REG_DATE)
//                      VALUES(null, "'.$list['list_id'].'", "'.$list['user_id'].'", NOW());';
            $query = DB::statement(DB::raw($query));


            if ($query) {
                DB::commit();
                return response()->json(array('data' => true));
            } else {
                DB::rollback();
                return response()->json(array('data' => false));
            }


            if (mysql_affected_rows() > 0) {
                $mySql->commit();
                $mySql->close();
                return true;
            } else {
                DB::rollback();

                return response()->json(array('data' => false));
            }
        } else {

            $query = "SELECT MV_LIST_POINT_PRODUCT
                                  FROM MV_LIST WHERE MV_LIST_ID='$companyId'";
            $query = DB::select( DB::raw($query) );

            if($query[0]->MV_LIST_POINT_PRODUCT > 15){
                $query = 'INSERT INTO POINT_TOTAL(ID, UserID, Category, outPoint, Content, MV_CL, RegDate, MngCode, Money)
                          VALUES(null, "' . $companyId . '", "PO_OUT01", 0, "' . $phone . '/' . $listId. '/DV", "DV", NOW(), 0, 0);';
                $query = DB::statement(DB::raw($query));

                if ($query) {
                    $query = 'INSERT INTO DV_ESTIMATE_CALLED(DV_ESTIMATE_CALLED_ID, DV_ESTIMATE_CALLED_FK_LIST, DV_ESTIMATE_CALLED_FK_MV_LIST, DV_ESTIMATE_CALLED_REG_DATE)
                          VALUES(null, "' . $listId . '", "' . $companyId . '", NOW());';
                    $query = DB::statement(DB::raw($query));

                    if ($query) {

                        $query = 'INSERT INTO DV_HISTORY(DV_HISTORY_ID, DV_HISTORY_TIME, DV_HISTORY_POINT, DV_HISTORY_PHONE, DV_HISTORY_FK_LIST, DV_HISTORY_FK_MV_LIST)
                                      VALUES(null, NOW(), 0, "' . $phone . '", "' . $listId . '", "' . $companyId . '");';
                        $query = DB::statement(DB::raw($query));

                        if ($query) {
                            $query = 'UPDATE DV_ESTIMATE_LIST SET DV_ESTIMATE_LIST_CALL=(select * FROM(SELECT DV_ESTIMATE_LIST_CALL+1 FROM DV_ESTIMATE_LIST WHERE DV_ESTIMATE_LIST_ID=' . $listId . ') AS T) WHERE DV_ESTIMATE_LIST_ID=' . $listId . ';';
                            $query = DB::statement(DB::raw($query));

                            if ($query) {

                                DB::commit();
                                return response()->json(array('data' => true));
                            } else {
                                DB::rollback();

                                return response()->json(array('data' => 'dv_list_call_update_fail'));
                            }
                        } else {
                            DB::rollback();

                            return response()->json(array('data' => 'dv_history_insert_fail'));
                        }

                    } else {
                        DB::rollback();

                        return response()->json(array('data' => 'dv_call_insert_fail'));
                    }
                } else {
                    DB::rollback();

                    return response()->json(array('data' => 'point_total_insert_fail'));
                }
            } else {
                $query = 'SELECT MV_POINT_FREE, MV_POINT_CASH FROM MV_POINT WHERE MV_POINT_FK_MOVE="' . $companyId . '";';
                $query = DB::select(DB::raw($query));

                if(count($query)){
                    DB::rollback();
                    return response()->json(array('data' => 'point_find_fail'));
                } else {
                    $free_point = $query[0]->MV_POINT_FREE;
                    $cash_point = $query[0]->MV_POINT_CASH;
                }




                if ($free_point > 5) {
                    $free_point -= 5;
                } else {
                    $reduce = 5 - $free_point;
                    $free_point = 0;
                    $cash_point -= $reduce;

                    if ($cash_point < 0) {
                        DB::rollback();

                        return response()->json(array('data' => 'point_lack'));
                    }
                }

                $query = 'UPDATE MV_POINT SET MV_POINT_FREE=' . $free_point . ', MV_POINT_CASH=' . $cash_point . ' WHERE MV_POINT_FK_MOVE=' . $companyId . ';';
                $query = DB::statement(DB::raw($query));

                if ($query) {
                    $query = 'INSERT INTO POINT_TOTAL(ID, UserID, Category, outPoint, Content, MV_CL, RegDate, MngCode, Money)
                          VALUES(null, "' . $companyId . '", "PO_OUT01", 5, "' . $phone . '/' . $listId. '/DV", "DV", NOW(), 0, 0);';
                    $query = DB::statement(DB::raw($query));

                    if ($query) {
                        $query = 'INSERT INTO DV_ESTIMATE_CALLED(DV_ESTIMATE_CALLED_ID, DV_ESTIMATE_CALLED_FK_LIST, DV_ESTIMATE_CALLED_FK_MV_LIST, DV_ESTIMATE_CALLED_REG_DATE)
                          VALUES(null, "' . $listId . '", "' . $companyId . '", NOW());';
                        $query = DB::statement(DB::raw($query));

                        if ($query) {

                            $query = 'INSERT INTO DV_HISTORY(DV_HISTORY_ID, DV_HISTORY_TIME, DV_HISTORY_POINT, DV_HISTORY_PHONE, DV_HISTORY_FK_LIST, DV_HISTORY_FK_MV_LIST)
                                      VALUES(null, NOW(), 5, "' . $phone . '", "' . $listId . '", "' . $companyId . '");';
                            $query = DB::statement(DB::raw($query));

                            if ($query) {
                                $query = 'UPDATE DV_ESTIMATE_LIST SET DV_ESTIMATE_LIST_CALL=(select * FROM(SELECT DV_ESTIMATE_LIST_CALL+1 FROM DV_ESTIMATE_LIST WHERE DV_ESTIMATE_LIST_ID=' . $listId . ') AS T) WHERE DV_ESTIMATE_LIST_ID=' . $listId . ';';
                                $query = DB::statement(DB::raw($query));

                                if ($query) {

                                    DB::commit();
                                    return response()->json(array('data' => true));
                                } else {
                                    DB::rollback();

                                    return response()->json(array('data' => 'dv_list_call_update_fail'));
                                }
                            } else {
                                DB::rollback();

                                return response()->json(array('data' => 'dv_history_insert_fail'));
                            }

                        } else {
                            DB::rollback();

                            return response()->json(array('data' => 'dv_call_insert_fail'));
                        }
                    } else {
                        DB::rollback();

                        return response()->json(array('data' => 'point_total_insert_fail'));
                    }
                } else {
                    DB::rollback();

                    return response()->json(array('data' => 'point_update_fail'));
                }
            }


        }

    }
}
