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
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// ตรวจสอบว่าสินค้ามีอยู่จริง
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบสินค้านี้']);
    exit;
}

// ตรวจสอบสต็อก
if ($quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'มีสินค้าในสต็อกเพียง ' . $product['stock'] . ' ชิ้น']);
    exit;
}

// อัปเดตตะกร้า
try {
    // ตรวจสอบว่าสินค้ามีในตะกร้าแล้วหรือไม่
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // อัปเดตจำนวน
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $cartItem['id']]);
    } else {
        // เพิ่มสินค้าใหม่
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
    }

    // นับจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode(['success' => true, 'cart_count' => $cartCount]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
