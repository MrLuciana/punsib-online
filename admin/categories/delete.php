<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

// ตรวจสอบว่ามี ID ที่ส่งมาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบหมวดหมู่ที่ต้องการลบ');
    redirect(BASE_URL . '/admin/categories/list.php');
}

$categoryId = (int)$_GET['id'];

// ตรวจสอบว่าหมวดหมู่มีสินค้าเกี่ยวข้องหรือไม่
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $productCount = $stmt->fetchColumn();

    if ($productCount > 0) {
        setAlert('danger', 'ไม่สามารถลบหมวดหมู่ได้ เนื่องจากมีสินค้าในหมวดหมู่นี้');
        redirect(BASE_URL . '/admin/categories/list.php');
    }

    // ดึงข้อมูลหมวดหมู่เพื่อแสดงชื่อในข้อความแจ้งเตือน
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        setAlert('danger', 'ไม่พบหมวดหมู่ที่ต้องการลบ');
        redirect(BASE_URL . '/admin/categories/list.php');
    }

    // ลบหมวดหมู่
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);

    setAlert('success', 'ลบหมวดหมู่ "' . htmlspecialchars($category['name']) . '" เรียบร้อยแล้ว');
} catch (PDOException $e) {
    setAlert('danger', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
}

redirect(BASE_URL . '/admin/categories/list.php');
