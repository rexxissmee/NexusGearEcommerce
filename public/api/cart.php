<?php
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/app.php';
require_once APP_ROOT . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

function respond($code, $payload) {
  http_response_code($code);
  echo json_encode($payload);
  exit;
}

try {
  $method = $_SERVER['REQUEST_METHOD'];

  if ($method === 'GET') {
    $userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
    if ($userId <= 0) respond(400, ['error' => ['Valid user_id is required.']]);
    $sql = 'SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price, p.original_price, p.thumbnail, c.name AS category_name
            FROM cart_items ci
            JOIN products p ON p.id = ci.product_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE ci.user_id = ?
            ORDER BY ci.id DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $items = array_map(function($r) {
      return [
        'cart_item_id' => (int)$r['id'],
        'id' => (string)$r['product_id'],
        'name' => $r['name'],
        'price' => (float)$r['price'],
        'originalPrice' => $r['original_price'] !== null ? (float)$r['original_price'] : null,
        'image' => $r['thumbnail'] ?: '/placeholder.svg?height=300&width=300',
        'category' => $r['category_name'] ?: 'Other',
        'quantity' => (int)$r['quantity'],
      ];
    }, $rows);
    respond(200, ['success' => true, 'data' => $items]);
  }

  if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) respond(400, ['error' => ['Invalid body']]);
    $action = $data['action'] ?? 'add';
    $userId = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    if ($userId <= 0) respond(400, ['error' => ['Valid user_id is required.']]);

    if ($action === 'add') {
      $productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
      $quantity = isset($data['quantity']) ? max(1, (int)$data['quantity']) : 1;
      if ($productId <= 0) respond(400, ['error' => ['Valid product_id is required.']]);
      // upsert by unique (user_id, product_id)
      $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
      $stmt->execute([$userId, $productId, $quantity]);
      respond(200, ['success' => true, 'message' => 'Added to cart']);
    }

    if ($action === 'update') {
      $cartItemId = isset($data['cart_item_id']) ? (int)$data['cart_item_id'] : 0;
      $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;
      if ($cartItemId <= 0 || $quantity <= 0) respond(400, ['error' => ['Valid cart_item_id and quantity are required.']]);
      $stmt = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?');
      $stmt->execute([$quantity, $cartItemId, $userId]);
      respond(200, ['success' => true, 'message' => 'Cart updated']);
    }

    if ($action === 'remove') {
      $cartItemId = isset($data['cart_item_id']) ? (int)$data['cart_item_id'] : 0;
      if ($cartItemId <= 0) respond(400, ['error' => ['Valid cart_item_id is required.']]);
      $stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
      $stmt->execute([$cartItemId, $userId]);
      respond(200, ['success' => true, 'message' => 'Item removed']);
    }

    if ($action === 'clear') {
      $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
      $stmt->execute([$userId]);
      respond(200, ['success' => true, 'message' => 'Cart cleared']);
    }

    respond(400, ['error' => ['Invalid action']]);
  }

  respond(405, ['error' => 'Method not allowed']);
} catch (PDOException $e) {
  respond(500, ['error' => ['Database error'], 'detail' => $e->getMessage()]);
}


