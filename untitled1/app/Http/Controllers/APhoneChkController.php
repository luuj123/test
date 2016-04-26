<?php
/**
 * Created by PhpStorm.
 * User: s-hyun
 * Date: 2015-07-22
 * Time: 오전 9:42
 */

header('Content-Type: text/html; charset=utf-8');
header("Cache-control: No-Cache");
header("Pragma: No-Cache");
date_default_timezone_set("Asia/Seoul");
require_once("../lib/db_conn.php");

class PhoneChkController {
    function SendPhone($PhoneNumber){
        $r=str_pad(mt_rand(0,999999),6,'0');
        $SendText = "이사모아%20휴대폰%20인증%20서비스입니다.%20인증번호는%20[%20".$r."%20]%20입니다.";
        $url = "http://jycadmin.24all.co.kr/Source/controller/SMSController.php?numberList=".$PhoneNumber."&sender=16702477&info=".$SendText;
        $ch=curl_init(); //파라미터:url -선택사항

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        //echo $data.":";
        //echo $curl_errno.":";
        //echo $curl_error;
        curl_close($ch);

        $mySql = new MYSQL();
        $mySql->begin_transaction();

        $query = "INSERT INTO USER_PHONE_CHK (USER_PHONE, CONFIRM, REGDATE) VALUES ( '".$PhoneNumber."','".$r."',NOW() )";
        $this->sendQuery($mySql, $query);

        if(mysql_affected_rows()==1){
            $mySql->commit();
            $mySql->close();
            return true;
        } else {
            $mySql->rollback();
            return "rollback";
        }
    }

    function ConfirmChk($PhoneNumber,$ChkNumber){
        $mySql = new MYSQL();
        $query = "SELECT COUNT(1) FROM USER_PHONE_CHK WHERE USER_PHONE = '".$PhoneNumber."' AND CONFIRM = '".$ChkNumber."' AND REGDATE >= date_add(now(), interval -1 hour) order by REGDATE DESC LIMIT 1";
        $mySql = $this->sendQuery($mySql, $query);

        $data = array();
        while($row = $mySql->fetch_row()){
            $form = array();
            $form['Count'] = $row[0];
            array_push($data, $form);
        }
        $mySql->close();
        return $data;
    }

	function EstimateEnd($PhoneNumber){

        $PhoneNumber = str_replace("+82", "0", $PhoneNumber);

		$SendText = "[이사모아%20견적등록완료]%0A상담전화가%20곧%20올꺼에요^^%20가격/품질%20비교%20후%20만족스런%20이사하세요♡";
        //$SendText = "[견적등록완료]%0A곧%20이사업체가%20상담전화를%20드려요~%0A비교해%20방문견적%20받고%20만족스런%20이사하세요♡%0A%0A또한%20이사모아에서는%20고객님의%0A이사편의를%20돕기%20위해%0A협력업체를%20안내해드립니다~%0A%0A1.%20이사청소%0A관공서,%20어린이집%20청소까지%0A책임지고%20있는%20우수청소업체%0A재%20청소율%20100%!!%0A소독살균%20무료서비스%0A%0A2.%20폐가전/폐가구/헌옷%0A이사%20후%20버려야할%0A가전/가구%20돈주고%20버리기%20너무%20아까우셨죠?%0A이사모아가%20무상수거%20해드립니다.%0A%0A3.%20해충박멸%0A진드기,%20해충%20박멸을%20한번에%0A%0A필요하신%20서비스명과%20함께%0A원하시는%20서비스%20시행일을%0A문자로%20보내주세요%0A%0A이사모아에서%0A협력업체를%20안내해드리겠습니다~%0A%0A예1)%20청소%2011월%2021일%0A예2)%20폐가구%2011월%2030일%0A예3)%20해충%2011월%2010일%0A%0A도움되는%20리빙%20상품%20추천!!%0A1.%20리빙스턴%0A음식물%20쓰레기를%20싱크대에서%20자동으로%20완전%20분해하는%20음식물처리기!!%0A%0A2.%20피톤치드%0A지속적인%20새집증후%20완전제거에%20대한%20산림욕%20무상지원(피톤치드는%20별도구매)에%20대한%20상담만%20받아도%20홈케어%20서비스를%20무료로%20제공해드려요~%0A%0A이사모아%20앱%20리빙모아에서%20자세한%20정보를%20확인하세요~^^";
		//$SendText = "[견적등록완료]%0A곧%20이사업체가%20상담전화를%20드려요~%0A비교해%20방문견적%20받고%20만족스런%20이사하세요♡%0A%0A또한%20이사모아에서는%20고객님의%0A이사편의를%20돕기%20위해%0A협력업체를%20안내해드립니다~%0A%0A1.%20이사청소%0A관공서,%20어린이집%20청소까지%0A책임지고%20있는%20우수청소업체%0A재%20청소율%20100%!!%0A소독살균%20무료서비스%0A%0A2.%20폐가전/폐가구/헌옷%0A이사%20후%20버려야할%0A가전/가구%20돈주고%20버리기%20너무%20아까우셨죠?%0A이사모아가%20무상수거%20해드립니다.%0A%0A3.%20해충박멸%0A진드기,%20해충%20박멸을%20한번에%0A%0A필요하신%20서비스명과%20함께%0A원하시는%20서비스%20시행일을%0A문자로%20보내주세요%0A%0A이사모아에서%0A협력업체를%20안내해드리겠습니다~%0A%0A예1)%20청소%2011월%2021일%0A예2)%20폐가구%2011월%2030일%0A예3)%20해충%2011월%2010일%0A%0A도움되는%20리빙%20상품%20추천!!%0A1.%20리빙스턴%0A음식물%20쓰레기를%20싱크대에서%20자동으로%20완전%20분해하는%20음식물처리기!!%0A%0A2.%20피톤치드%0A지속적인%20새집증후%20완전제거에%20대한%20산림욕%20무상지원(피톤치드는%20별도구매)에%20대한%20상담만%20받아도%20홈케어%20서비스를%20무료로%20제공해드려요~%0A%0A이사모아%20앱%20리빙모아에서%20자세한%20정보를%20확인하세요~^^";
		//$SendText = "[견적등록완료]%20곧%20이사업체가%20상담전화를%20드려요~%20비교해%20방문견적%20받고%20만족스런%20이사하세요♡";
        //$SendText = "[이사비교견적%20등록완료]%20곧%20이사모아에%20가입된%20이사업체에서%20상담전화를%20드릴거에요.%20비교해%20방문견적%20받으시고%20만족스런%20이사하세요-♡%20항상%20건강하시고%20좋은%20하루%20보내세요~";
        $url = "http://jycadmin.24all.co.kr/Source/controller/SMSController.php?numberList=".$PhoneNumber."&sender=16702477&info=".$SendText;
        $ch=curl_init(); //파라미터:url -선택사항

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        curl_close($ch);
    }

	function CLEstimateEnd($PhoneNumber){

        $PhoneNumber = str_replace("+82", "0", $PhoneNumber);

		$SendText = "[청소견적%20등록완료]%0A%0A이사모아에%20정식%20가입된%20청소전문업체가%203일%20이내로%20상담전화%20드립니다♡";
        $url = "http://jycadmin.24all.co.kr/Source/controller/SMSController.php?numberList=".$PhoneNumber."&sender=16702477&info=".$SendText;
        $ch=curl_init(); //파라미터:url -선택사항

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        curl_close($ch);
    }

    function CLCompany($PhoneNumber){

        $PhoneNumber = str_replace("+82", "0", $PhoneNumber);

        $SendText = "[이사모아]%20청소견적이%20등록되었습니다.";

        $url = "http://jycadmin.24all.co.kr/Source/controller/SMSController.php?numberList=".$PhoneNumber."&sender=16702477&info=".$SendText;
        $ch=curl_init(); //파라미터:url -선택사항

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        $data = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        $curl_error = curl_error($ch);

        curl_close($ch);
    }

    function sendQuery($mySql, $query){
        if(!$mySql->query($query)){
            $mySql->error();
            exit;
        }
        return $mySql;
    }
}