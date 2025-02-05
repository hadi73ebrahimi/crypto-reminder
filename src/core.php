<?php
require_once 'config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function sendTelegramMessage($chatId, $message) {
    $url = TELEGRAM_API_URL . "sendMessage";
    
    $postData = json_encode([
        'chat_id' => $chatId,
        'text' => $message,
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function handleTelegramCommand($conn, $update) {
    $chatId = $update['message']['chat']['id'] ?? null;
    $text = trim($update['message']['text'] ?? '');
    if (!$chatId || empty($text)) return;

    if (preg_match('/^\/setalert\s+([A-Za-z0-9\/]+)\s*([<>])\s*([0-9.]+)/i', $text, $matches)){
        handleSetAlert($conn, $chatId, $matches);
    } elseif ($text === '/removealert') {
        handleRemoveAlert($conn, $chatId);
    } elseif ($text === '/getalert') {
        handleGetAlert($conn, $chatId);
    } else {
        sendDefaultHelpMessage($chatId);
    }
}

function handleSetAlert($conn, $chatId, $matches) {
    list(, $symbol, $condition, $price) = $matches;
    $symbol = strtoupper(str_replace('/', '', $symbol));

    $result = mysqli_query($conn, "SELECT * FROM available_pairs WHERE UPPER(symbol) = UPPER('$symbol')");
    if (mysqli_num_rows($result) > 0) {
        mysqli_query($conn, "REPLACE INTO alerts (chat_id, symbol, conditionstate, price) VALUES ('$chatId', '$symbol', '$condition', '$price')");
        sendTelegramMessage($chatId, "✅ Alert set: $symbol $condition $price");
    } else {
        sendTelegramMessage($chatId, "❌ Invalid symbol. Use /getpairs to see available pairs.");
    }
}

function handleRemoveAlert($conn, $chatId) {
    mysqli_query($conn, "DELETE FROM alerts WHERE chat_id = '$chatId'");
    sendTelegramMessage($chatId, "✅ Your alert has been removed. Use /getalert to check active alerts.");
}

function handleGetAlert($conn, $chatId) {
    $result = mysqli_query($conn, "SELECT symbol, conditionstate, price FROM alerts WHERE chat_id = '$chatId'");
    if (mysqli_num_rows($result) > 0) {
        $alerts = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $message = "📌 Your active alerts:\n";
        foreach ($alerts as $alert) {
            $message .= "🔔 {$alert['symbol']} {$alert['conditionstate']} {$alert['price']}\n";
        }
    } else {
        $message = "⚠️ You have no active alerts.";
    }
    sendTelegramMessage($chatId, $message);
}

function sendDefaultHelpMessage($chatId) {
    $message = "📢 *Available Commands:*\n" .
               "✅ Set an alert: `/setalert BTC/USDT > 50000`\n" .
               "✅ Set an alert: `/setalert BTC/USDT < 30000`\n" .
               "🔍 Check alerts: `/getalert`\n" .
               "❌ Remove alert: `/removealert`";
    sendTelegramMessage($chatId, $message);
}

function getTelegramUpdate() {
    $handle = fopen("php://input", "rb"); // Open the input stream
    $rawData = '';

    if ($handle) {
        while (!feof($handle)) {
            $rawData .= fread($handle, 8192);
        }
        fclose($handle);
    }

    return json_decode($rawData, true);
}

$update = getTelegramUpdate();
if (!empty($update['message'])) {
    handleTelegramCommand($conn, $update);
}

mysqli_close($conn);