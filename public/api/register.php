<?php
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

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

require_once '../../config/database.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$firstName = trim($data['firstName'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';
$agree = $data['agree'] ?? false;

// Validate
$errors = [];
if (!$firstName) $errors[] = 'First name is required.';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (!$phone) $errors[] = 'Phone number is required.';
if (!$password || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
if (!$agree) $errors[] = 'You must agree to the terms.';

if ($errors) {
    http_response_code(400);
    echo json_encode(['error' => $errors]);
    exit;
}

// Check if email exists
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => ['Email already exists.']]);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$stmt = $pdo->prepare('INSERT INTO users (first_name, email, phone, password) VALUES (?, ?, ?, ?)');
try {
    $stmt->execute([$firstName, $email, $phone, $hashedPassword]);
    echo json_encode(['success' => true, 'message' => 'Registration successful.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => ['Registration failed.']]);
}
