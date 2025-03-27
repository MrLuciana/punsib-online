<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบการล็อกอิน
if (!isLoggedIn()) {
    setAlert('danger', 'กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ');
    redirect('login.php?redirect=checkout');
}

// ตรวจสอบว่าีการส่งฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setAlert('danger', 'วิธีการส่งข้อมูลไม่ถูกต้อง');
    redirect('checkout.php');
}

// ดึงข้อมูลตะกร้าินค้า
$stmt = $conn->prepare("
    SELECT p.id, p.name, p.price, p.discount_price, c.quantity 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ? AND p.status = 1
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    setAlert('warning', 'ไม่มีสินค้าในตะกร้า');
    redirect('cart.php');
}

// ตรวจสอบข้อมูลจากฟอร์ม
$requiredFields = ['fullname', 'phone', 'address', 'province', 'district', 'postal_code', 'shipping_method', 'payment_method'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        setAlert('danger', 'กรุณากรอกข้อมูลให้ครบถ้วน');
        redirect('checkout.php');
    }
}

// คำนวณราารวม
$subtotal = 0;
foreach ($cartItems as $item) {
    $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shippingFee = $_POST['shipping_method'] === 'express' ? 100 : 50;
$total = $subtotal + $shippingFee;

// สร้างเลขที่คำสั่งซื้อ
$orderNumber = 'PO-' . date('Ymd') . '-' . strtoupper(uniqid());

// เริ่ม Transaction
$conn->beginTransaction();

try {
    // บันทึกคำสั่งซื้อ
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, order_number, total_amount, payment_method, shipping_address) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $shippingAddress = $_POST['address'] . ', ' . $_POST['district'] . ', ' . $_POST['province'] . ' ' . $_POST['postal_code'];
    
    $stmt->execute([
        $_SESSION['user_id'],
        $orderNumber,
        $total,
        $_POST['payment_method'],
        $shippingAddress
    ]);
    
    $orderId = $conn->lastInsertId();
    
    // บันทึกรายการสินค้า
    $stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price, total_price) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    foreach ($cartItems as $item) {
        $price = $item['discount_price'] > 0 ? $item['discount_price'] : $item['price'];
        $totalPrice = $price * $item['quantity'];
        
        $stmt->execute([
            $orderId,
            $item['id'],
            $item['quantity'],
            $price,
            $totalPrice
        ]);
        
        // ลดสต็อกสินค้า
        $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
             ->execute([$item['quantity'], $item['id']]);
    }
    
    // ลบสินค้าในตะกร้า
    $conn->prepare("DELETE FROM cart WHERE user_id = ?")
         ->execute([$_SESSION['user_id']]);
    
    // Commit Transaction
    $conn->commit();
    
    // บันทึกรายได้
    $stmt = $conn->prepare("
        INSERT INTO revenue (date, order_id, amount, payment_method, shipping_fee, net_amount) 
        VALUES (CURDATE(), ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $orderId,
        $subtotal,
        $_POST['payment_method'],
        $shippingFee,
        $total
    ]);
    
    // ส่งอีเมลยืนยันการสั่งซื้อ (สามารถเพิ่มโค้ดนี้ในภายหลัง)
    
    setAlert('success', 'สั่งซื้อสินค้าเรียบร้อยแล้ว เลขที่คำสั่งซื้อ: ' . $orderNumber);
    redirect('order_confirmation.php?order_id=' . $orderId);
    
} catch (PDOException $e) {
    // Rollback Transaction หากเกิดข้อผิดพลาด
    $conn->rollBack();
    setAlert('danger', 'เกิดข้อผิดพลาดในการสั่งซื้อ: ' . $e->getMessage());
    redirect('checkout.php');
}
