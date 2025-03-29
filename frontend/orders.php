<?php
require_once '../config/db.php';
require_once '../config/functions.php';

$pageTitle = "รายการสั่งซื้อ - ร้านขนมปั้นสิบยายนิดพัทลุง";

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = BASE_URL . 'orders.php';
    setAlert('warning', 'กรุณาเข้าสู่ระบบเพื่อดูรายการสั่งซื้อ');
    redirect('login.php');
}

// Get filter parameters
$status = $_GET['status'] ?? 'all';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$search = $_GET['search'] ?? '';

// Build base query
$query = "
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.total_price) as items_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = :user_id
";

// Add conditions based on filters
$params = [':user_id' => $_SESSION['user_id']];

if ($status !== 'all') {
    $query .= " AND o.order_status = :status";
    $params[':status'] = $status;
}

if (!empty($date_from) && validateDate($date_from)) {
    $query .= " AND DATE(o.created_at) >= :date_from";
    $params[':date_from'] = $date_from;
}

if (!empty($date_to) && validateDate($date_to)) {
    $query .= " AND DATE(o.created_at) <= :date_to";
    $params[':date_to'] = $date_to;
}

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE :search OR o.note LIKE :search)";
    $params[':search'] = "%$search%";
}

// Complete the query with grouping
$query .= " GROUP BY o.id ORDER BY o.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get order counts for filter tabs
// Another option - use a different name:
    $countStmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN order_status = 'shipped' THEN 1 ELSE 0 END) as shipped,
        SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN order_status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN order_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        COUNT(*) as total_orders
    FROM orders 
    WHERE user_id = ?
");

$countStmt->execute([$_SESSION['user_id']]);
$orderCounts = $countStmt->fetch(PDO::FETCH_ASSOC);

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="mb-0">
                <i class="fas fa-shopping-bag me-2"></i>รายการสั่งซื้อของฉัน
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">หน้าหลัก</a></li>
                    <li class="breadcrumb-item active" aria-current="page">รายการสั่งซื้อ</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="get" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">สถานะ</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all">ทั้งหมด (<?= $orderCounts['total_orders'] ?>)</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>รอดำเนินการ (<?= $orderCounts['pending'] ?>)</option>
                                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>กำลังดำเนินการ (<?= $orderCounts['processing'] ?>)</option>
                                <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>จัดส่งแล้ว (<?= $orderCounts['shipped'] ?>)</option>
                                <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>ส่งถึงผู้รับแล้ว (<?= $orderCounts['delivered'] ?>)</option>
                                <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>สำเร็จ (<?= $orderCounts['completed'] ?>)</option>
                                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>ยกเลิก (<?= $orderCounts['cancelled'] ?>)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">จากวันที่</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">ถึงวันที่</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">ค้นหา</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="search" name="search" placeholder="เลขที่สั่งซื้อ, หมายเหตุ" value="<?= htmlspecialchars($search) ?>">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="row">
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                        <h4 class="mb-3">ไม่พบรายการสั่งซื้อ</h4>
                        <p class="text-muted mb-4">คุณยังไม่มีรายการสั่งซื้อในระบบ</p>
                        <a href="products.php" class="btn btn-success px-4">
                            <i class="fas fa-store me-2"></i>เลือกซื้อสินค้า
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="120">เลขที่สั่งซื้อ</th>
                                        <th width="120">วันที่สั่งซื้อ</th>
                                        <th>สินค้า</th>
                                        <th width="120">จำนวน</th>
                                        <th width="150">ราคารวม</th>
                                        <th width="120">สถานะ</th>
                                        <th width="80"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($order['order_number']) ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($order['created_at'])) ?>
                                            </td>
                                            <td>
                                                <?php
                                                $itemsStmt = $conn->prepare("
                                                    SELECT p.name 
                                                    FROM order_items oi
                                                    JOIN products p ON oi.product_id = p.id
                                                    WHERE oi.order_id = ?
                                                    LIMIT 2
                                                ");
                                                $itemsStmt->execute([$order['id']]);
                                                $items = $itemsStmt->fetchAll(PDO::FETCH_COLUMN);
                                                ?>
                                                <?= htmlspecialchars(implode(', ', $items)) ?>
                                                <?php if ($order['item_count'] > 2): ?>
                                                    และสินค้าอีก <?= $order['item_count'] - 2 ?> ชิ้น
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= number_format($order['item_count']) ?> ชิ้น
                                            </td>
                                            <td class="fw-bold">
                                                <?= number_format($order['total_amount'], 2) ?> บาท
                                            </td>
                                            <td>
                                                <span class="badge <?= getOrderStatusBadgeClass($order['order_status']) ?>">
                                                    <?= getOrderStatusText($order['order_status']) ?>
                                                </span>
                                                <?php if ($order['payment_status'] === 'pending' && $order['payment_method'] !== 'cash'): ?>
                                                    <small class="d-block text-danger">รอการชำระเงิน</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if (count($orders) > 10): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Auto-submit form when filter changes
    $('#status, #date_from, #date_to').change(function() {
        $(this).closest('form').submit();
    });

    // Date range validation
    $('#date_from, #date_to').change(function() {
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            Swal.fire({
                icon: 'error',
                title: 'วันที่ไม่ถูกต้อง',
                text: 'วันที่เริ่มต้นต้องมาก่อนวันที่สิ้นสุด'
            });
            $(this).val('');
        }
    });
});
</script>

<style>
.order-table th {
    white-space: nowrap;
}

.order-status-badge {
    font-size: 0.85rem;
    padding: 0.35rem 0.65rem;
}

@media (max-width: 768px) {
    .order-table {
        font-size: 0.85rem;
    }
    
    .order-table th, 
    .order-table td {
        padding: 0.5rem;
    }
}
</style>

<?php
include '../includes/footer.php';
?>
