<?php
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once APP_ROOT . '/config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$errors = [];
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (!$password) $errors[] = 'Password is required.';

if ($errors) {
    http_response_code(400);
    echo json_encode(['error' => $errors]);
    exit;
}

// Check user
$stmt = $pdo->prepare('SELECT id, first_name, last_name, email, phone, date_of_birth, gender, role, address_street, address_ward, address_city, address_country, created_at, updated_at, password FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => ['Invalid email or password.']]);
    exit;
}

unset($user['password']);

echo json_encode([
    'success' => true,
    'message' => 'Login successful.',
    'user' => $user
]);
