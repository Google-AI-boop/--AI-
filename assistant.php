<?php
// assistant.php
// 簡易語音助理後端示範（Webhook）
// 說明：此檔案接受 JSON POST，格式 { "text": "使用者文字" }
// 回傳 JSON: { "reply": "助理回覆文字" }
// 可搭配前端 assistant.html 使用 fetch('/assistant.php', {method:'POST', body: JSON.stringify({text})})

header('Content-Type: application/json; charset=utf-8');
// Allow CORS for demo (請在生產環境收窄來源)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
// CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$raw = file_get_contents('php://input');
if (empty($raw)) {
    // 也接受 application/x-www-form-urlencoded 的 text 欄位
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
} else {
    $data = json_decode($raw, true);
    $text = isset($data['text']) ? trim($data['text']) : '';
}

if ($text === '') {
    echo json_encode(['error' => 'no_text_provided', 'reply' => '請提供文字輸入。']);
    exit;
}

// 簡單意圖辨識
$lower = mb_strtolower($text, 'UTF-8');
$reply = '';

// 問候
if (preg_match('/(你好|哈囉|嗨|您好)/u', $text)) {
    $reply = '你好！很高興為你服務。';
} elseif (preg_match('/(時間|現在幾點|現在時間)/u', $text)) {
    $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
    $reply = '現在時間是 ' . $now->format('H:i') . '。';
} elseif (preg_match('/(天氣|氣象|下雨|溫度)/u', $text)) {
    $reply = '目前我還不能直接取得即時天氣資料；你可以連接到第三方天氣 API 我就能回覆詳細資訊。';
} elseif (preg_match('/(停止|拜拜|掰掰|再見)/u', $text)) {
    $reply = '再見！有需要再叫我～';
} else {
    // 預設回覆，示範如何回傳使用者原文
    $reply = '我聽到你說：' . $text . '。這是 PHP 範例回覆，你可以把這裡改成呼叫外部智慧對話 API。';
}

// 可延伸：與 Dialogflow / Google Assistant 集成、記憶使用者狀態、存取資料庫等

echo json_encode(['reply' => $reply], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
