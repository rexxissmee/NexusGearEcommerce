<?php
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once APP_ROOT . '/config/database.php';

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function get_json_body() {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all categories
        $stmt = $pdo->query("SELECT id, name, description, status, created_at FROM categories ORDER BY id DESC");
        $categories = $stmt->fetchAll();
        respond(200, [
            'success' => true,
            'data' => $categories,
        ]);
    }

    if ($method === 'POST') {
        $data = get_json_body();
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = $data['status'] ?? 'active';

        $errors = [];
        if ($name === '') { $errors[] = 'Category name is required.'; }
        if (!in_array($status, ['active', 'inactive'], true)) { $errors[] = 'Invalid status.'; }

        if ($errors) {
            respond(400, ['error' => $errors]);
        }

        // Insert
        $stmt = $pdo->prepare('INSERT INTO categories (name, description, status, created_at) VALUES (?, ?, ?, CURDATE())');
        $stmt->execute([$name, $description !== '' ? $description : null, $status]);
        $id = (int)$pdo->lastInsertId();

        respond(201, [
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => [
                'id' => $id,
                'name' => $name,
                'description' => $description !== '' ? $description : null,
                'status' => $status,
                'created_at' => date('Y-m-d'),
            ],
        ]);
    }

    if ($method === 'PUT' || $method === 'PATCH') {
        $data = get_json_body();
        $id = isset($data['id']) ? (int)$data['id'] : 0;
        $name = isset($data['name']) ? trim($data['name']) : null;
        $description = array_key_exists('description', $data) ? trim((string)$data['description']) : null;
        $status = $data['status'] ?? null;

        $errors = [];
        if ($id <= 0) { $errors[] = 'Valid category id is required.'; }
        if ($status !== null && !in_array($status, ['active', 'inactive'], true)) { $errors[] = 'Invalid status.'; }
        if ($name !== null && $name === '') { $errors[] = 'Category name cannot be empty.'; }

        if ($errors) {
            respond(400, ['error' => $errors]);
        }

        // Build dynamic query
        $fields = [];
        $params = [];
        if ($name !== null) { $fields[] = 'name = ?'; $params[] = $name; }
        if ($description !== null) { $fields[] = 'description = ?'; $params[] = ($description !== '' ? $description : null); }
        if ($status !== null) { $fields[] = 'status = ?'; $params[] = $status; }

        if (empty($fields)) {
            respond(400, ['error' => ['No fields to update.']]);
        }

        $params[] = $id;
        $sql = 'UPDATE categories SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        respond(200, [
            'success' => true,
            'message' => 'Category updated successfully.',
        ]);
    }

    if ($method === 'DELETE') {
        // id from query string or body
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $data = get_json_body();
            $id = isset($data['id']) ? (int)$data['id'] : 0;
        }

        if ($id <= 0) {
            respond(400, ['error' => ['Valid category id is required.']]);
        }

        $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
        $stmt->execute([$id]);

        respond(200, [
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }

    respond(405, ['error' => 'Method not allowed']);
} catch (PDOException $e) {
    // Handle unique constraint and other DB errors
    $message = $e->getCode() === '23000' ? 'Category name already exists.' : 'Database error.';
    respond(500, ['error' => [$message], 'detail' => $e->getMessage()]);
}


