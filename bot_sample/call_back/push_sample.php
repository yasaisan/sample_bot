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

/// メッセージ
$messeage_data = [
	"type" => "text",
	"text" => "ABCDEFG"
];
// 画像
$picture_data = [
	"type"               => "image",
	"originalContentUrl" => "https://upload.wikimedia.org/wikipedia/commons/2/27/Sus_scrofa_domesticus%2C_miniature_pig%2C_juvenile.jpg",
	"previewImageUrl"    => "https://upload.wikimedia.org/wikipedia/commons/2/27/Sus_scrofa_domesticus%2C_miniature_pig%2C_juvenile.jpg"
];

// ポストデータ
$post_data = [
	"to"       => "8541054879487",
	"messages" => [ $messeage_data, $picture_data ]
];

// curl実行
$ch = curl_init("https://api.line.me/v2/bot/message/push");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $post_data, JSON_UNESCAPED_UNICODE ));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: application/json; charser=UTF-8',
	'Authorization: Bearer ' . ACCESS_TOKEN
));
$result = curl_exec($ch);
$result = json_decode($result);
curl_close($ch);

if( isset($result->message) ){
	// エラー処理： $result-&gt;messageにエラーメッセージが入っている。
}