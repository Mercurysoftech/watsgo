<?php
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/auth.php';
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    $password = trim($input['password'] ?? '');

    if ($password === '') {
        throw new Exception('Password not provided');
    }

    // Check if user is logged in
    $user_id = $_SESSION['user_id'] ?? 0;
    if (!$user_id) {
        throw new Exception('User not logged in');
    }

    // Fetch user from database
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Verify password_hash
    if (password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>