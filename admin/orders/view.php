<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

// ตรวจสอบว่ามี ID มาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setAlert('danger', 'ไม่พบคำสั่งซื้อนี้');
    redirect('list.php');
}

$orderId = $_GET['id'];

// ดึงข้อมูลคำสั่งซื้อ
$sql = "SELECT o.*, u.fullname, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    setAlert('danger', 'ไม่พบคำสั่งซื้อนี้');
    redirect('list.php');
}

// ดึงรายการสินค้าในคำสั่งซื้อ
$sql = "SELECT oi.*, p.name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <a href="list.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> กลับไปรายการคำสั่งซื้อ
                    </a>
                </div>
            </div>
            
            <!-- ส่วนแสดงสถานะ -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">สถานะคำสั่งซื้อ</h5>
                                <span class="badge bg-<?= getOrderStatusColor($order['order_status']) ?>">
                                    <?= getOrderStatusText($order['order_status']) ?>
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">สถานะการชำระเงิน</h5>
                                <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                </span>
                            </div>
                            
                            <form method="post" action="update_status.php">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">อัปเดตสถานะคำสั่งซื้อ</label>
                                        <select name="order_status" class="form-select">
                                            <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                                            <option value="processing" <?= $order['order_status'] == 'processing' ? 'selected' : '' ?>>กำลังเตรียมสินค้า</option>
                                            <option value="shipped" <?= $order['order_status'] == 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว</option>
                                            <option value="delivered" <?= $order['order_status'] == 'delivered' ? 'selected' : '' ?>>จัดส่งสำเร็จ</option>
                                            <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">อัปเดตสถานะการชำระเงิน</label>
                                        <select name="payment_status" class="form-select">
                                            <option value="pending" <?= $order['payment_status'] == 'pending' ? 'selected' : '' ?>>รอชำระเงิน</option>
                                            <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>ชำระเงินแล้ว</option>
                                            <option value="failed" <?= $order['payment_status'] == 'failed' ? 'selected' : '' ?>>ชำระเงินล้มเหลว</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> อัปเดตสถานะ
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">สรุปรายการ</h5>
                            
                            <div class="row mb-2">
                                <div class="col-6">วันที่สั่งซื้อ:</div>
                                <div class="col-6 text-end"><?= thaiDate($order['created_at'], true) ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-6">ยอดรวม:</div>
                                <div class="col-6 text-end"><?= number_format($order['total_amount'], 2) ?> บาท</div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-6">วิธีการชำระเงิน:</div>
                                <div class="col-6 text-end">
                                    <?= $order['payment_method'] == 'bank_transfer' ? 'โอนเงินผ่านธนาคาร' : 'QR Code' ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">หมายเลขคำสั่งซื้อ:</div>
                                <div class="col-6 text-end"><?= $order['order_number'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ส่วนข้อมูลลูกค้า -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ข้อมูลลูกค้า</h5>
                            
                            <div class="mb-2">
                                <strong>ชื่อ-นามสกุล:</strong><br>
                                <?= htmlspecialchars($order['fullname']) ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong>อีเมล:</strong><br>
                                <?= htmlspecialchars($order['email']) ?>
                            </div>
                            
                            <div class="mb-2">
                                <strong>โทรศัพท์:</strong><br>
                                <?= $order['phone'] ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">ที่อยู่จัดส่ง</h5>
                            <div class="mb-2">
                                <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                            </div>
                            
                            <?php if (!empty($order['note'])): ?>
                            <h5 class="card-title mt-3">หมายเหตุ</h5>
                            <div class="mb-2">
                                <?= nl2br(htmlspecialchars($order['note'])) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ส่วนรายการสินค้า -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">รายการสินค้า</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width="50">#</th>
                                            <th>สินค้า</th>
                                            <th width="100" class="text-center">จำนวน</th>
                                            <th width="120" class="text-end">ราคาต่อหน่วย</th>
                                            <th width="120" class="text-end">ราคารวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($item['image'])): ?>
                                                    <img src="<?= BASE_URL ?>/uploads/products/<?= $item['image'] ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         class="img-thumbnail me-3" width="60">
                                                    <?php endif; ?>
                                                    <div><?= htmlspecialchars($item['name']) ?></div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= $item['quantity'] ?></td>
                                            <td class="text-end"><?= number_format($item['price'], 2) ?> บาท</td>
                                            <td class="text-end"><?= number_format($item['total_price'], 2) ?> บาท</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">ยอดรวมทั้งสิ้น</th>
                                            <th class="text-end"><?= number_format($order['total_amount'], 2) ?> บาท</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ส่วนหลักฐานการชำระเงิน -->
            <?php if (!empty($order['payment_slips'])): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">หลักฐานการชำระเงิน</h5>
                            
                            <div class="text-center">
                                <a href="<?= BASE_URL ?>/uploads/payment_slips/<?= $order['payment_slips'] ?>" target="_blank">
                                    <img src="<?= BASE_URL ?>/uploads/payment_slips/<?= $order['payment_slips'] ?>" 
                                         class="img-fluid img-thumbnail" 
                                         style="max-height: 300px;" 
                                         alt="หลักฐานการชำระเงิน">
                                </a>
                                
                                <div class="mt-3">
                                    <a href="<?= BASE_URL ?>/uploads/payments/<?= $order['payment_slips'] ?>" 
                                       class="btn btn-primary" 
                                       download="payment_<?= $order['order_number'] ?>.jpg">
                                        <i class="fas fa-download me-1"></i> ดาวน์โหลด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>
