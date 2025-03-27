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
$cart_id = $_POST['cart_id'] ?? 0;

// ตรวจสอบข้อมูล
if (!is_numeric($cart_id) || $cart_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID ตะกร้าไม่ถูกต้อง'
    ]);
    exit;
}

try {
    // ดึงข้อมูลสินค้าในตะกร้า
    $stmt = $conn->prepare("
        SELECT c.id, c.product_id 
        FROM cart c
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบสินค้าในตะกร้า'
        ]);
        exit;
    }

    // ลบสินค้าอกจากตะกร้า
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$cart_id]);

    // นับจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount,
        'product_id' => $cartItem['product_id'],
        'message' => 'ลบสินค้าอกจากตะกร้าเรียบร้อยแล้ว'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
