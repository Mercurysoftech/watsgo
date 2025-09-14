<?php
// src/auth.php
declare(strict_types=1);
require_once __DIR__.'/db.php';
require_once __DIR__.'/helpers.php';
require_once __DIR__.'/config.php';

function register_user(string $name, string $email, string $password, string $whatsapp): ?array {
    $pdo = db();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = uuidv4();
    $secret = random_hex(32); // 64 hex chars

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, whatsapp, webhook_token, webhook_secret) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$name, strtolower($email), $hash, $whatsapp, $token, $secret]);

    $id = (int)$pdo->lastInsertId();
    return find_user_by_id($id);
}

function find_user_by_email(string $email): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE email=?');
    $stmt->execute([strtolower($email)]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function find_user_by_id(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$id]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function find_user_by_token(string $token): ?array {
    $stmt = db()->prepare('SELECT * FROM users WHERE webhook_token=?');
    $stmt->execute([$token]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function login_user(string $email, string $password): ?array {
    $u = find_user_by_email($email);
    if ($u && password_verify($password, $u['password_hash'])) {
        session_start(); $_SESSION['user'] = $u; return $u;
    }
    return null;
}
