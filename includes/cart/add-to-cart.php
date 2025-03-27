<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';

header('Content-Type: application/json');

// ตรวจสอบ CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Token ไม่ถูกต้อง',
        'login_required' => false
    ]);
    exit;
}

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'กรุณาเข้าสู่ระบบก่อนเพิ่มสินค้า',
        'login_required' => true
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
    // ตรวจสอบว่าสินค้ามีอยู่จริงและมีสต็อก
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM products p 
        WHERE p.id = ? AND p.status = 1
        FOR UPDATE
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบสินค้านี้'
        ]);
        exit;
    }

    if ($product['stock'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'สินค้าหมดสต็อก'
        ]);
        exit;
    }

    // ตรวจสอบว่ามีสินค้านี้ในตะกร้าแล้วหรือไม่
    $stmt = $conn->prepare("
        SELECT * 
        FROM cart 
        WHERE user_id = ? AND product_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingItem) {
        // ตรวจสอบว่าจำนวนที่ต้องการเพิ่มจะเกินสต็อกหรือไม่
        $newQuantity = $existingItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'จำนวนสินค้าในสต็อกไม่เพียงพอ'
            ]);
            exit;
        }
        
        // อัปเดตจำนวนสินค้า
        $stmt = $conn->prepare("
            UPDATE cart 
            SET quantity = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$newQuantity, $existingItem['id']]);
    } else {
        // เพิ่มสินค้าใหม่
        $stmt = $conn->prepare("
            INSERT INTO cart (user_id, product_id, quantity) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
        
        // ลดสต็อกสินค้า (ถ้าต้องการ)
        // $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        // $stmt->execute([$quantity, $product_id]);
    }

    // นับจำนวนสินค้าในตะกร้า
    $stmt = $conn->prepare("
        SELECT SUM(quantity) as total 
        FROM cart 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cartCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
        'success' => true,
        'cart_count' => $cartCount,
        'message' => 'เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
