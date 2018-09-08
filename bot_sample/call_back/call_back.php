<?php
DEFINE("ACCESS_TOKEN","QMWZXjlgVbI1eyWO+nlH7Q0VBWc/fnnlDVppIkcAwKeoPgQps8HuibW4W16nDgpi/HS4+1bcrRIxqDyDclsfUrQC8Gkyxiv/+Qi33dDqos1nGRsN0F18y5e5xGVEaNrul0BwJE2lKX6AFn0YArEfSQdB04t89/1O/w1cDnyilFU=");
DEFINE("SECRET_TOKEN","62aa32a57fa6748ff49014ee153dff51");

use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot;
//use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
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
    
//    error_log("inputData-------- : " . print_r($inputData,true));
    
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
            continue;
        }
        $replyInfo = array();
        $reply_token = $event->getReplyToken();
        error_log("InputText-------- : " . print_r($event->getText(), true));
        // 入力文字
        $input_text = $event->getText();
        
        // 翻訳処理
        // 翻訳するためのトークンを取得します。有効期限は取得後10分間です
        $accessToken = getToken($azure_key);

           // 翻訳するための文字列を生成します
        $params = "text=" . urlencode($input_text) . "&to=" . $toLanguage . "&from=" . $fromLanguage . "&appId=Bearer+" . $accessToken;

        // 翻訳するためのURLを生成
        $translateUrl = "http://api.microsofttranslator.com/v2/Http.svc/Translate?" . $params;
        // 翻訳を実行
        $curlResponse = curlRequest($translateUrl);
        
//        error_log("curlResponse-------- : " . print_r($curlResponse, true));
        preg_match('/>(.+?)<\/string>/',$curlResponse, $m);
        $transrateInputText = $m[1];
        
        error_log("transrateInputText-------- : " . print_r($transrateInputText, true));

        //メッセージ追加
        $TextMessageBuilder = new TextMessageBuilder($transrateInputText);
        array_push($replyInfo, $TextMessageBuilder);
//        replyMessage($Bot, $reply_token, $transrateInputText);
        
        $image_info_lists = google_image($input_text)["items"];
        if ($image_info_lists != null) {
            $count = 0;
            foreach ($image_info_lists as $image_info) {
                if ($count >= 3) {
                    break;
                }
                $ori_url = $image_info["link"];
                $preview_url = $image_info["image"]["thumbnailLink"];
                if(!preg_match('/https/',$ori_url) || !preg_match('/https/',$preview_url)){
                    continue;
                }
//                error_log("reply_token-------- : " . print_r($reply_token, true));
                // 画像追加
                $image_carousel_info = array();
                $image_carousel_info["imageUrl"] = $ori_url;
//              $ImageMessageBuilder = new ImageMessageBuilder($ori_url, $preview_url);
                $ImageCarouselTemplateBuilder = new ImageCarouselTemplateBuilder($image_carousel_info);
                array_push($replyInfo, $ImageMessageBuilder);
                //がそう返却
//              replyImage($Bot, $reply_token, $ori_url, $preview_url);
                $count++;
            }
        } else {
            $TextMessageBuilder = new TextMessageBuilder("画像は見つかりませんでした");
            array_push($replyInfo, $TextMessageBuilder);
            //メッセージ返却
//            replyMessage($Bot, $reply_token, "画像は見つかりませんでした");
        }
         error_log("replyInfo-------- : " . print_r($replyInfo, true));
        // 返信
        replyMultiInfo($Bot, $reply_token, $replyInfo);
    }
}

// シンプルメッセージ返却
function replyMessage($bot, $token, $send_message) {
    
    $SendMessage = new MultiMessageBuilder();
    $TextMessageBuilder = new TextMessageBuilder($send_message);
    $SendMessage->add($TextMessageBuilder);
    
    $res = $bot->replyMessage($token, $SendMessage);
    if (!$res->isSucceeded()) {
        error_log("ReplyFailedMessage : " . print_r($res, true));
    }
}

// シンプル画像返却
function replyImage($bot, $token, $original_url, $thum_url) {
    $SendMessage = new MultiMessageBuilder();
    $ImageMessageBuilder = new ImageMessageBuilder($original_url, $thum_url);
    $SendMessage->add($ImageMessageBuilder);
    $res = $bot->replyMessage($token, $SendMessage);
    if (!$res->isSucceeded()) {
        error_log("ReplyFailedImage : " . print_r($res, true));
    }
}

function replyMultiInfo($bot, $token, $msgs) {
    $SendMessage = new MultiMessageBuilder();
    
    foreach ($msgs as $value) {
        $SendMessage->add($value);
    }
    
    $res = $bot->replyMessage($token, $SendMessage);
    if (!$res->isSucceeded()) {
        error_log("ReplyFailedMultiInfo : " . print_r($res, true));
    }
}

// 翻訳するためのトークンを取得する関数です
// このトークンの有効期限は、取得してから10分間と短め
function getToken($azure_key) {
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

// 翻訳を実行する関数
function curlRequest($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $curlResponse = curl_exec($ch);
    curl_close($ch);
    return $curlResponse;
}

function google_image($word) {
    // TODO: キーの外だし
    $baseurl = "https://www.googleapis.com/customsearch/v1?";
    $baseurl .= "key=AIzaSyCqe72UGyiLECERkWVTvOLXdFJxYvVspTI&cx=016901115011056515106:6pjbegaiuga&searchType=image&q=";
    $myurl = $baseurl . urlencode($word);
    $myjson = file_get_contents($myurl);
    $recs = json_decode($myjson, true);
    return $recs;
}
