<?php
require_once '../config/db.php';
require_once '../config/functions.php';
require_once 'config/admin_functions.php';

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "แดชบอร์ดผู้ดูแลระบบ";

// ดึงข้อมูลสถิติ
$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCustomers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// ดึงยอดขายล่าสุด
$recentOrders = $conn->query("
    SELECT o.id, o.order_number, o.total_amount, o.created_at, u.fullname 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ดึงสินค้าขายดี
$bestSellers = $conn->query("
    SELECT p.id, p.name, SUM(oi.quantity) as total_sold 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/admin-head.php';
?>

<body class="admin-dashboard">
    <?php include '../includes/admin-navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include '../includes/admin-sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">แดชบอร์ด</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">สินค้าทั้งหมด</h5>
                                        <h2 class="mb-0"><?= number_format($totalProducts) ?></h2>
                                    </div>
                                    <i class="fas fa-box fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">หมวดหมู่</h5>
                                        <h2 class="mb-0"><?= number_format($totalCategories) ?></h2>
                                    </div>
                                    <i class="fas fa-tags fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">คำสั่งซื้อ</h5>
                                        <h2 class="mb-0"><?= number_format($totalOrders) ?></h2>
                                    </div>
                                    <i class="fas fa-shopping-bag fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">ลูกค้า</h5>
                                        <h2 class="mb-0"><?= number_format($totalCustomers) ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">คำสั่งซื้อล่าสุด</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>เลขที่</th>
                                                <th>ลูกค้า</th>
                                                <th>ยอดรวม</th>
                                                <th>วันที่</th>
                                                <th>จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td><?= $order['order_number'] ?></td>
                                                    <td><?= htmlspecialchars($order['fullname']) ?></td>
                                                    <td><?= number_format($order['total_amount'], 2) ?> บาท</td>
                                                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                                    <td>
                                                        <a href="orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
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
                    </div>

                    <!-- Best Sellers -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">สินค้าขายดี</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($bestSellers as $product): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span><?= htmlspecialchars($product['name']) ?></span>
                                            <span class="badge bg-primary rounded-pill"><?= $product['total_sold'] ?> ชิ้น</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
