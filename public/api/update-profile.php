<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once APP_ROOT . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'] ?? null;
$first_name = trim($data['first_name'] ?? '');
$last_name = trim($data['last_name'] ?? '');
$phone = trim($data['phone'] ?? '');
$date_of_birth = $data['date_of_birth'] ?? null;
$gender = $data['gender'] ?? null;
$address_street = trim($data['address_street'] ?? '');
$address_ward = trim($data['address_ward'] ?? '');
$address_city = trim($data['address_city'] ?? '');
$address_country = trim($data['address_country'] ?? '');

$errors = [];
if (!$id) $errors[] = 'User ID is required.';
if (!$first_name) $errors[] = 'First name is required.';
if (!$last_name) $errors[] = 'Last name is required.';

if ($errors) {
    http_response_code(400);
    echo json_encode(['error' => $errors]);
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ?, date_of_birth = ?, gender = ?, address_street = ?, address_ward = ?, address_city = ?, address_country = ?, updated_at = NOW() WHERE id = ?');
$success = $stmt->execute([$first_name, $last_name, $phone, $date_of_birth, $gender, $address_street, $address_ward, $address_city, $address_country, $id]);

if (!$success) {
    http_response_code(500);
    echo json_encode(['error' => ['Failed to update user.']]);
    exit;
}

$stmt = $pdo->prepare('SELECT id, first_name, last_name, email, phone, date_of_birth, gender, role, address_street, address_ward, address_city, address_country, created_at, updated_at FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => ['User not found.']]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Profile updated successfully.',
    'user' => $user
]);
