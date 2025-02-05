<?php

require_once 'config.php';
include_once 'cronjob.php';

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$pairs = getPrices();
if (!empty($pairs)) {


    foreach ($pairs as $pair) {
        $symbol = $pair['symbol'];


        $checkQuery = "SELECT id FROM available_pairs WHERE symbol = '$symbol'";
        $result = mysqli_query($conn, $checkQuery);

        if (mysqli_num_rows($result) === 0) {
            mysqli_query($conn, "INSERT INTO available_pairs (symbol) VALUES ('$symbol')");
        }
    }
}

mysqli_close($conn);
?>