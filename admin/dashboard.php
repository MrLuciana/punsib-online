<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบสิทธิ์การเข้าถึง
if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect('index.php');
}

$pageTitle = "แดชบอร์ดแบบเรียบง่าย";

// ดึงข้อมูลสถิติ
$stats = [];
$query = "
    SELECT 
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()) as today_orders,
        (SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE() AND order_status = 'completed') as today_revenue,
        (SELECT COUNT(*) FROM products WHERE status = 1) as total_products,
        (SELECT COUNT(*) FROM users WHERE status = 1) as total_users,
        (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders
";

$stmt = $conn->query($query);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/admin-head.php';
include '../includes/admin-navbar.php';
?>

<div class="simple-container">

    <h2 class="dashboard-title">แดชบอร์ดสรุปข้อมูล</h2>
    
    <div class="stats-grid">
        <!-- ยอดขายวันนี้ -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-value"><?= number_format($stats['today_revenue'] ?? 0, 2) ?></div>
            <div class="stat-label">ยอดขายวันนี้ (บาท)</div>
        </div>
        
        <!-- จำนวนออเดอร์วันนี้ -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="stat-value"><?= $stats['today_orders'] ?? 0 ?></div>
            <div class="stat-label">ออเดอร์วันนี้</div>
        </div>
        
        <!-- จำนวนสินค้า -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-value"><?= $stats['total_products'] ?? 0 ?></div>
            <div class="stat-label">สินค้าทั้งหมด</div>
        </div>
        
        <!-- จำนวนสมาชิก -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-value"><?= $stats['total_users'] ?? 0 ?></div>
            <div class="stat-label">สมาชิกทั้งหมด</div>
        </div>
        
        <!-- ออเดอร์รอดำเนินการ -->
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-value"><?= $stats['pending_orders'] ?? 0 ?></div>
            <div class="stat-label">ออเดอร์รอดำเนินการ</div>
        </div>
    </div>
</div>


<?php
include '../includes/admin-footer.php';
?>
