<?php

require_once 'config.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function getPrices() {
    $ch = curl_init(BINANCE_API);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (use only if needed)
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return [];
    }

    return $response ? json_decode($response, true) : [];
}

function processAlerts($conn, $apiUrl) {
    $prices = getPrices();
    if (empty($prices)) {
        return;
    }

    $result = mysqli_query($conn, "SELECT * FROM alerts WHERE symbol IS NOT NULL");
    if (!$result) {
        return;
    }

    $alerts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $messageQueue = [];

    foreach ($alerts as $alert) {
        foreach ($prices as $price) {
            if (strcasecmp($price['symbol'], strtoupper($alert['symbol'])) === 0) {
                if (($alert['conditionstate'] === '>' && $price['price'] > $alert['price']) ||
                    ($alert['conditionstate'] === '<' && $price['price'] < $alert['price'])) {
                    
                    $message = "Price alert: {$alert['symbol']} is {$alert['conditionstate']} {$alert['price']} (current: {$price['price']})";
                    $messageQueue[] = ['chat_id' => $alert['chat_id'], 'message' => $message];

                    $updateQuery = "UPDATE alerts SET symbol = NULL WHERE id = " . (int)$alert['id'];
                    mysqli_query($conn, $updateQuery);
                }
            }
        }
    }

    sendMessagesRateLimited($apiUrl, $messageQueue);
}

function sendMessagesRateLimited($apiUrl, $messageQueue) {
    $delay = (1 / TELEGRAM_RATE_LIMIT) * 1000000; // Convert to microseconds

    foreach ($messageQueue as $msg) {
        $url = $apiUrl . "sendMessage";
        $postData = json_encode([
            'chat_id' => $msg['chat_id'],
            'text' => $msg['message']
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

        curl_exec($ch);
        curl_close($ch);
        usleep($delay); // Respect Telegram's rate limits
    }
}

processAlerts($conn, TELEGRAM_API_URL);
mysqli_close($conn);