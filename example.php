<?php
/* * **************************************************************************
 * 
 * MIT License
 * 
 * APISTORE.CO.KR - Kakao Notifiaction Talk API Plugin For PHP
 * 
 * Created by doogle@naver.com (Shin, dong-hoon) @ 2019-05-29
 *
 * Blog : https://doogle.link/
 * 
 * APISTORE 카카오알림톡 API 구현체 사용예제
 * 
 * ***************************************************************************
 *
 * https://www.apistore.co.kr/api/apiProviderGuide.do?service_seq=558
 * 이 소스코드는 카카오 알림톡 API 사용자 가이드 (ver 1.5.2) 기반으로 제작하였습니다.
 * 
 * API 문서 변동 사항
 * 
 * 2017.11.09 (v1.5) : 템플릿 버튼 5 개 추가, 템플릿 조회 API, Response 추가
 * 
 */
 
// 아래 APIStore 사용설명서를 참고하시기 바랍니다.
// https://www.apistore.co.kr/api/apiProviderGuide.do?service_seq=558 

require_once "./TKakaoNotificationTalk.php";

// 초기화.
$key = "..."; // 구매 시 발급받은 Key의 코드값 (헤더 “x-waple-authorization”의 값으로 설정하는 값을 말한다.)
$clientId = "..."; // {client_id} 는 API스토어에 가입한 후 해당 API를 사용(구매) 신청한 ID.
$defaultCallBack = "..."; // 발신자 전화번호

$knt = new TKakaoNotificationTalk\TKakaoNotificationTalk($key, $clientId, $defaultCallBack);

// 메시지 발송.
$body = [
      phone => "수신자 전화번호",
      callback => "발진자 전화번호", // 발신번호 발신자번호등록 기능으로 먼저 등록해야 메시지발송이 가능합니다.
      reqdate => "", // 예약발송시 다음과같은 형식으로 일시를 지정한다. "20160517000000", 비워두면 즉시발송.
      msg => "발송할 메시지 내용입니다.", // 변수부분을 제외하고 템플릿내용 동일해야합니다.
      template_code => "...", // 미리 APISTORE 카카오 알림톡 템플릿으로 등록승인된 템플릿의 코드값
      url => "https://...", // 알림톡 버튼에 연결할 URL 주소값.
      url_button_txt => "상세보기",
      // 그외 추가 가능한 값들...
      // failed_type => "LMS",
      // failed_subject => "",
      // failed_msg => "",
      // btn_types => "웹링크,웹링크",
      // btn_txts => "숙소예약확인서,숙소홈페이지",
      // btn_urls1 => "https://...",
      // btn_urls2 => "https://..."
];
$response = $knt->postMessage($body);
if ($response->body->result_message == "OK") {
  // 정상 발송. (이것의 의미는 API호출이.정상적일뿐이고 실제 수신자에게 제대로 메시지가전달되었는지는 발송결과확인을 해야 알 수 있다.)
  echo "CMID: " . $response->body->cmid . "\n"; // cmid 값을 기억하고 있다가 발송결과 확인할 때 사용하면 된다.
  echo "OK.\n";
} else {
  // ERROR 발송 실패.
  echo "RESULT MESSAGE: " . $response->body->result_message ."\n";
  echo "RESULT CODE: " . $response->body->result_code ."\n";
}


// 발송결과 확인. (발송결과는 발송후 즉시받을 수 있는 것이 아니므로 5분에 한번씩이라든지 주기적으로 확인해야 합니다.)
$sendResponse = $knt1->getReport(["cmid" => $response->body->cmid]);
if (isset($sendResponse) 
  && isset($sendResponse->body) 
  && isset($sendResponse->body->CMID) 
  && $sendResponse->body->CMID != "" 
  && $sendResponse->body->CMID != "result is null") {
  echo "report_status        => " . $sendResponse->body->STATUS . "\n";                                  // 발송상태 1: 발송대기 2: 전송완료 3: >결과수신완료
  echo "report_status_text   => " . $knt1->getReportStatusText($sendResponse->body->STATUS) . "\n";      // 발송상태 텍스트
  echo "report_rslt          => " . $sendResponse->body->RSLT . "\n";                                    // 최종 카카오알림톡 결과수신
  echo "report_rslt_text     => " . $knt1->getReportResultText($sendResponse->body->RSLT) . "\n";        // 최종 카카오알림톡 결과수신 텍스트
  echo "report_msg_rslt      => " . $sendResponse->body->msg_rslt . "\n";                                // 최종 카카오알림톡 실패 시 메시지 결과수신
  echo "report_msg_rslt_text => " . $knt1->getReportMsgResultText($sendResponse->body->msg_rslt) . "\n"; // 최종 카카오알림톡 실패 시 메시지 결과수신 텍스트
} else {
  echo "ERROR 발송결과 확인도중 오류발생.\n";
}

