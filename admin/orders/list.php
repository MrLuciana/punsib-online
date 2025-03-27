<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "รายการคำสั่งซื้อ";

// ตัวกรอง
$status = $_GET['status'] ?? '';
$payment_status = $_GET['payment_status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// สร้างคำสั่ง SQL
$sql = "SELECT o.*, u.fullname, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE 1=1";
$params = [];

if (!empty($status)) {
    $sql .= " AND o.order_status = ?";
    $params[] = $status;
}

if (!empty($payment_status)) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $payment_status;
}

if (!empty($date_from)) {
    $sql .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

$sql .= " ORDER BY o.created_at DESC";

// ดึงข้อมูลคำสั่งซื้อ
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">รายการคำสั่งซื้อ</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- ฟอร์มกรองข้อมูล -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>กรองข้อมูล</h5>
                </div>
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">สถานะคำสั่งซื้อ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                                <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>กำลังเตรียมสินค้า</option>
                                <option value="shipped" <?= $status == 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว</option>
                                <option value="delivered" <?= $status == 'delivered' ? 'selected' : '' ?>>จัดส่งสำเร็จ</option>
                                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
                                <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="payment_status" class="form-label">สถานะการชำระเงิน</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="">ทั้งหมด</option>
                                <option value="pending" <?= $payment_status == 'pending' ? 'selected' : '' ?>>รอชำระเงิน</option>
                                <option value="paid" <?= $payment_status == 'paid' ? 'selected' : '' ?>>ชำระเงินแล้ว</option>
                                <option value="failed" <?= $payment_status == 'failed' ? 'selected' : '' ?>>ชำระเงินล้มเหลว</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">จากวันที่</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $date_from ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">ถึงวันที่</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $date_to ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i> ค้นหา
                            </button>
                            <a href="list.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sync-alt me-1"></i> ล้างค่า
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ตารางแสดงผล -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="ordersTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="120">เลขที่</th>
                                    <th>ลูกค้า</th>
                                    <th width="120">ยอดรวม</th>
                                    <th width="150">สถานะ</th>
                                    <th width="150">วันที่สั่งซื้อ</th>
                                    <th width="100">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="view.php?id=<?= $order['id'] ?>" class="text-primary">
                                            <?= $order['order_number'] ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($order['fullname']) ?></div>
                                        <div class="text-muted small"><?= $order['phone'] ?></div>
                                    </td>
                                    <td class="text-end"><?= number_format($order['total_amount'], 2) ?> บาท</td>
                                    <td>
                                        <span class="badge bg-<?= getOrderStatusColor($order['order_status']) ?>">
                                            <?= getOrderStatusText($order['order_status']) ?>
                                        </span>
                                        <br>
                                        <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?> mt-1">
                                            <?= getPaymentStatusText($order['payment_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= thaiDate($order['created_at'], true) ?></td>
                                    <td>
                                        <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> ดูรายละเอียด
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // ระบบ DataTable
    $('#ordersTable').DataTable({
        responsive: true,
        order: [[4, 'desc']],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Thai.json'
        },
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        initComplete: function() {
            // ปุ่ม Export
            $('#exportBtn').click(function() {
                // สามารถเพิ่มฟังก์ชัน Export เป็น Excel/PDF ได้ที่นี่
                alert('ระบบ Export ข้อมูล');
            });
        }
    });
});
</script>

<?php
include '../../includes/footer.php';
?>
