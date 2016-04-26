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

class APointProductList extends Controller
{

    public function ProductList(Request $request){
        $query = "SELECT
                    PRODUCT_ID, PRODUCT_CATEGORY, PRODUCT_NAME, PRODUCT_POINT, PRODUCT_AMOUNT, PRODUCT_DISCOUNT, PRODUCT_AREA_COUNT, PRODUCT_REGDATE, PRODUCT_REGMNG, PRODUCT_USEYN, DATE_FORMAT(PRODUCT_STARTDATE, '%Y-%m-%d'),
	                (SELECT ADMIN_R_NAME FROM ADMIN WHERE ADMIN_ID = PRODUCT_REGMNG) AS ADMIN_NAME,
	                CASE PRODUCT_CATEGORY WHEN 0 THEN '요금제' WHEN 1 THEN '추가상품' END AS PRODUCT_CATEGORY_NAME,
	                PRODUCT_FINAL_AMOUNT,
	                (SELECT COUNT(1) FROM MV_LIST WHERE MV_LIST_POINT_PRODUCT = PRODUCT_ID) AS ProductCount
                FROM POINT_PRODUCT WHERE PRODUCT_USEYN = 'Y' ORDER BY PRODUCT_ID";

        $query = DB::select( DB::raw($query));

        return response()->json(array('list' => $query));
    }
}
