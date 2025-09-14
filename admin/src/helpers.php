<?php
// src/helpers.php
declare(strict_types=1);

function uuidv4(): string {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function random_hex(int $bytes = 32): string {
    return bin2hex(random_bytes($bytes));
}

function require_login(): array {
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: /index.php');
        exit;
    }
    return $_SESSION['user'];
}

function set_flash(string $msg) {
    $_SESSION['flash'] = $msg;
}

function get_flash(): ?string {
    if (!empty($_SESSION['flash'])) {
        $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m;
    }
    return null;
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
