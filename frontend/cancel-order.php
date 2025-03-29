<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบการเข้าู่ระบบ
if (!isLoggedIn()) {
    setAlert('warning', 'กรุณาเข้าู่ระบบก่อนดำเนินการ');
    redirect('login.php');
}

// ตรวจสอบ CSRF Token
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    setAlert('error', 'โทเคนไม่ถูกต้อง');
    redirect('orders.php');
}

// ตรวจสอบว่าีการส่ง order_id มา
if (!isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    setAlert('error', 'ไม่พบรายการสั่งซื้อที่ระบุ');
    redirect('orders.php');
}

$orderId = (int)$_POST['order_id'];

// ดึงข้อมูลออร์เดอร์
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE id = :order_id 
    AND user_id = :user_id 
    AND order_status = 'pending'
    AND payment_status = 'pending'
");
$stmt->execute([
    ':order_id' => $orderId,
    ':user_id' => $_SESSION['user_id']
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่าบออร์เดอร์และสามารถยกเลิกได้
if (!$order) {
    setAlert('error', 'ไม่พบรายการสั่งซื้อที่ระบุหรือไม่สามารถยกเลิกได้');
    redirect('orders.php');
}

// เริ่ม Transaction
$conn->beginTransaction();

try {
    // อัปเดตสถานะออร์เดอร์เป็น cancelled
    $stmt = $conn->prepare("
        UPDATE orders 
        SET order_status = 'cancelled', 
            updated_at = NOW() 
        WHERE id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);

    // คืนสินค้าเข้าต็อก
    $stmt = $conn->prepare("
        SELECT product_id, quantity 
        FROM order_items 
        WHERE order_id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orderItems as $item) {
        $stmt = $conn->prepare("
            UPDATE products 
            SET stock = stock + :quantity 
            WHERE id = :product_id
        ");
        $stmt->execute([
            ':quantity' => $item['quantity'],
            ':product_id' => $item['product_id']
        ]);
    }

    // บันทึกการยกเลิกใน revenue (ถ้ามี)
    $stmt = $conn->prepare("
        DELETE FROM revenue 
        WHERE order_id = :order_id
    ");
    $stmt->execute([':order_id' => $orderId]);

    // Commit transaction
    $conn->commit();

    setAlert('success', 'ยกเลิกการสั่งซื้อเรียบร้อยแล้ว');
    redirect('order-detail.php?id=' . $orderId);
} catch (PDOException $e) {
    // Rollback transaction หากเกิดข้อผิดพลาด
    $conn->rollBack();
    
    setAlert('error', 'เกิดข้อผิดพลาดในการยกเลิกการสั่งซื้อ', $e->getMessage());
    redirect('order-detail.php?id=' . $orderId);
}
