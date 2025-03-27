<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

// ตรวจสอบว่ามีการส่ง ID ลูกค้ามาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setAlert('danger', 'ไม่พบลูกค้าที่ต้องการ');
    redirect('list.php');
}

$customer_id = (int)$_GET['id'];

// ดึงข้อมูลลูกค้า
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    setAlert('danger', 'ไม่พบลูกค้าที่ต้องการ');
    redirect('list.php');
}

// ดึงคำสั่งซื้อของลูกค้า
$ordersStmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$ordersStmt->execute([$customer_id]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "รายละเอียดลูกค้า: " . htmlspecialchars($customer['fullname']);

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">รายละเอียดลูกค้า</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>กลับ
                    </a>
                    <button class="btn btn-<?= $customer['status'] ? 'danger' : 'success' ?> toggle-status" 
                            data-id="<?= $customer['id'] ?>" 
                            data-status="<?= $customer['status'] ?>">
                        <i class="fas fa-<?= $customer['status'] ? 'times' : 'check' ?> me-2"></i>
                        <?= $customer['status'] ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน' ?>
                    </button>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="row">
                <!-- ข้อมูลลูกค้า -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">ข้อมูลส่วนตัว</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">ชื่อผู้ใช้:</div>
                                <div class="col-sm-8"><?= htmlspecialchars($customer['username']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">ชื่อ-สกุล:</div>
                                <div class="col-sm-8"><?= htmlspecialchars($customer['fullname']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">อีเมล:</div>
                                <div class="col-sm-8"><?= htmlspecialchars($customer['email']) ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">เบอร์โทร:</div>
                                <div class="col-sm-8"><?= $customer['phone'] ?: '-' ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">ที่อยู่:</div>
                                <div class="col-sm-8"><?= $customer['address'] ? nl2br(htmlspecialchars($customer['address'])) : '-' ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4 fw-bold">วันที่สมัคร:</div>
                                <div class="col-sm-8"><?= date('d/m/Y H:i', strtotime($customer['created_at'])) ?></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 fw-bold">สถานะ:</div>
                                <div class="col-sm-8">
                                    <span class="badge bg-<?= $customer['status'] ? 'success' : 'danger' ?>">
                                        <?= $customer['status'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สถิติคำสั่งซื้อ -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">สถิติการสั่งซื้อ</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // คำนวณสถิติ
                            $totalOrdersCount = count($orders);
                            $totalSpent = 0;
                            $lastOrderDate = '-';
                            
                            if ($totalOrdersCount > 0) {
                                $totalSpentStmt = $conn->prepare("SELECT SUM(total_amount) FROM orders WHERE user_id = ?");
                                $totalSpentStmt->execute([$customer_id]);
                                $totalSpent = $totalSpentStmt->fetchColumn();
                                
                                $lastOrderDate = date('d/m/Y', strtotime($orders[0]['created_at']));
                            }
                            ?>
                            <div class="row mb-3">
                                <div class="col-sm-6 fw-bold">จำนวนคำสั่งซื้อ:</div>
                                <div class="col-sm-6"><?= number_format($totalOrdersCount) ?> ครั้ง</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-6 fw-bold">ยอดซื้อทั้งหมด:</div>
                                <div class="col-sm-6"><?= number_format($totalSpent, 2) ?> บาท</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-6 fw-bold">คำสั่งซื้อล่าสุด:</div>
                                <div class="col-sm-6"><?= $lastOrderDate ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ประวัติการสั่งซื้อ -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ประวัติการสั่งซื้อ</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>ลูกค้ายังไม่มีประวัติการสั่งซื้อ
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>เลขที่คำสั่งซื้อ</th>
                                        <th>วันที่สั่งซื้อ</th>
                                        <th>ยอดรวม</th>
                                        <th>สถานะคำสั่งซื้อ</th>
                                        <th>สถานะการชำระเงิน</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= $order['order_number'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td><?= number_format($order['total_amount'], 2) ?> บาท</td>
                                            <td>
                                                <span class="badge bg-<?= getOrderStatusColor($order['order_status']) ?>">
                                                    <?= getOrderStatusText($order['order_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= getPaymentStatusColor($order['payment_status']) ?>">
                                                    <?= getPaymentStatusText($order['payment_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="../orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
$(document).ready(function() {
    // ระบบเปิด/ปิดการใช้งานลูกค้า
    $('.toggle-status').click(function() {
        const customerId = $(this).data('id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus ? 0 : 1;
        
        Swal.fire({
            title: 'ยืนยันการเปลี่ยนสถานะ',
            text: `คุณแน่ใจว่าต้องการ${currentStatus ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน'}ลูกค้านี้หรือไม่?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'toggle-status.php',
                    method: 'POST',
                    data: { 
                        id: customerId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'เปลี่ยนสถานะเรียบร้อย',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                        });
                    }
                });
            }
        });
    });
});
</script>

<?php
include '../../includes/footer.php';
?>
