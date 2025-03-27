<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

// ตรวจสอบข้อมูล
if (!isset($_POST['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$cart_id = (int)$_POST['cart_id'];

try {
    // ดึงข้อมูลสินค้าในตะกร้า
    $stmt = $conn->prepare("SELECT product_id FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบสินค้าในตะกร้า']);
        exit;
    }

    // ลบสินค้า
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
    $stmt->execute([$cart_id]);

    // นับจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'success' => true, 
        'cart_count' => $cartCount,
        'product_id' => $cartItem['product_id']
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
