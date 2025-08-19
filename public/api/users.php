<?php
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once APP_ROOT . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function respond($statusCode, $payload) {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.role, u.created_at FROM users u WHERE u.id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                respond(404, ['error' => ['User not found.']]);
            }
            $agg = $pdo->prepare('SELECT COUNT(o.id) AS orders_count, COALESCE(SUM(o.total), 0) AS total_spent FROM orders o WHERE o.user_id = ?');
            $agg->execute([$id]);
            $summary = $agg->fetch(PDO::FETCH_ASSOC) ?: ['orders_count' => 0, 'total_spent' => 0];

            respond(200, [
                'success' => true,
                'data' => [
                    'id' => (int)$user['id'],
                    'name' => trim($user['first_name'] . ' ' . ($user['last_name'] ?? '')),
                    'email' => $user['email'],
                    'phone' => $user['phone'],
                    'role' => $user['role'],
                    'joined' => $user['created_at'],
                    'orders' => (int)$summary['orders_count'],
                    'total_spent' => (float)$summary['total_spent'],
                ],
            ]);
        }

        // list users with aggregates
        $sql = 'SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.role, u.created_at,
                       COALESCE(SUM(o.total), 0) AS total_spent, COUNT(o.id) AS orders_count
                FROM users u
                LEFT JOIN orders o ON o.user_id = u.id
                GROUP BY u.id
                ORDER BY u.id DESC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array_map(function($u) {
            return [
                'id' => (int)$u['id'],
                'name' => trim($u['first_name'] . ' ' . ($u['last_name'] ?? '')),
                'email' => $u['email'],
                'phone' => $u['phone'],
                'role' => $u['role'],
                'joined' => $u['created_at'],
                'orders' => (int)$u['orders_count'],
                'total_spent' => (float)$u['total_spent'],
            ];
        }, $rows);

        respond(200, ['success' => true, 'data' => $users]);
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            respond(400, ['error' => ['Invalid JSON body.']]);
        }
        $action = $data['action'] ?? '';

        if ($action === 'update') {
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if ($id <= 0) respond(400, ['error' => ['Valid user id is required.']]);
            $first_name = isset($data['first_name']) ? trim($data['first_name']) : null;
            $last_name = array_key_exists('last_name', $data) ? trim((string)$data['last_name']) : null;
            $email = isset($data['email']) ? trim($data['email']) : null;
            $phone = isset($data['phone']) ? trim($data['phone']) : null;
            $role = isset($data['role']) ? trim($data['role']) : null; // 'admin' or 'user'

            $fields = [];
            $params = [];
            if ($first_name !== null) { $fields[] = 'first_name = ?'; $params[] = $first_name; }
            if ($last_name !== null) { $fields[] = 'last_name = ?'; $params[] = ($last_name !== '' ? $last_name : null); }
            if ($email !== null) { $fields[] = 'email = ?'; $params[] = $email; }
            if ($phone !== null) { $fields[] = 'phone = ?'; $params[] = $phone; }
            if ($role !== null) { $fields[] = 'role = ?'; $params[] = ($role === 'admin' ? 'admin' : 'user'); }
            if (empty($fields)) respond(400, ['error' => ['No fields to update.']]);
            $fields[] = 'updated_at = NOW()';
            $params[] = $id;
            $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            respond(200, ['success' => true, 'message' => 'User updated successfully.']);
        }

        if ($action === 'delete') {
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            if ($id <= 0) respond(400, ['error' => ['Valid user id is required.']]);
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
            respond(200, ['success' => true, 'message' => 'User deleted successfully.']);
        }

        respond(400, ['error' => ['Invalid action.']]);
    }

    respond(405, ['error' => 'Method not allowed']);
} catch (PDOException $e) {
    respond(500, ['error' => 'Database error', 'detail' => $e->getMessage()]);
} catch (Exception $e) {
    respond(500, ['error' => 'Server error', 'detail' => $e->getMessage()]);
}


