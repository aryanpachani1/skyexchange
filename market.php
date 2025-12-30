<?php

// ===== BETFAIR CREDENTIALS =====
$APP_KEY = "ZWs1EcMsiE8a0eTM";
$SESSION = "a+/qwS8bfmZEAlM4j+/qBoyh2AEbeyy6MulGIfra=";

// ===== MARKET ID (from frontend) =====
if(!isset($_GET['marketId'])){
  die(json_encode(["error"=>"marketId missing"]));
}

$marketId = $_GET['marketId'];

// ===== BETFAIR JSON-RPC PAYLOAD =====
$payload = json_encode([
  "jsonrpc" => "2.0",
  "method" => "SportsAPING/v1.0/listMarketBook",
  "params" => [
    "marketIds" => [$marketId],
    "priceProjection" => [
      "priceData" => ["EX_BEST_OFFERS"]
    ]
  ],
  "id" => 1
]);

// ===== CURL CALL =====
$ch = curl_init("https://api.betfair.com/exchange/betting/json-rpc/v1");

curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    "X-Application: $APP_KEY",
    "X-Authentication: $SESSION",
    "Content-Type: application/json"
  ],
  CURLOPT_POSTFIELDS => $payload
]);

$response = curl_exec($ch);

if(curl_errno($ch)){
  die(json_encode(["error"=>curl_error($ch)]));
}

curl_close($ch);

// ===== RESPONSE PARSE =====
$data = json_decode($response, true);

if(!isset($data['result'][0]['runners'])){
  die(json_encode(["error"=>"Invalid response","raw"=>$data]));
}

$runners = $data['result'][0]['runners'];

$out = ["runners"=>[]];

foreach($runners as $r){
  $out["runners"][] = [
    "selectionId" => $r["selectionId"],
    "back" => array_slice($r["ex"]["availableToBack"], 0, 3),
    "lay"  => array_slice($r["ex"]["availableToLay"], 0, 3)
  ];
}

// ===== OUTPUT =====
header("Content-Type: application/json");
echo json_encode($out);
