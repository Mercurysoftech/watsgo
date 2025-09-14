<?php
// src/db.php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // Detect environment
        $isLocal = in_array($_SERVER['SERVER_NAME'] ?? 'cli', ['127.0.0.1', 'localhost']);

        if ($isLocal) {
            // Local DB config
            $host = "127.0.0.1";
            $dbname = "mercurysoftech_whatsapp";
            $user = "root";
            $pass = "";
        } else {
            // Live server DB config
            $host = "localhost"; // or your live DB host
            $dbname = "MercurySoftech_whatsappbot";
            $user = "MercurySoftech_whatsappbot";
            $pass = "Mercury@2025";
        }

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function envv(string $k, $default=null) {
    return $default; // not using .env anymore
}

