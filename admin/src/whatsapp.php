<?php
// src/whatsapp.php
declare(strict_types=1);
require_once __DIR__.'/db.php';

function wa_send_text($phone, $message, $wa_phone_id=null, $wa_token=null) {
    if (!$wa_phone_id || !$wa_token) {
        return false;
    }
    $url = "https://graph.facebook.com/v20.0/{$wa_phone_id}/messages";
    $payload = [
        "messaging_product" => "whatsapp",
        "to" => $phone,
        "type" => "text",
        "text" => ["body" => $message]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$wa_token}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status >= 200 && $status < 300;
}
