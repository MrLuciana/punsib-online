<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "รายละเอียดการสั่งซื้อ - ร้านขนมปั้นสิบยายนิดพัทลุง";

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = BASE_URL . 'orders.php';
    setAlert('warning', 'กรุณาเข้าสู่ระบบเพื่อดูรายการสั่งซื้อ');
    redirect('login.php');
}

// Check if order ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('error', 'ไม่พบรายการสั่งซื้อที่ระบุ');
    redirect('orders.php');
}

$orderId = (int)$_GET['id'];

// Get order details
$stmt = $conn->prepare("
    SELECT o.*, u.fullname, u.phone, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = :order_id AND (o.user_id = :user_id OR :is_admin = 1)
");
$stmt->execute([
    ':order_id' => $orderId,
    ':user_id' => $_SESSION['user_id'],
    ':is_admin' => isAdmin() ? 1 : 0
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setAlert('error', 'ไม่พบรายการสั่งซื้อที่ระบุหรือคุณไม่มีสิทธิ์เข้าถึง');
    redirect('orders.php');
}

// Get order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image, p.discount_price, p.price as original_price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = :order_id
");
$stmt->execute([':order_id' => $orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle payment slip upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_slip'])) {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        setAlert('error', 'โทเคนไม่ถูกต้อง');
        redirect("order-detail.php?id=$orderId");
    }
    
    // Check if order is pending payment
    if ($order['payment_status'] !== 'pending' || $order['order_status'] !== 'pending') {
        setAlert('warning', 'ไม่สามารถอัปโหลดสลิปได้ เนื่องจากสถานะการสั่งซื้อไม่ถูกต้อง');
        redirect("order-detail.php?id=$orderId");
    }
    
    // Upload payment slip
    $uploadDir = '../uploads/payment_slips/';
    $uploadResult = uploadImage($_FILES['payment_slip'], $uploadDir);
    
    if ($uploadResult['success']) {
        // Update order with payment slip
        $stmt = $conn->prepare("UPDATE orders SET payment_slips = :payment_slip WHERE id = :order_id");
        $stmt->execute([
            ':payment_slip' => $uploadResult['file_name'],
            ':order_id' => $orderId
        ]);
        
        setAlert('success', 'อัปโหลดสลิปการชำระเงินเรียบร้อยแล้ว');
        redirect("order-detail.php?id=$orderId");
    } else {
        setAlert('error', $uploadResult['message']);
    }
}

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">
                <i class="fas fa-file-invoice me-2"></i>รายละเอียดการสั่งซื้อ
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">หน้าลัก</a></li>
                    <li class="breadcrumb-item"><a href="orders.php">รายการสั่งซื้อ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายละเอียดการสั่งซื้อ</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-box-open me-2"></i>สินค้าในรายการ
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">รูปภาพ</th>
                                    <th>สินค้า</th>
                                    <th width="100" class="text-center">จำนวน</th>
                                    <th width="120" class="text-end">ราาต่อชิ้น</th>
                                    <th width="120" class="text-end">รวม</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['image']): ?>
                                                <img src="<?= asset('uploads/products/' . $item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                            <?php if ($item['discount_price'] > 0 && $item['discount_price'] < $item['original_price']): ?>
                                                <small class="text-muted">
                                                    <del><?= number_format($item['original_price'], 2) ?> บาท</del>
                                                    <span class="text-danger ms-2"><?= number_format($item['discount_price'], 2) ?> บาท</span>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted"><?= number_format($item['price'], 2) ?> บาท</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format($item['quantity']) ?></td>
                                        <td class="text-end"><?= number_format($item['price'], 2) ?> บาท</td>
                                        <td class="text-end fw-bold"><?= number_format($item['total_price'], 2) ?> บาท</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end">ยอดรวมสินค้า</td>
                                    <td class="text-end fw-bold"><?= number_format(array_sum(array_column($orderItems, 'total_price')), 2) ?> บาท</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">ค่าัดส่ง</td>
                                    <td class="text-end fw-bold">0.00 บาท</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end">ยอดรวมทั้งสิ้น</td>
                                    <td class="text-end fw-bold text-success"><?= number_format($order['total_amount'], 2) ?> บาท</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($order['payment_status'] === 'pending' && $order['payment_method'] !== 'cash'): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>ชำระเงิน
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>กรุณาอัปโหลดสลิปการโอนเงินภายใน 24 ชั่วโมง เพื่อยืนยันการสั่งซื้อ
                        </div>

                        <div class="mb-4">
                            <h6>ข้อมูลการโอนเงิน</h6>
                            <div class="border p-3 rounded bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>ธนาคาร:</strong> กสิกรไทย</p>
                                        <p class="mb-1"><strong>ชื่อบัญชี:</strong> นางนิด นิรนาม</p>
                                        <p class="mb-1"><strong>เลขที่บัญชี:</strong> 123-4-56789-0</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>จำนวนเงิน:</strong> <?= number_format($order['total_amount'], 2) ?> บาท</p>
                                        <p class="mb-1"><strong>วันที่โอน:</strong> <?= date('d/m/Y') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($order['payment_slips'])): ?>
                            <form method="post" enctype="multipart/form-data">
                                <?= csrfField() ?>
                                <div class="mb-3">
                                    <label for="payment_slip" class="form-label">อัปโหลดสลิปการโอนเงิน</label>
                                    <input class="form-control" type="file" id="payment_slip" name="payment_slip" accept="image/*" required>
                                    <div class="form-text">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</div>
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload me-2"></i>อัปโหลดสลิป
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>คุณได้อัปโหลดสลิปการโอนเงินเรียบร้อยแล้ว
                            </div>
                            <div class="text-center mb-3">
                                <img src="<?= asset('uploads/payment_slips/' . $order['payment_slips']) ?>" alt="สลิปการโอนเงิน" class="img-fluid rounded border" style="max-height: 300px;">
                            </div>
                            <p class="text-muted">ระบบกำลังตรวจสอบการชำระเงินของคุณ กรุณารอการยืนยันจากทางร้าน</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>ข้อมูลการสั่งซื้อ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>เลขที่สั่งซื้อ</h6>
                        <p><?= htmlspecialchars($order['order_number']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>วันที่สั่งซื้อ</h6>
                        <p><?= thaiDate($order['created_at'], true) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>สถานะการสั่งซื้อ</h6>
                        <p>
                            <span class="badge <?= getOrderStatusBadgeClass($order['order_status']) ?>">
                                <?= getOrderStatusText($order['order_status']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>สถานะการชำระเงิน</h6>
                        <p>
                            <?php if ($order['payment_status'] === 'pending'): ?>
                                <span class="badge bg-warning text-dark">
                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                </span>
                            <?php elseif ($order['payment_status'] === 'paid'): ?>
                                <span class="badge bg-success">
                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>วิธีการชำระเงิน</h6>
                        <p><?= getPaymentMethodText($order['payment_method']) ?></p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>ข้อมูลลูกค้า
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>ชื่อ-สกุล</h6>
                        <p><?= htmlspecialchars($order['fullname']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>อีเมล</h6>
                        <p><?= htmlspecialchars($order['email']) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h6>เบอร์โทรศัพท์</h6>
                        <p><?= htmlspecialchars($order['phone']) ?></p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-truck me-2"></i>ที่อยู่ในการจัดส่ง
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>ที่อยู่จัดส่ง</h6>
                        <p><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                    </div>
                    
                    <?php if ($order['billing_address']): ?>
                        <div class="mb-3">
                            <h6>ที่อยู่ใบกำกับภาษี</h6>
                            <p><?= nl2br(htmlspecialchars($order['billing_address'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order['note']): ?>
                        <div class="mb-3">
                            <h6>หมายเหตุ</h6>
                            <p><?= nl2br(htmlspecialchars($order['note'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($order['order_status'] === 'pending' && $order['payment_status'] === 'pending'): ?>
        <div class="row mt-4">
            <div class="col">
                <div class="card border-danger">
                    <div class="card-body text-center">
                        <h5 class="text-danger mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>ต้องการยกเลิกการสั่งซื้อนี้หรือไม่?
                        </h5>
                        <p class="mb-4">คุณสามารถยกเลิกการสั่งซื้อนี้ได้หากยังไม่ทำการชำระเงิน</p>
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times me-2"></i>ยกเลิกการสั่งซื้อ
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="cancelOrderModalLabel">ยืนยันการยกเลิกการสั่งซื้อ</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>คุณแน่ใจว่า้องการยกเลิกการสั่งซื้อ <strong><?= $order['order_number'] ?></strong> ใช่หรือไม่?</p>
                        <p class="text-danger">การกระทำนี้ไม่สามารถยกเลิกได้</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <form method="post" action="cancel-order.php">
                            <?= csrfField() ?>
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>ยืนยันการยกเลิก
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.order-detail-card {
    border-radius: 10px;
    overflow: hidden;
}

.order-detail-card .card-header {
    padding: 1rem 1.25rem;
}

.order-detail-card .card-body {
    padding: 1.5rem;
}

.order-product-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

@media (max-width: 768px) {
    .order-product-img {
        width: 50px;
        height: 50px;
    }
}
</style>

<?php
include '../includes/footer.php';
?>
