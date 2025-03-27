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

try {
    // ลบสินค้าั้งหมดในตะกร้า
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'cart_count' => 0,
        'message' => 'ล้างตะกร้าเรียบร้อยแล้ว'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
