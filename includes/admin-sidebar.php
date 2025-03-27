<?php
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// กำหนดเมนูที่ใช้งานอยู่
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="<?php echo ADMIN_URL; ?>dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>แดชบอร์ด
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>products/list.php">
                    <i class="fas fa-box me-2"></i>จัดการสินค้า
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>categories/list.php">
                    <i class="fas fa-tags me-2"></i>จัดการหมวดหมู่
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>orders/list.php">
                    <i class="fas fa-shopping-bag me-2"></i>จัดการคำสั่งซื้อ
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>customers/list.php">
                    <i class="fas fa-users me-2"></i>จัดการลูกค้า
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo ADMIN_URL; ?>revenue/report.php">
                    <i class="fas fa-chart-line me-2"></i>รายงานยอดขาย
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                </a>
            </li>
        </ul>
    </div>
</nav>