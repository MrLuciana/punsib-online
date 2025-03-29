<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "ยืนยันคำสั่งซื้อ - ร้านขนมปั้นสิบยายนิดพัทลุง";

// Check if order number exists in session
if (!isset($_SESSION['order_number'])) {
    setAlert('warning', 'ไม่พบข้อมูลคำสั่งซื้อ');
    redirect('products.php');
}

$orderNumber = $_SESSION['order_number'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.fullname, u.email, u.phone 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.order_number = ?
");
$stmt->execute([$orderNumber]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setAlert('danger', 'ไม่พบข้อมูลคำสั่งซื้อ');
    redirect('products.php');
}

// Get order items
$itemsStmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemsStmt->execute([$order['id']]);
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate shipping fee
$shippingFee = $order['total_amount'] >= 500 ? 0 : 50;
$subtotal = $order['total_amount'] - $shippingFee;

// Clear order number from session to prevent refresh issues
unset($_SESSION['order_number']);

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                </div>
                <h1 class="mb-3">คำสั่งซื้อของคุณสำเร็จแล้ว!</h1>
                <p class="lead">ขอบคุณที่สั่งซื้อสินค้ากับเรา หมายเลขคำสั่งซื้อของคุณคือ</p>
                <h3 class="text-primary"><?= htmlspecialchars($order['order_number']) ?></h3>
                
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-success">
                        <i class="fas fa-file-invoice me-2"></i>ดูรายละเอียดคำสั่งซื้อ
                    </a>
                    <a href="products.php" class="btn btn-success">
                        <i class="fas fa-shopping-bag me-2"></i>ช้อปปิ้งต่อ
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-5">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>ข้อมูลคำสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">ข้อมูลลูกค้า</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>ชื่อ:</strong> <?= htmlspecialchars($order['fullname']) ?>
                                </li>
                                <li class="mb-2">
                                    <strong>อีเมล:</strong> <?= htmlspecialchars($order['email']) ?>
                                </li>
                                <li class="mb-2">
                                    <strong>โทรศัพท์:</strong> <?= htmlspecialchars($order['phone']) ?>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">ข้อมูลการจัดส่ง</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <strong>สถานะ:</strong> 
                                    <span class="badge bg-warning text-dark"><?= getOrderStatusText($order['order_status']) ?></span>
                                </li>
                                <li class="mb-2">
                                    <strong>วันที่สั่งซื้อ:</strong> 
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </li>
                                <li class="mb-2">
                                    <strong>ที่อยู่จัดส่ง:</strong> 
                                    <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-5">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>รายละเอียดคำสั่งซื้อ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">สินค้า</th>
                                    <th>รายละเอียด</th>
                                    <th width="100">ราคา</th>
                                    <th width="100">จำนวน</th>
                                    <th width="120">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <img src="<?= asset($item['image'] ?? 'assets/images/product1.jpg') ?>" 
                                                 class="img-fluid rounded-2" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                        </td>
                                        <td>
                                            <?= number_format($item['price'], 2) ?> บาท
                                        </td>
                                        <td>
                                            <?= number_format($item['quantity']) ?>
                                        </td>
                                        <td class="fw-bold">
                                            <?= number_format($item['total_price'], 2) ?> บาท
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>สรุปการชำระเงิน</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>ราคาสินค้า</span>
                        <span><?= number_format($subtotal, 2) ?> บาท</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>ค่าจัดส่ง</span>
                        <span><?= $shippingFee == 0 ? '<span class="text-success">ฟรี</span>' : number_format($shippingFee, 2).' บาท' ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>รวมทั้งสิ้น</span>
                        <span><?= number_format($order['total_amount'], 2) ?> บาท</span>
                    </div>
                    
                    <div class="mt-4">
                        <h6 class="mb-3">วิธีการชำระเงิน</h6>
                        <div class="alert alert-info">
                            <?php if ($order['payment_method'] === 'bank_transfer'): ?>
                                <i class="fas fa-university me-2"></i>
                                <strong>โอนเงินผ่านธนาคาร</strong>
                                <p class="mb-1 mt-2">กรุณาชำระเงินภายใน 24 ชั่วโมง</p>
                                <p class="mb-1">บัญชีธนาคารไทยพาณิชย์</p>
                                <p class="mb-1">ชื่อบัญชี: ร้านขนมปั้นสิบยายนิด</p>
                                <p class="mb-1">เลขที่บัญชี: 123-4-56789-0</p>
                                <p class="mb-0">จำนวนเงิน: <?= number_format($order['total_amount'], 2) ?> บาท</p>
                            <?php elseif ($order['payment_method'] === 'qr_code'): ?>
                                <i class="fas fa-qrcode me-2"></i>
                                <strong>ชำระผ่าน QR Code</strong>
                                <div class="text-center mt-3">
                                    <img src="<?= asset('assets/images/qr-payment.png') ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                                    <p class="mt-2 mb-0">สแกน QR Code เพื่อชำระเงิน</p>
                                    <p class="mb-0">จำนวนเงิน: <?= number_format($order['total_amount'], 2) ?> บาท</p>
                                </div>
                            <?php else: ?>
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <strong><?= getPaymentMethodText($order['payment_method']) ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-success mt-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-envelope fa-2x me-3"></i>
                    <div>
                        <h5 class="mb-1">อีเมลยืนยันการสั่งซื้อ</h5>
                        <p class="mb-0">เราได้ส่งอีเมลยืนยันการสั่งซื้อไปที่ <?= htmlspecialchars($order['email']) ?> แล้ว กรุณาตรวจสอบอีเมลของคุณ</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.order-confirmation-icon {
    font-size: 5rem;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

@media (max-width: 768px) {
    .order-actions {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .order-actions .btn {
        width: 100%;
    }
}
</style>

<?php
include '../includes/footer.php';
?>
