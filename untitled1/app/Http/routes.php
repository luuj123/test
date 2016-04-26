<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('login', 'LoginController@login');



//login2/test

//Route::get('login', function(){

//});

/*
Route::get('/', function () {
    return "111";
    return view('welcome');
});
*/
Route::get('EstimateList1', 'AMovingEstimateList@EstimateList1');
Route::get('EstimateList2', 'AMovingEstimateList@EstimateList2');


Route::group(['prefix'=>'v1'], function(){
    Route::group(['prefix'=>'App'], function(){
        Route::group(['prefix'=>'Company'], function(){
            Route::post('CompanyPoint', 'ACompany@CompanyPoint'); // regacy 업체 결제정보
            Route::post('DownPointList', 'ACompany@readPointDownContent'); // regacy 포인트 사용내역
            Route::post('CompanyVersion', 'ACompany@version'); // regacy 업체 버전정보
            Route::post('CompanyApprove', 'ACompany@CompanyApprove'); // new 업체 가입신청
            Route::post('UserRegisterSet', 'AUser@RegisterSet'); // regacy
            Route::post('UserRegisterGet', 'AUser@RegisterGet'); // regacy
            Route::post('AssignDate', 'ACompany@Assign_date'); // new 업체 날짜선택
            Route::post('AssignDateReturn', 'ACompany@Assign_date_return'); // new 업체 날짜리턴
            Route::post('GradeCheckMoving', 'AMovingEstimateDetail@GradeCheck'); // new 이사업체 등급 체크
            Route::post('GradeCheckDeliver', 'ADeliver@GradeCheck'); // new 용달업체 등급 체크
            Route::post('EstimateClick', 'AMovingEstimateList@EstimateClick'); // new 이사업체 견적 조회 여부
            Route::post('MovingEstimateList', 'AMovingEstimateList@EstimateList'); // new 이사 내지역 견적리스트
            Route::post('MovingEstimateSearchList', 'AMovingEstimateList@SearchList'); // new 이사 전체지역 검색 견적리스트
            Route::post('MovingEstimateDetail', 'AMovingEstimateDetail@EstimateList'); // new 이사 견적 상세페이지
            Route::post('MovingEstimateCalling', 'AEstimateCalling@MovingCallingDown'); // regacy 이사 입찰 콜
            Route::post('MovingEstimateReplyInsert', 'AEstimateReply@insertReply'); // regacy 이사 견적 댓글 입찰
            Route::post('MovingEstimateReplyUpdate', 'AEstimateReply@updateReply'); // regacy 이사 견적 댓글 수정
            Route::post('MovingEstimateReplyDelete', 'AEstimateReply@deleteReply'); // regacy 이사 견적 댓글 제거
            Route::post('MovingEstimateReplyRead', 'AEstimateReply@readReply'); // regacy 이사 견적 댓글 목록
            Route::post('DeliverCompanyEstimateSearchList', 'ADeliver@SearchList'); // new 용달 전체지역 검색 견적리스트
            Route::post('DeliverCompanyEstimateList', 'ADeliver@EstimateList'); // new 용달 내지역 견적리스트
            Route::post('DeliverEstimateDetail', 'ADeliver@EstimateDetail'); // new 용달 디테일
            Route::post('DeliverEstimateCalling', 'AEstimateCalling@DeliverCallingDown'); // regacy 용달 입찰 콜
            Route::post('CodeArea', 'ACodeArea@CodeArea'); // regacy 주소 코드
            Route::post('QnAList', 'ACompany@QNA'); // new 업체 QnA
            Route::post('MainCount', 'AMain@Count'); // regacy 오늘 이사 견적 수
            Route::post('MovingCompanyList', 'ACompany@MovingCompanyList'); // regacy 집주변 업체리스트
            Route::post('MovingCompanyReplyList', 'ACompany@NewMovingCompanyReplyList'); // new 업체가 입찰한 견적리스트
        });

        Route::group(['prefix'=>'Normal'], function(){
            Route::post('MovingEstimateInsert', 'AMovingEstimateList@EstimateInsert'); // regacy 이사 견적등록
            Route::post('DeliverEstimateInsert', 'ADeliver@EstimateInsert'); // regacy 용달 견적등록
            Route::post('CleanEstimateInsert', 'ACLEstimateList@CLEstimateInsert'); // regacy 청소 견적등록
            Route::post('CleanEstimateUpdate', 'ACLEstimateList@CLEstimateUpdate'); // regacy 청소 견적수정
            Route::post('UserRegisterSet', 'AUser@RegisterSet'); // regacy 유저 정보등록
            Route::post('UserRegisterGet', 'AUser@RegisterGet'); // regacy 유저 정보
            Route::post('MovingCompanyList', 'ACompany@MovingCompanyList'); // regacy 내 집주변 이사업체 리스트
            Route::post('MovingAroundCall', 'AUser@UserCall'); // regacy 집주변 업체 전화
            Route::post('UserMyEstimate', 'AMovingEstimateList@UserMyEstimate'); // regacy 우리집 정보 / 내가 올린 견적목록
            Route::post('MVCompanyCallToUser', 'AMovingEstimateList@MVCompanyCallToUser'); // regacy 나에게 전화한 업체목록
            Route::post('NoticeBoardInformation', 'ABoardController@NoticeBoardInformation'); // regacy 이사 꿀팁
            Route::post('TonginEvent', 'AEvent@TonginEvent'); // regacy 통인 이벤트 견적등록
            Route::post('EndDialog', 'AEvent@TonginEvent'); // regacy 통인 이벤트 여부 및 문구
            Route::post('CeoCheck', 'ACompany@CeoCheck'); // regacy 업체 유무 체크
            Route::post('UserAppVersionVisitChk', 'AUser@UserAppVersionVisitChk'); // regacy 앱버전 및 안드로이드 SDK 버전정보 등록
            Route::post('MainLoading', 'AMain@MainLoading'); // regacy 메인페이지 정보
            Route::post('LocationEstimateCountSi', 'AMain@SiCount');
            Route::post('LocationEstimateCountGu', 'AMain@GuCount');
            Route::post('DeliverCompanyList', 'ACompany@DeliverCompanyList'); // regacy 용달 업체리스트
            Route::post('DeliverUserCall', 'ADeliver@user_call'); // regacy 용달 집주변 업체 전화
            Route::post('Advertisement', 'AEvent@Advertisement'); // regacy
            Route::post('MovingEstimateUpdate', 'AMovingEstimateList@EstimateUpdate'); // regacy 이사 견적수정
            Route::post('CleanEstimateModify', 'ACLEstimateList@CLEstimateModify'); // regacy 청소 견적 신청했던



//            Route::post('UserRegisterUpdate', 'AUser@RegisterUpdate'); // regacy
//            Route::post('AppVersionGet', 'AMain@AppVersionGet'); // regacy
//            Route::post('AlarmSetting', 'ASettings@alarm'); // regacy
//            Route::post('GetLocation', 'AMain@GetLocation'); // regacy
//            Route::post('movingDataNew', 'ACompany@AroundCompany'); // regacy
//
//            Route::post('minItems', 'AMovingEstimateList@EstimateRead'); // regacy
//
//            Route::post('AppVersion', 'ACompany@version'); // regacy
        });

        Route::group(['prefix'=>'OldCompany'], function(){
            Route::post('MovingEstimateCalling', 'AEstimateCalling@MovingCallingDown'); // regacy 이사 견적 입찰
            Route::post('checkPointContent', 'ACompany@checkPointContent'); // regacy 무제한 요금제 / 포인트 충분한지
            Route::post('CeoStateChange', 'ACompany@CeoStateChange'); // regacy 사장님 모드 / 사장님 앱 어떤 걸 사용할지
            Route::post('CompanyPoint', 'ACompany@CompanyPoint'); // regacy 결제 및 가입정보
            Route::post('CompanyPointUpdate', 'ACompany@CompanyPointUpdate'); // regacy 업체 포인트 충전
            Route::post('DownPointList', 'ACompany@readPointDownContent'); // regacy 업체 상담내역
            Route::post('DeliverCompanyMyEstimate', 'ADeliver@company_estimate'); // regacy 용달 입찰 견적리스트
            Route::post('EstimateDetailForLatest', 'AMovingEstimateList@EstimateDetailForLatest'); // regacy 이사 견적 디테일
            Route::post('MovingEstimateReplyInsert', 'AEstimateReply@insertReply'); // regacy 이사 견적 댓글 입찰
            Route::post('MovingEstimateReplyUpdate', 'AEstimateReply@updateReply'); // regacy 이사 견적 댓글 수정
            Route::post('MovingEstimateReplyDelete', 'AEstimateReply@deleteReply'); // regacy 이사 견적 댓글 제거
            Route::post('MovingEstimateReplyPushUser', 'AEstimateReply@PushUser'); // regacy 댓글 입찰 시 사용자에게 푸시
            Route::post('DeliverEstimateCalling', 'AEstimateCalling@DeliverCallingDown'); // regacy 용달 견적 입찰
            Route::post('MovingCompanyReplyList', 'ACompany@MovingCompanyReplyList'); // regacy 업체가 입찰한 견적리스트
        });



//        Route::post('DeliverUserMyEstimate', 'ADeliver@User_my_estimate'); // regacy
//        Route::post('DeliverCompanyOldEstimateList', 'ADeliver@company_estimate_list'); // regacy (new 인 SearchList 써야함)
//        Route::post('DeliverUserEstimateList', 'ADeliver@user_estimate_list'); // regacy
//        Route::post('DeliverNearByCompany', 'ACompany@nearbycompany'); // regacy
//        Route::post('PointProductList', 'ACompany@ProductList'); // regacy
//        Route::post('CompanyInfo', 'ACompany@companyInfo'); // regacy
//        Route::post('AlarmSetting', 'ASettings@alarm'); // regacy
//        Route::post('MovingEstimateReplyInsert', 'AEstimateReply@insertReply'); // regacy
//        Route::post('MovingEstimateReplyUpdate', 'AEstimateReply@updateReply'); // regacy
//        Route::post('MovingEstimateReplyDelete', 'AEstimateReply@deleteReply'); // regacy
//        Route::post('MovingEstimateReplyRead', 'AEstimateReply@readReply'); // regacy
//        Route::post('CodeArea', 'ACodeArea@CodeArea'); // regacy
//        Route::post('UserRegisterSet', 'AUser@RegisterSet'); // regacy
//        Route::post('UserRegisterGet', 'AUser@RegisterGet'); // regacy
//        Route::post('UserRegisterUpdate', 'AUser@RegisterUpdate'); // regacy
//        Route::post('AppVersionGet', 'AMain@AppVersionGet'); // regacy



        Route::post('MovingCompanyList', 'ACompany@MovingCompanyList'); // regacy 우철이 때문에 지우면 안됨

		//SH - 2016.02.25
		Route::group(['prefix'=>'OldCeoEstimate'], function(){ //기존 앱 사장님 모드 견적 리스트
			Route::any('MoveFullList', 'AOldCeoEstimate@MoveFullList'); //이사견적 전체 리스트
			Route::any('MoveMyList', 'AOldCeoEstimate@MoveMyList'); //이사견적 내지역 견적 리스트
			Route::any('NoTenderMoveList', 'AOldCeoEstimate@NoTenderMoveList'); //이사 미입찰 리스트

			Route::any('DeliveryFullList', 'AOldCeoEstimate@DeliveryFullList'); //용달견적 전체 리스트
			Route::any('DeliveryMyList', 'AOldCeoEstimate@DeliveryMyList'); //용달견적 전체 리스트
			Route::any('NoTenderDeliveryList', 'AOldCeoEstimate@NoTenderDeliveryList'); //용달 미입찰 리스트

			Route::any('DeliveryFullList2', 'AOldCeoEstimate@DeliveryFullList2'); //용달견적 전체 리스트
			Route::any('DeliveryMyList2', 'AOldCeoEstimate@DeliveryMyList2'); //용달견적 전체 리스트
			Route::any('NoTenderDeliveryList2', 'AOldCeoEstimate@NoTenderDeliveryList2'); //용달 미입찰 리스트
		});

		//SH - 2016.02.29
		Route::group(['prefix'=>'CombineEstimate'], function(){ //앱-웹 통합 견적등록
			Route::any('AllEstimateInsert', 'ACombineEstimate@AllEstimateInsert'); //견적 등록
		});
    });
    Route::group(['prefix'=>'WEB'], function(){
        Route::any('SetEstimate', 'WEstimate@SetEstimate'); //IOS 웹 이사 견적 등록
        Route::any('SetDelivery', 'WEstimate@SetDelivery'); //IOS 웹 이사 견적 등록
        Route::any('SetClean', 'WEstimate@SetClean'); //IOS 웹 청소 견적 등록
    });
});

//SH - ios version mobile web
Route::group(['prefix'=>'IOS'], function(){
    Route::group(['prefix'=>'Main'], function(){
        Route::any('View', 'OMain@View'); //메인 뷰
        Route::any('GetAddress', 'OMain@GetAddress'); //메인 뷰
        Route::any('GetFullCount', 'OMain@GetFullCount'); //메인 카운트
    });
    Route::group(['prefix'=>'Estimate'], function(){
        Route::any('View', 'OEstimate@View'); //견적 리스트 뷰
        Route::any('Search', 'OEstimate@Search'); //견적 리스트 검색
        Route::any('/', 'OEstimate@Estimate'); //견적 리스트 검색

        Route::any('Delivery', 'OEstimate@Delivery'); //용달 견적 리스트 검색
        Route::any('Clean', 'OEstimate@Clean'); //청소 견적 리스트 검색
    });
    Route::group(['prefix'=>'Company'], function(){
        Route::any('View', 'OCompany@View'); //업체 리스트 뷰
        Route::any('Search', 'OCompany@Search'); //업체 리스트 검색
        Route::any('GetArea', 'OCompany@GetArea'); //업체 리스트 검색
    });
    Route::group(['prefix'=>'Delivery'], function(){
        Route::any('/', 'ODelivery@Delivery'); //용달 견적 등록 페이지
    });
    Route::group(['prefix'=>'Clean'], function(){
        Route::any('/', 'OClean@Clean'); //용달 견적 등록 페이지
    });
    Route::any('Agreement', function () { return view('ios.Agreement'); });
});

Route::any('/GetAreaCode', 'CommonController@GetAreaCode'); //지역 코드 가져오기
Route::any('/AdresToCoor', 'CommonController@AdresToCoor'); //주소 > 구글좌표 변환
Route::any('/GetValueCode', 'CommonController@GetValueCode'); //주소 > 구글좌표 변환

Route::group(array('domain' => 'sc.24all.co.kr'), function(){
    //삼성카드
    Route::any('Main', 'SamSung@View'); // 메인 페이지
    Route::any('EstimateListView', 'SamSung@EstimateListView'); // 견적 리스트 페이지 뷰
    Route::any('MyEstimateView', 'SamSung@MyEstimateView'); // 내 견적 페이지 뷰

    Route::any('/', 'SamSung@WebView'); // 웹 메인 페이지
    Route::any('WTerms', 'SamSung@WTermsView'); // 약관
    Route::any('WEstimateList', 'SamSung@WEstimateListView'); // 웹 견적 리스트
    Route::any('WMyEstimate', 'SamSung@WMyEstimateView'); // 내 견적 페이지 뷰

    Route::group(['prefix'=>'Api'], function(){
        Route::any('GetMainCount', 'SamSung@GetMainCount'); // 삼성카드 메인 견적수 가져오기
        Route::any('Search', 'SamSung@Search'); // 삼성카드 메인 견적수 가져오기
        Route::any('MyEstimateSearch', 'SamSung@MyEstimateSearch'); // 내 견적 가져오기

        Route::any('WebEstimateListSearch', 'SamSung@WEstimateListSearch'); // 내 견적 가져오기

        Route::any('SetPhoneChk', 'SamSung@SetPhoneChk'); // 내 견적 가져오기
        Route::any('ConfirmChk', 'SamSung@ConfirmChk'); // 내 견적 가져오기
    });
});

//이사모아 데모
Route::group(array('domain' => 'vdt004.venditz.com'), function(){
    Route::any('/Demo', 'ZDemo@View'); // 로그인 화면
});













//관리자 페이지
Route::group(array('domain' => 'vdtadmin.24all.co.kr'), function(){
    Route::any('/', 'MLogin@Login'); // 로그인 화면
    Route::any('Company', 'MCompany@View'); // 업체 관리 화면
    Route::any('Estimate', 'MEstimate@View'); // 통합 견적 화면
    Route::any('Delivery', 'MDelivery@View'); // 용달 견적 화면
    Route::any('Point', 'MPoint@View'); // 통합 포인트 관리 화면
    Route::any('CallStats', 'MCallStats@View'); // 통계(콜) 화면
    Route::any('EstimateStats', 'MEstimateStats@View'); // 통계(견적) 화면
    Route::any('EstimateAllDate', 'MEstimateAllDate@View'); // 통계(견적-전체) 화면
    Route::any('PointProduct', 'MPointProduct@View'); // 요금제 관리 화면
    Route::any('ChargePoint', 'MChargePoint@View'); // 요금제 포인트 충전
    Route::any('Indefinite', 'MIndefinite@View'); // 무제한 요금제
    Route::any('CompanyJoinForm', 'MCompanyJoinForm@View'); // 웹가입
    Route::any('Counsel', 'MCounsel@View'); // 상담
    Route::any('CompanyArea', 'MCompanyArea@View'); // 지역별 업체 검색
    Route::any('SMS', 'MSMService@View'); // 지역별 업체 검색

    Route::group(['prefix'=>'Api'], function(){
        Route::any('MngLog', 'CommonController@MngLog'); // 관리자 로그
        Route::any('LoginChk', 'MLogin@LoginChk'); // 로그인 체크

        //Company
        Route::group(['prefix'=>'Company'], function(){
            Route::any('Search', 'MCompany@CompanySearch'); // 업체 검색
            Route::any('CompanyDetail', 'MCompany@CompanyDetail'); // 업체 상세
            Route::any('EstimateDetail', 'MCompany@EstimateDetail'); // 입찰 견적
            Route::any('PointDetail_2016', 'MCompany@PointDetail_2016'); // 포인트 사용내역 - 2016년도
            Route::any('NormalCard', 'MCompany@NormalCard'); // 카드 결제 상태 변경
            Route::any('SSCard', 'MCompany@SSCard'); // 삼성카드 결제 상태 변경
            Route::any('EventAdd', 'MCompany@EventAdd'); // 이벤트 참여 상태 변경
            Route::any('NewCompanyInfoSave', 'MCompany@NewCompanyInfoSave'); // 신규 업체 정보 저장
            Route::any('NewCompanyRegi', 'MCompany@NewCompanyRegi'); // 신규 업체 지역 저장
            Route::any('GetCompanyArea', 'MCompany@GetCompanyArea'); // 업체 지역 가져오기
            Route::any('CompanyIconUpload', 'MCompany@CompanyIconUpload'); // 업체 아이콘 업로드
            Route::any('CompanyInfoUpdate', 'MCompany@CompanyInfoUpdate'); // 업체 정보 업데이트
            Route::any('CompanyAreaInsert', 'MCompany@CompanyAreaInsert'); // 업체 지역 추가
            Route::any('CompanyAreaDelete', 'MCompany@CompanyAreaDelete'); // 업체 지역 삭제
            Route::any('CompanyAreaGradeChange', 'MCompany@CompanyAreaGradeChange'); // 업체 지역 추가
            Route::any('CompanyMatching', 'MCompany@CompanyMatching'); // 업체 인증
            Route::any('GatPointChargeInfo', 'MCompany@GatPointChargeInfo'); // 업체 요금제/결제 정보
            Route::any('SatPointChargeInfo', 'MCompany@SatPointChargeInfo'); // 업체 요금제/결제 정보 저장
            Route::any('GetPointProduct', 'MCompany@GetPointProduct'); // 요금제 정보
            Route::any('PrintGrid', 'MCompany@PrintGrid'); // 엑셀 파일 출력용
        });

        //Estimate
        Route::group(['prefix'=>'Estimate'], function(){
            Route::any('SearchMV', 'MEstimate@SearchMV'); // 이사 견적 검색
            Route::any('RealCountMV', 'MEstimate@RealCountMV'); // 이사 견적 카운트 (총, 승인, 비승인 / 앱, 웹, 모바일 / 입찰, 미입찰, 입찰률)
            Route::any('SearchCL', 'MEstimate@SearchCL'); // 청소 견적 검색
            Route::any('RealCountCL', 'MEstimate@RealCountCL'); // 청소 견적 카운트 (총, 승인, 비승인)
            Route::any('EstimateTenderCompany', 'MEstimate@EstimateTenderCompany'); // 이사 견적 입찰 업체 목록
            Route::any('EstimateStateYes', 'MEstimate@EstimateStateYes'); // 이사 견적 - 승인
            Route::any('EstimateStateNo', 'MEstimate@EstimateStateNo'); // 이사 견적 - 비승인
            Route::any('EstimateDouble', 'MEstimate@EstimateDouble'); // 이사 중복 견적
            Route::any('EstimateDoubleDetail', 'MEstimate@EstimateDoubleDetail'); // 이사 중복 견적
            Route::any('EstimateUnusual', 'MEstimate@EstimateUnusual'); // 이사 중복 견적
            Route::any('CompulsionAssignmentCompanySearch', 'MEstimate@CompulsionAssignmentCompanySearch'); // 이사 견적 강제 할당 업체 검색
            Route::any('EstimateAssignment', 'MEstimate@EstimateAssignment'); // 이사 견적 강제 할당
            Route::any('PrintGrid', 'MEstimate@PrintGrid'); // 엑셀 파일 출력용
            Route::any('ReEstimate', 'MEstimate@ReEstimate'); // 견적 재등록
        });

        //Delivery
        Route::group(['prefix'=>'Delivery'], function(){
            Route::any('Search', 'MDelivery@Search'); // 용달 견적 검색
            Route::any('SearchCount', 'MDelivery@SearchCount'); // 용달 견적 검색
            Route::any('ReasonInsert', 'MDelivery@ReasonInsert'); // 용달 견적 상태 변경
            Route::any('EstimateTenderCompany', 'MDelivery@EstimateTenderCompany'); // 용달 견적 입찰 업체 목록
            Route::any('DeliveryCompanySearch', 'MDelivery@DeliveryCompanySearch'); // 용달 견적 강제 할당 업체 검색
            Route::any('EstimateAssignment', 'MDelivery@EstimateAssignment'); // 용달 견적 강제 할당 로직
        });

        //Point
        Route::group(['prefix'=>'Point'], function(){
            Route::any('Search', 'MPoint@Search'); // 업체 검색
            Route::any('CompanyDetail', 'MPoint@CompanyDetail'); // 업체 상세 정보 - 수정해야함
            Route::any('CompanyPointUseList', 'MPoint@CompanyPointUseList'); // 업체 포인트 사용내역
            Route::any('PointUpdate', 'MPoint@PointUpdate'); // 업체 포인트 내역 업데이트
            Route::any('PointReturnRequest', 'MPoint@PointReturnRequest'); // 업체 포인트 반환 요청 업체 리스트
            Route::any('PointReturnList', 'MPoint@PointReturnList'); // 업체 - 반환요청 리스트
            Route::any('PointReturnResultList', 'MPoint@PointReturnResultList'); // 업체 - 반환결과 리스트
            Route::any('PointReturnResult', 'MPoint@PointReturnResult'); // 업체 - 반환결과 입력
            Route::any('PrintGrid', 'MPoint@PrintGrid'); // 엑섹 파일 출력용
        });

        //CallStats
        Route::group(['prefix'=>'CallStats'], function(){
            Route::any('CustomCompanyCall', 'MCallStats@CustomCompanyCall'); // 소비자 > 추천업체 콜 수
            Route::any('CompanyCall', 'MCallStats@CompanyCall'); // 업체별 콜 수
            Route::any('AreaCall', 'MCallStats@AreaCall'); // 지역별 콜 수
            Route::any('LineChart', 'MCallStats@LineChart'); // 차트
        });

        //EstimateStats
        Route::group(['prefix'=>'EstimateStats'], function(){
            Route::any('InflowRoute', 'MEstimateStats@InflowRoute'); // 유입 경로별
            Route::any('AreaCategory', 'MEstimateStats@AreaCategory'); // 지역별
            Route::any('AreaCategory2', 'MEstimateStats@AreaCategory2'); // 차트
        });

        //EstimateAllDate
        Route::group(['prefix'=>'EstimateAllDate'], function(){
            Route::any('AllChart', 'MEstimateAllDate@AllChart'); // 전체 그리드

            Route::any('Comparison_MinusYearDay', 'MEstimateAllDate@Comparison_MinusYearDay'); //
            Route::any('Comparison_MinusYearMonth', 'MEstimateAllDate@Comparison_MinusYearMonth'); //
            Route::any('Comparison_MinusYear', 'MEstimateAllDate@Comparison_MinusYear'); //
            Route::any('Comparison_WeekMonth', 'MEstimateAllDate@Comparison_WeekMonth'); //
            Route::any('Comparison_WeekYear', 'MEstimateAllDate@Comparison_WeekYear'); //
            Route::any('YearMonthChart', 'MEstimateAllDate@YearMonthChart'); //
            Route::any('YearMonthDayChart', 'MEstimateAllDate@YearMonthDayChart'); //
        });

        //GetPointProduct
        Route::group(['prefix'=>'PointProduct'], function(){
            Route::any('GetPointProduct', 'MPointProduct@GetPointProduct'); // 요금제 정보 가져오기
            Route::any('ProductDelete', 'MPointProduct@ProductDelete'); // 요금제 사용중지
            Route::any('ProductInsert', 'MPointProduct@ProductInsert'); // 요금제 사용중지
        });

        //ChargePoint
        Route::group(['prefix'=>'ChargePoint'], function(){
            Route::any('Search', 'MChargePoint@Search'); // 요금제 사용 업체 검색
            Route::any('CompanyDetail', 'MChargePoint@CompanyDetail'); // 요금제 사용 업체 상세내용
            Route::any('PointDetail', 'MChargePoint@PointDetail'); // 요금제 사용 상세내용
            Route::any('PointMP', 'MChargePoint@PointMP'); // 요금제 포인트 충전/회수
            Route::any('PointReCharge', 'MChargePoint@PointReCharge'); // 요금제 포인트 - 반환 충전
            Route::any('PaymentList', 'MChargePoint@PaymentList'); // 요금제 포인트 - 결제 현황
            Route::any('PaymentDateInsert', 'MChargePoint@PaymentDateInsert'); // 요금제 포인트 - 결제 현황 입력
            Route::any('PaymentDateDelete', 'MChargePoint@PaymentDateDelete'); // 요금제 포인트 - 결제 현황 삭제
            Route::any('PaySearch', 'MChargePoint@PaySearch'); //
        });

        //Indefinite
        Route::group(['prefix'=>'Indefinite'], function(){
            Route::any('Search', 'MIndefinite@Search'); //업체 리스트
            Route::any('SearchNoPay', 'MIndefinite@SearchNoPay'); //미결제 업체
            Route::any('Pay', 'MIndefinite@Pay'); //
            Route::any('NoPay', 'MIndefinite@NoPay'); //
            Route::any('PaySMS', 'MIndefinite@PaySMS'); //
            Route::any('RecoveryCompany', 'MIndefinite@RecoveryCompany'); //
        });

        //CompanyJoinForm
        Route::group(['prefix'=>'CompanyJoinForm'], function(){
            Route::any('Search', 'MCompanyJoinForm@Search'); //업체 리스트
            Route::any('NewCompany', 'MCompanyJoinForm@NewCompany'); //업체 정보 입력
            Route::any('NewRegi', 'MCompanyJoinForm@NewRegi'); //업체 알람 지역 입력
            Route::any('CompRegi', 'MCompanyJoinForm@CompRegi'); //가입 처리
        });

        //Counsel
        Route::group(['prefix'=>'Counsel'], function(){
            Route::any('Search', 'MCounsel@Search'); //상담 검색
            Route::any('CounselCompanySearch', 'MCounsel@CounselCompanySearch'); //상담 업체 검색
            Route::any('CounselInsert', 'MCounsel@CounselInsert'); //상담 입력
            Route::any('CounselUpdateSetting', 'MCounsel@CounselUpdateSetting'); //상담 결과입력을 위한 셋팅
            Route::any('CounselStateChange', 'MCounsel@CounselStateChange'); //상담 상태 변경
            Route::any('Solution', 'MCounsel@Solution'); //상담 상태 변경
        });

        //CompanyArea
        Route::group(['prefix'=>'CompanyArea'], function(){
            Route::any('SearchArea', 'MCompanyArea@SearchArea'); //검색
        });

        //SMS
        Route::group(['prefix'=>'SMS'], function(){
            Route::any('SendList', 'MSMService@SendList'); //발송 내역
            Route::any('ReturnList', 'MSMService@ReturnList'); //수신 내역
            Route::any('Search', 'MSMService@Search'); //업체 검색
            Route::any('SearchPay', 'MSMService@SearchPay'); //요금 검색
        });
    });
});