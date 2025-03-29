<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL . 'login.php');
}

// ตรวจสอบว่ามี ID สินค้าหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบสินค้าที่ต้องการลบ');
    redirect(BASE_URL . 'admin/products/list.php');
}

$productId = intval($_GET['id']);

try {
    // ดึงข้อมูลสินค้าเพื่อลบรูปภาพ
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = :id");
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        // ลบรูปภาพถ้ามี
        if ($product['image'] && file_exists(BASE_URL . "uploads/products/" . $product['image'])) {
            unlink(BASE_URL . "uploads/products/" . $product['image']);
        }
        
        // ลบข้อมูลสินค้า
        $stmt = $conn->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $productId]);
        
        setAlert('success', 'ลบสินค้าเรียบร้อยแล้ว');
    } else {
        setAlert('danger', 'ไม่พบสินค้าที่ต้องการลบ');
    }
} catch (Exception $e) {
    setAlert('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
}

redirect(BASE_URL . 'admin/products/list.php');
?>
