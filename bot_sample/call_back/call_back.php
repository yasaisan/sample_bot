<?php
DEFINE("ACCESS_TOKEN","QMWZXjlgVbI1eyWO+nlH7Q0VBWc/fnnlDVppIkcAwKeoPgQps8HuibW4W16nDgpi/HS4+1bcrRIxqDyDclsfUrQC8Gkyxiv/+Qi33dDqos1nGRsN0F18y5e5xGVEaNrul0BwJE2lKX6AFn0YArEfSQdB04t89/1O/w1cDnyilFU=");
DEFINE("SECRET_TOKEN","62aa32a57fa6748ff49014ee153dff51");

use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
//use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\Constant\HTTPHeader;

//LINESDKの読み込み
require_once(__DIR__."/../../vendor/autoload.php");

// まずあらかじめ翻訳するための認証キーをAzureポータルサイトから
// 取得しておき、以下のKEY_1と置き換えます
$azure_key = "351118560b1a45929f0d91492722b4af";
// 翻訳元言語です
// 翻訳可能な言語の一覧は以下のブログ記事に載っています
// http://ken-tokyo.hatenablog.com/entry/2017/12/08/150212
$fromLanguage = "en";  
// 翻訳先言語です
$toLanguage = "ja";
// 翻訳する英文です
$inputStr = "This";


$list = array(
    "大吉",
    "中吉",
    "小吉",
    "末吉",
    "吉",
    "凶",
);

//LINEから送られてきたらtrueになる
if(isset($_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE])){

    //LINEBOTにPOSTで送られてきた生データの取得
    $inputData = file_get_contents("php://input");
    
    error_log("inputData-------- : " . print_r($inputData,true));
    
    //LINEBOTSDKの設定
    $httpClient = new CurlHTTPClient(ACCESS_TOKEN);
    
//    error_log("y--------" . print_r($inputData,true));
    
    $Bot = new LINEBot($httpClient, ['channelSecret' => SECRET_TOKEN]);
    $signature = $_SERVER["HTTP_".HTTPHeader::LINE_SIGNATURE];
    
//    error_log("y--------" . print_r($signature,true));
     
    $Events = $Bot->parseEventRequest($inputData, $signature);
    
    error_log("Events-------- : " . print_r($Events,true));
    
    //大量にメッセージが送られると複数分のデータが同時に送られてくるため、foreachをしている。
    foreach ($Events as $event) {
        if (!($event instanceof TextMessage)) {
//            $logger->info('Non text message has come');
            continue;
        }
        error_log("InputText-------- : " . print_r($event->getText(), true));
        // 入力文字
        $input_text = $event->getText();
        
        // 翻訳処理
        // 翻訳するためのトークンを取得します。有効期限は取得後10分間です
        $accessToken = getToken($azure_key);

           // 翻訳するための文字列を生成します
        $params = "text=" . urlencode($input_text) . "&to=" . $toLanguage . "&from=" . $fromLanguage . "&appId=Bearer+" . $accessToken;

        // 翻訳するためのURLを生成します
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?$params";

        // 翻訳を実行します
        $curlResponse = curlRequest($translateUrl);

        // 翻訳結果はxmlで帰ってくるのでそれを読み込みます
//        $translatedStr = simplexml_load_string($curlResponse);
        
        error_log("curlResponse-------- : " . print_r($curlResponse, true));
        preg_match('/>(.+?)<\/string>/',$curlResponse, $m);
        $transrateInputText = $m[1];
        error_log("transrateInputText-------- : " . print_r($transrateInputText, true));
//        error_log("transrateText-------- : " . print_r($translatedStr[0], true));

        $SendMessage = new MultiMessageBuilder();
        $TextMessageBuilder = new TextMessageBuilder($transrateInputText);
        $SendMessage->add($TextMessageBuilder);
        $Bot->replyMessage($event->getReplyToken(), $SendMessage);
    }
}
// 翻訳するためのトークンを取得する関数です
// このトークンの有効期限は、取得してから10分間とかなり短めです
function getToken($azure_key)
{
    $url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
    $ch = curl_init();
    $data_string = json_encode('{body}');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string),
            'Ocp-Apim-Subscription-Key: ' . $azure_key
        )
    );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $strResponse = curl_exec($ch);
    curl_close($ch);
    return $strResponse;
}

// 翻訳を実行する関数です
function curlRequest($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
    return $curlResponse;
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