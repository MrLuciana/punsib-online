<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณาเข้าู่ระบบก่อนแก้ไขตะกร้า'
    ]);
    exit;
}

// ตรวจสอบ Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Method ไม่ถูกต้อง'
    ]);
    exit;
}

// รับข้อมูล
$product_id = $_POST['product_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

// ตรวจสอบข้อมูล
if (!is_numeric($product_id) || $product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID สินค้าไม่ถูกต้อง'
    ]);
    exit;
}

if (!is_numeric($quantity) || $quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'จำนวนไม่ถูกต้อง'
    ]);
    exit;
}

try {
    // ตรวจสอบว่าินค้ามีอยู่จริงและมีสต็อก
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบสินค้านี้'
        ]);
        exit;
    }

    if ($quantity > $product['stock']) {
        echo json_encode([
            'success' => false,
            'message' => 'จำนวนสินค้าในสต็อกไม่เพียงพอ'
        ]);
        exit;
    }

    // อัปเดตจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("
        UPDATE cart 
        SET quantity = ? 
        WHERE user_id = ? AND product_id = ?
    ");
    $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);

    // นับจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount,
        'message' => 'อัปเดตตะกร้าเรียบร้อยแล้ว'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
