<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    setAlert('danger', 'คำขอไม่ถูกต้อง');
    redirect('list.php');
}

$orderId = $_POST['order_id'];
$orderStatus = $_POST['order_status'] ?? 'pending';
$paymentStatus = $_POST['payment_status'] ?? 'pending';

// อัปเดตสถานะคำสั่งซื้อ
$sql = "UPDATE orders 
        SET order_status = ?, 
            payment_status = ?, 
            updated_at = NOW() 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$success = $stmt->execute([$orderStatus, $paymentStatus, $orderId]);

if ($success) {
    // ถ้าสถานะเปลี่ยนเป็น completed ให้บันทึกรายได้
    if ($orderStatus === 'completed') {
        // ดึงข้อมูลคำสั่งซื้อ
        $orderSql = "SELECT * FROM orders WHERE id = ?";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // ดึงรายการสินค้าในคำสั่งซื้อ
            $itemsSql = "SELECT oi.*, p.category_id 
                         FROM order_items oi
                         JOIN products p ON oi.product_id = p.id
                         WHERE oi.order_id = ?";
            $itemsStmt = $conn->prepare($itemsSql);
            $itemsStmt->execute([$orderId]);
            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            // บันทึกรายได้สำหรับแต่ละสินค้า
            foreach ($items as $item) {
                // คำนวณส่วนลด (ถ้ามี)
                $discountAmount = $item['price'] - $item['total_price'];
                
                $insertRevenueSql = "INSERT INTO revenue 
                    (date, order_id, product_id, category_id, amount, 
                     payment_method, source, discount_amount, 
                     shipping_fee, tax_amount, net_amount, notes) 
                    VALUES 
                    (CURDATE(), ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?)";
                
                $insertStmt = $conn->prepare($insertRevenueSql);
                $insertStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['category_id'],
                    $item['total_price'],
                    $order['payment_method'],
                    'online', // เราใส่เป็น online เนื่องจากเป็นคำสั่งซื้อออนไลน์
                    $discountAmount,
                    $item['total_price'], // net_amount ในที่นี้เท่ากับ total_price เพราะไม่คิด shipping และ tax
                    'รายได้จากคำสั่งซื้อ #' . $order['order_number']
                ]);
            }
        }
    }

    setAlert('success', 'อัปเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว');
} else {
    setAlert('danger', 'เกิดข้อผิดพลาดในการอัปเดตสถานะ');
}

redirect('view.php?id=' . $orderId);
?>
