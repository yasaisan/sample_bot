<?php
DEFINE("ACCESS_TOKEN","QMWZXjlgVbI1eyWO+nlH7Q0VBWc/fnnlDVppIkcAwKeoPgQps8HuibW4W16nDgpi/HS4+1bcrRIxqDyDclsfUrQC8Gkyxiv/+Qi33dDqos1nGRsN0F18y5e5xGVEaNrul0BwJE2lKX6AFn0YArEfSQdB04t89/1O/w1cDnyilFU=");
DEFINE("SECRET_TOKEN","62aa32a57fa6748ff49014ee153dff51");

use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\Constant\HTTPHeader;

//LINESDKの読み込み
require_once(__DIR__."/../../vendor/autoload.php");

//LINEから送られてきたらtrueになる
if(isset($_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE])){

    //LINEBOTにPOSTで送られてきた生データの取得
    $inputData = file_get_contents("php://input");
    
    error_log(print_r(SECRET_TOKEN,true));
    
    //LINEBOTSDKの設定
    $httpClient = new CurlHTTPClient(ACCESS_TOKEN);
    
    error_log(print_r($httpClient,true));
    
    $Bot = new LINEBot($HttpClient, ['channelSecret' => SECRET_TOKEN]);
    $signature = $_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE]; 
    $Events = $Bot->parseEventRequest($InputData, $Signature);
    
    //大量にメッセージが送られると複数分のデータが同時に送られてくるため、foreachをしている。
    foreach($Events as $event){
        $SendMessage = new MultiMessageBuilder();
        $TextMessageBuilder = new TextMessageBuilder("よろぽん！");
        $SendMessage->add($TextMessageBuilder);
        $Bot->replyMessage($event->getReplyToken(), $SendMessage);
    }
}
//$accessToken = 'QMWZXjlgVbI1eyWO+nlH7Q0VBWc/fnnlDVppIkcAwKeoPgQps8HuibW4W16nDgpi/HS4+1bcrRIxqDyDclsfUrQC8Gkyxiv/+Qi33dDqos1nGRsN0F18y5e5xGVEaNrul0BwJE2lKX6AFn0YArEfSQdB04t89/1O/w1cDnyilFU=';
// 
////ユーザーからのメッセージ取得
//$json_string = file_get_contents('php://input');
//$json_object = json_decode($json_string);
// 
////取得データ
//$replyToken = $json_object->{"events"}[0]->{"replyToken"};        //返信用トークン
//$message_type = $json_object->{"events"}[0]->{"message"}->{"type"};    //メッセージタイプ
//$message_text = $json_object->{"events"}[0]->{"message"}->{"text"};    //メッセージ内容
// 
////メッセージタイプが「text」以外のときは何も返さず終了
//if($message_type != "text") exit;
// 
////返信メッセージ
//$return_message_text = "「" . $message_text . "」ｗｗｗ";
// 
////返信実行
//sending_messages($accessToken, $replyToken, $message_type, $return_message_text);
//
//
////メッセージの送信
//function sending_messages($accessToken, $replyToken, $message_type, $return_message_text){
//    //レスポンスフォーマット
//    $response_format_text = [
//        "type" => $message_type,
//        "text" => $return_message_text
//    ];
// 
//    //ポストデータ
//    $post_data = [
//        "replyToken" => $replyToken,
//        "messages" => [$response_format_text]
//    ];
// 
//    //curl実行
//    $ch = curl_init("https://api.line.me/v2/bot/message/reply");
//    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
//    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//        'Content-Type: application/json; charser=UTF-8',
//        'Authorization: Bearer ' . $accessToken
//    ));
//    $result = curl_exec($ch);
//    curl_close($ch);
//}