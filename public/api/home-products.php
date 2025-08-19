<?php
// CORS headers for cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET, OPTIONS');
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
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        respond(405, ['error' => 'Method not allowed']);
    }

    $type = $_GET['type'] ?? 'featured';
    
    if ($type === 'featured') {
        // Get featured products with category name
        $stmt = $pdo->query('SELECT p.id, p.name, p.price, p.original_price, p.thumbnail, p.is_featured, p.is_on_sale, p.is_new_arrival, p.average_rating, p.review_count, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 ORDER BY p.created_at DESC LIMIT 8');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'new_arrivals') {
        // Get new arrivals with category name
        $stmt = $pdo->query('SELECT p.id, p.name, p.price, p.original_price, p.thumbnail, p.is_featured, p.is_on_sale, p.is_new_arrival, p.average_rating, p.review_count, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_new_arrival = 1 ORDER BY p.created_at DESC LIMIT 8');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'sale') {
        // Get sale products with category name
        $stmt = $pdo->query('SELECT p.id, p.name, p.price, p.original_price, p.thumbnail, p.is_featured, p.is_on_sale, p.is_new_arrival, p.average_rating, p.review_count, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_on_sale = 1 ORDER BY p.created_at DESC LIMIT 8');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        respond(400, ['error' => 'Invalid type parameter']);
    }

    // Transform data to match frontend format
    $transformedProducts = array_map(function($product) {
        return [
            'id' => (string)$product['id'],
            'name' => $product['name'],
            'price' => (float)$product['price'],
            'originalPrice' => isset($product['original_price']) && $product['original_price'] !== null ? (float)$product['original_price'] : null,
            'image' => $product['thumbnail'] ?: '/placeholder.svg?height=300&width=300',
            'category' => $product['category_name'] ?: 'Other',
            'rating' => isset($product['average_rating']) ? (float)$product['average_rating'] : 4.5,
            'reviews' => isset($product['review_count']) ? (int)$product['review_count'] : 0,
            'featured' => (bool)$product['is_featured'],
            'sale' => (bool)$product['is_on_sale'],
            'newArrival' => (bool)$product['is_new_arrival']
        ];
    }, $products);

    respond(200, ['success' => true, 'data' => $transformedProducts]);

} catch (PDOException $e) {
    respond(500, ['error' => 'Database error', 'detail' => $e->getMessage()]);
} catch (Exception $e) {
    respond(500, ['error' => 'Server error', 'detail' => $e->getMessage()]);
}
