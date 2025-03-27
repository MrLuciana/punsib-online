<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

// ตรวจสอบว่ามี ID หรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบคำสั่งซื้อที่ต้องการ');
    redirect('list.php');
}

$order_id = (int)$_GET['id'];

// ดึงข้อมูลคำสั่งซื้อ
$stmt = $conn->prepare("
    SELECT o.*, u.fullname, u.email, u.phone, u.address 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setAlert('danger', 'ไม่พบคำสั่งซื้อที่ต้องการ');
    redirect('list.php');
}

// ดึงรายการสินค้าในคำสั่งซื้อ
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงประวัติการอัปเดตสถานะ
$stmt = $conn->prepare("
    SELECT * FROM order_status_history 
    WHERE order_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$order_id]);
$status_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "รายละเอียดคำสั่งซื้อ #" . $order['order_number'];

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">รายละเอียดคำสั่งซื้อ #<?= $order['order_number'] ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> กลับ
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="row mb-4">
                <!-- ข้อมูลคำสั่งซื้อ -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>ข้อมูลคำสั่งซื้อ</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="150">เลขที่คำสั่งซื้อ</th>
                                            <td><?= $order['order_number'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>วันที่สั่งซื้อ</th>
                                            <td><?= thaiDate($order['created_at'], true) ?></td>
                                        </tr>
                                        <tr>
                                            <th>สถานะคำสั่งซื้อ</th>
                                            <td>
                                                <span class="badge bg-<?= getOrderStatusColor($order['order_status']) ?>">
                                                    <?= getOrderStatusText($order['order_status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>สถานะการชำระเงิน</th>
                                            <td>
                                                <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>วิธีการชำระเงิน</th>
                                            <td><?= getPaymentMethodText($order['payment_method']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>ยอดรวม</th>
                                            <td class="fw-bold"><?= number_format($order['total_amount'], 2) ?> บาท</td>
                                        </tr>
                                        <tr>
                                            <th>หมายเหตุ</th>
                                            <td><?= $order['note'] ? htmlspecialchars($order['note']) : '-' ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลลูกค้า -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>ข้อมูลลูกค้า</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="150">ชื่อ-สกุล</th>
                                            <td><?= htmlspecialchars($order['fullname']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>อีเมล</th>
                                            <td><?= htmlspecialchars($order['email']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>เบอร์โทร</th>
                                            <td><?= $order['phone'] ?></td>
                                        </tr>
                                        <tr>
                                            <th>ที่อยู่</th>
                                            <td><?= nl2br(htmlspecialchars($order['address'])) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- รายการสินค้า -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>รายการสินค้า</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>สินค้า</th>
                                            <th width="100">ราคา</th>
                                            <th width="80">จำนวน</th>
                                            <th width="120">รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= BASE_URL . ($item['image'] ?: 'assets/images/no-image.jpg') ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         width="60" class="me-3">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= number_format($item['price'], 2) ?> บาท</td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td class="fw-bold"><?= number_format($item['total_price'], 2) ?> บาท</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">ยอดรวมสินค้า</th>
                                            <th class="text-end"><?= number_format(array_sum(array_column($order_items, 'total_price')), 2) ?> บาท</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">ค่าจัดส่ง</th>
                                            <th class="text-end"><?= number_format($order['total_amount'] - array_sum(array_column($order_items, 'total_price')), 2) ?> บาท</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">รวมทั้งสิ้น</th>
                                            <th class="text-end"><?= number_format($order['total_amount'], 2) ?> บาท</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- อัปเดตสถานะ -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i>อัปเดตสถานะ</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="update_status.php">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <div class="mb-3">
                                    <label for="order_status" class="form-label">สถานะคำสั่งซื้อ</label>
                                    <select class="form-select" id="order_status" name="order_status" required>
                                        <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                                        <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>กำลังเตรียมสินค้า</option>
                                        <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว</option>
                                        <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>จัดส่งสำเร็จ</option>
                                        <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                                        <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">สถานะการชำระเงิน</label>
                                    <select class="form-select" id="payment_status" name="payment_status" required>
                                        <option value="pending" <?= $order['payment_status'] == 'pending' ? 'selected' : '' ?>>รอชำระเงิน</option>
                                        <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>ชำระเงินแล้ว</option>
                                        <option value="failed" <?= $order['payment_status'] == 'failed' ? 'selected' : '' ?>>ชำระเงินล้มเหลว</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="note" class="form-label">หมายเหตุ (ถ้ามี)</label>
                                    <textarea class="form-control" id="note" name="note" rows="2"><?= htmlspecialchars($order['note'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> บันทึกการเปลี่ยนแปลง
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ประวัติการอัปเดต -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>ประวัติการอัปเดต</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($status_history)): ?>
                        <div class="timeline">
                            <?php foreach ($status_history as $history): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">
                                                <?= getOrderStatusText($history['order_status']) ?> 
                                                <span class="badge bg-<?= getPaymentStatusColor($history['payment_status']) ?> ms-2">
                                                    <?= getPaymentStatusText($history['payment_status']) ?>
                                                </span>
                                            </h6>
                                            <small class="text-muted"><?= thaiDate($history['created_at'], true) ?></small>
                                        </div>
                                        <?php if ($history['note']): ?>
                                            <p class="mb-0 text-muted"><?= htmlspecialchars($history['note']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">ไม่มีประวัติการอัปเดต</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    border-left: 2px solid #dee2e6;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: -0.75rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background-color: #198754;
    border: 2px solid white;
}

.timeline-content {
    padding-left: 1rem;
}
</style>

<script>
$(document).ready(function() {
    // ระบบแจ้งเตือนก่อนเปลี่ยนสถานะ
    $('#order_status, #payment_status').change(function() {
        const orderStatus = $('#order_status').val();
        const paymentStatus = $('#payment_status').val();
        
        if (orderStatus === 'cancelled' || paymentStatus === 'failed') {
            Swal.fire({
                title: 'ยืนยันการเปลี่ยนแปลง',
                text: 'การเปลี่ยนสถานะนี้อาจมีผลต่อระบบ คุณแน่ใจหรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'ยืนยัน',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (!result.isConfirmed) {
                    $(this).val($(this).data('prev'));
                }
            });
        }
        
        $(this).data('prev', $(this).val());
    });
});
</script>

<?php
include '../../includes/footer.php';
?>
