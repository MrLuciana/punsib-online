<?php
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// กำหนดเมนูที่ใช้งานอยู่
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
<div class="sidebar bg-dark">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand d-flex align-items-center justify-content-center py-4">
        <div class="sidebar-brand-icon">
            <i class="fas fa-store-alt"></i>
        </div>
        <div class="sidebar-brand-text ml-2">ร้านยายนิด</div>
    </div>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Items -->
    <ul class="nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>แดชบอร์ด</span>
            </a>
        </li>

        <!-- Divider -->
        <hr class="sidebar-divider">

        <!-- Heading -->
        <div class="sidebar-heading">
            การขาย
        </div>

        <!-- Orders -->
        <li class="nav-item <?= in_array($current_page, ['orders.php', 'order-detail.php']) ? 'active' : '' ?>">
            <a class="nav-link" href="orders.php">
                <i class="fas fa-fw fa-shopping-cart"></i>
                <span>ออเดอร์</span>
                <span class="badge badge-danger badge-pill float-right">5</span>
            </a>
        </li>

        <!-- Customers -->
        <li class="nav-item <?= in_array($current_page, ['customers.php', 'customer-detail.php']) ? 'active' : '' ?>">
            <a class="nav-link" href="customers.php">
                <i class="fas fa-fw fa-users"></i>
                <span>ลูกค้า</span>
            </a>
        </li>

        <!-- Products Section -->
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
            สินค้า
        </div>

        <!-- Products -->
        <li class="nav-item <?= in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : '' ?>">
            <a class="nav-link" href="products.php">
                <i class="fas fa-fw fa-box-open"></i>
                <span>สินค้าั้งหมด</span>
            </a>
        </li>

        <!-- Categories -->
        <li class="nav-item <?= in_array($current_page, ['categories.php', 'category-add.php', 'category-edit.php']) ? 'active' : '' ?>">
            <a class="nav-link" href="categories.php">
                <i class="fas fa-fw fa-tags"></i>
                <span>หมวดหมู่</span>
            </a>
        </li>

        <!-- Inventory -->
        <li class="nav-item <?= ($current_page == 'inventory.php') ? 'active' : '' ?>">
            <a class="nav-link" href="inventory.php">
                <i class="fas fa-fw fa-warehouse"></i>
                <span>สต็อกสินค้า</span>
                <span class="badge badge-warning badge-pill float-right">3</span>
            </a>
        </li>

        <!-- System Section -->
        <hr class="sidebar-divider">
        <div class="sidebar-heading">
            ระบบ
        </div>

        <!-- Users -->
        <li class="nav-item <?= in_array($current_page, ['users.php', 'user-add.php', 'user-edit.php']) ? 'active' : '' ?>">
            <a class="nav-link" href="users.php">
                <i class="fas fa-fw fa-user-cog"></i>
                <span>ผู้ใช้งาน</span>
            </a>
        </li>

        <!-- Settings -->
        <li class="nav-item <?= ($current_page == 'settings.php') ? 'active' : '' ?>">
            <a class="nav-link" href="settings.php">
                <i class="fas fa-fw fa-cog"></i>
                <span>ตั้งค่าระบบ</span>
            </a>
        </li>
    </ul>

    <!-- Sidebar Toggler -->
    <!-- ปรับปุ่ม Toggle เป็นแบบนี้ -->
    <div class="text-center d-none d-md-inline mt-3">
        <button class="btn btn-circle btn-sm btn-outline-light" id="sidebarToggle">
            <i class="fas fa-angle-left"></i>
        </button>
    </div>

</div>