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

function ensure_upload_dir(string $dir): void {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

function save_uploaded_file(array $file, string $targetDir): ?string {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $originalName = $file['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $safeExt = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? $ext : 'jpg';
    $unique = uniqid('img_', true) . '_' . time();
    $fileName = $unique . '.' . $safeExt;
    $absPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $absPath)) {
        return null;
    }
    // return web-relative path used by frontend and stored in DB
    return '/images/product-images/' . $fileName;
}

function delete_file_if_exists(string $webPath): void {
    $webPath = str_replace('\\', '/', $webPath);
    if ($webPath === '' || $webPath[0] !== '/') return;
    $absolute = APP_ROOT . '/public' . $webPath;
    if (is_file($absolute)) {
        @unlink($absolute);
    }
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $uploadDir = APP_ROOT . '/public/images/product-images';
    ensure_upload_dir($uploadDir);

    if ($method === 'GET') {
        // GET /products.php or /products.php?id=1
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?');
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                respond(404, ['error' => ['Product not found.']]);
            }
            $stmt = $pdo->prepare('SELECT id, image_url FROM product_images WHERE product_id = ? ORDER BY id ASC');
            $stmt->execute([$id]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            respond(200, ['success' => true, 'data' => ['product' => $product, 'images' => $images]]);
        }

        // list with category name and rating fields
        $stmt = $pdo->query('SELECT p.id, p.name, p.price, p.original_price, p.thumbnail, p.stock, p.is_featured, p.is_on_sale, p.is_new_arrival, p.average_rating, p.review_count, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC');
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        respond(200, ['success' => true, 'data' => $products]);
    }

    if ($method === 'POST') {
        $action = $_POST['action'] ?? 'create';

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
            $original_price = isset($_POST['original_price']) && $_POST['original_price'] !== '' ? (float)$_POST['original_price'] : null;
            $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
            $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
            $is_featured = isset($_POST['is_featured']) && $_POST['is_featured'] === 'true' ? 1 : 0;
            $is_on_sale = isset($_POST['is_on_sale']) && $_POST['is_on_sale'] === 'true' ? 1 : 0;
            $is_new_arrival = isset($_POST['is_new_arrival']) && $_POST['is_new_arrival'] === 'true' ? 1 : 0;

            $errors = [];
            if ($name === '') $errors[] = 'Product name is required.';
            if ($price === null) $errors[] = 'Price is required.';
            if ($errors) respond(400, ['error' => $errors]);

            // handle thumbnail (optional but recommended)
            $thumbnailPath = null;
            if (isset($_FILES['thumbnail'])) {
                $thumb = save_uploaded_file($_FILES['thumbnail'], $uploadDir);
                if ($thumb) $thumbnailPath = $thumb;
            }

            $stmt = $pdo->prepare('INSERT INTO products (name, description, price, original_price, thumbnail, stock, category_id, is_featured, is_on_sale, is_new_arrival, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute([
                $name, ($description !== '' ? $description : null), $price, $original_price, $thumbnailPath, $stock, $category_id, $is_featured, $is_on_sale, $is_new_arrival
            ]);
            $productId = (int)$pdo->lastInsertId();

            // additional images
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $count = count($_FILES['images']['name']);
                $imgStmt = $pdo->prepare('INSERT INTO product_images (image_url, product_id) VALUES (?, ?)');
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i] ?? null,
                        'type' => $_FILES['images']['type'][$i] ?? null,
                        'tmp_name' => $_FILES['images']['tmp_name'][$i] ?? null,
                        'error' => $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $_FILES['images']['size'][$i] ?? 0,
                    ];
                    $saved = save_uploaded_file($file, $uploadDir);
                    if ($saved) {
                        $imgStmt->execute([$saved, $productId]);
                    }
                }
            }

            respond(201, ['success' => true, 'message' => 'Product created successfully.', 'data' => ['id' => $productId]]);
        }

        if ($action === 'update') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) respond(400, ['error' => ['Valid product id is required.']]);

            $stmt = $pdo->prepare('SELECT thumbnail FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) respond(404, ['error' => ['Product not found.']]);

            $name = isset($_POST['name']) ? trim($_POST['name']) : null;
            $description = isset($_POST['description']) ? trim($_POST['description']) : null;
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? (float)$_POST['price'] : null;
            $original_price = array_key_exists('original_price', $_POST) ? ($_POST['original_price'] !== '' ? (float)$_POST['original_price'] : null) : null;
            $stock = isset($_POST['stock']) && $_POST['stock'] !== '' ? (int)$_POST['stock'] : null;
            $category_id = array_key_exists('category_id', $_POST) ? ($_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null) : null;
            $is_featured = isset($_POST['is_featured']) ? (($_POST['is_featured'] === 'true') ? 1 : 0) : null;
            $is_on_sale = isset($_POST['is_on_sale']) ? (($_POST['is_on_sale'] === 'true') ? 1 : 0) : null;
            $is_new_arrival = isset($_POST['is_new_arrival']) ? (($_POST['is_new_arrival'] === 'true') ? 1 : 0) : null;

            $fields = [];
            $params = [];
            if ($name !== null) { $fields[] = 'name = ?'; $params[] = $name; }
            if ($description !== null) { $fields[] = 'description = ?'; $params[] = ($description !== '' ? $description : null); }
            if ($price !== null) { $fields[] = 'price = ?'; $params[] = $price; }
            if ($original_price !== null || array_key_exists('original_price', $_POST)) { $fields[] = 'original_price = ?'; $params[] = $original_price; }
            if ($stock !== null) { $fields[] = 'stock = ?'; $params[] = $stock; }
            if (array_key_exists('category_id', $_POST)) { $fields[] = 'category_id = ?'; $params[] = $category_id; }
            if ($is_featured !== null) { $fields[] = 'is_featured = ?'; $params[] = $is_featured; }
            if ($is_on_sale !== null) { $fields[] = 'is_on_sale = ?'; $params[] = $is_on_sale; }
            if ($is_new_arrival !== null) { $fields[] = 'is_new_arrival = ?'; $params[] = $is_new_arrival; }

            // new thumbnail upload
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE) {
                $newThumb = save_uploaded_file($_FILES['thumbnail'], $uploadDir);
                if ($newThumb) {
                    // delete old file if exists
                    if (!empty($existing['thumbnail'])) {
                        delete_file_if_exists($existing['thumbnail']);
                    }
                    $fields[] = 'thumbnail = ?';
                    $params[] = $newThumb;
                }
            }

            if (!empty($fields)) {
                $fields[] = 'updated_at = NOW()';
                $sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?';
                $params[] = $id;
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }

            // delete selected old images
            if (!empty($_POST['delete_image_ids'])) {
                $ids = array_filter(array_map('intval', explode(',', (string)$_POST['delete_image_ids'])));
                if (!empty($ids)) {
                    // fetch paths to delete files
                    $in = implode(',', array_fill(0, count($ids), '?'));
                    $sel = $pdo->prepare("SELECT id, image_url FROM product_images WHERE id IN ($in) AND product_id = ?");
                    $sel->execute([...$ids, $id]);
                    $rows = $sel->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($rows as $row) {
                        delete_file_if_exists($row['image_url']);
                    }
                    $del = $pdo->prepare("DELETE FROM product_images WHERE id IN ($in) AND product_id = ?");
                    $del->execute([...$ids, $id]);
                }
            }

            // append new images
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $count = count($_FILES['images']['name']);
                $imgStmt = $pdo->prepare('INSERT INTO product_images (image_url, product_id) VALUES (?, ?)');
                for ($i = 0; $i < $count; $i++) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i] ?? null,
                        'type' => $_FILES['images']['type'][$i] ?? null,
                        'tmp_name' => $_FILES['images']['tmp_name'][$i] ?? null,
                        'error' => $_FILES['images']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $_FILES['images']['size'][$i] ?? 0,
                    ];
                    $saved = save_uploaded_file($file, $uploadDir);
                    if ($saved) {
                        $imgStmt->execute([$saved, $id]);
                    }
                }
            }

            respond(200, ['success' => true, 'message' => 'Product updated successfully.']);
        }

        if ($action === 'delete') {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id <= 0) respond(400, ['error' => ['Valid product id is required.']]);

            // delete product images and files
            $stmt = $pdo->prepare('SELECT image_url FROM product_images WHERE product_id = ?');
            $stmt->execute([$id]);
            $imgs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($imgs as $img) {
                delete_file_if_exists($img['image_url']);
            }
            $pdo->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$id]);

            // delete thumbnail file if exists
            $stmt = $pdo->prepare('SELECT thumbnail FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['thumbnail'])) {
                delete_file_if_exists($row['thumbnail']);
            }
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);

            respond(200, ['success' => true, 'message' => 'Product deleted successfully.']);
        }

        respond(400, ['error' => ['Invalid action.']]);
    }

    respond(405, ['error' => 'Method not allowed']);
} catch (PDOException $e) {
    respond(500, ['error' => ['Database error.'], 'detail' => $e->getMessage()]);
}


