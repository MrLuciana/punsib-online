<?php
require_once '../../../config/db.php';
require_once '../../../config/functions.php';
require_once '../../config/admin_functions.php';

header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้']);
    exit;
}

// ตรวจสอบข้อมูล
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$customer_id = (int)$_POST['id'];
$status = (int)$_POST['status'];

try {
    // ตรวจสอบว่าลูกค้ามีอยู่จริง
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบลูกค้าที่ต้องการ']);
        exit;
    }

    // อัปเดตสถานะ
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$status, $customer_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
