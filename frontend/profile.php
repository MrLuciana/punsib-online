<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: logout.php');
    exit();
}

// ดึงคำสั่งซื้อของผู้ใช้
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.total_price) as total_amount
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "โปรไฟล์ของฉัน - " . $user['fullname'];

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Profile -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="<?= BASE_URL ?>assets/images/profile-placeholder.png" 
                             class="rounded-circle shadow" 
                             width="150" 
                             height="150" 
                             alt="Profile Image">
                        <button class="btn btn-sm btn-success position-absolute bottom-0 end-0 rounded-circle">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <h4 class="mb-1"><?= htmlspecialchars($user['fullname']) ?></h4>
                    <p class="text-muted mb-3">
                        <i class="fas fa-user-tag me-1"></i>
                        <?= ($user['role'] == 'admin') ? 'ผู้ดูแลระบบ' : 'สมาชิก' ?>
                    </p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button class="btn btn-outline-success btn-sm">
                            <i class="fas fa-edit me-1"></i> แก้ไขโปรไฟล์
                        </button>
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-lock me-1"></i> เปลี่ยนรหัสผ่าน
                        </button>
                    </div>
                    
                    <hr>
                    
                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            <?= htmlspecialchars($user['email']) ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            <?= $user['phone'] ? htmlspecialchars($user['phone']) : 'ยังไม่ได้ระบุ' ?>
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <?= $user['address'] ? htmlspecialchars($user['address']) : 'ยังไม่ได้ระบุที่อยู่' ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>เมนูหลัก</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i>คำสั่งซื้อของฉัน
                    </a>
                    <!-- <a href="wishlist.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart me-2"></i>รายการโปรด
                    </a> -->
                    <!-- <a href="addresses.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i>ที่อยู่จัดส่ง
                    </a> -->
                    <a href="change-password.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-lock me-2"></i>เปลี่ยนรหัสผ่าน
                    </a>
                    <?php if($user['role'] == 'admin'): ?>
                    <a href="<?= BASE_URL ?>admin/" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-cog me-2"></i>แผงควบคุม
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>คำสั่งซื้อล่าสุด</h5>
                        <a href="orders.php" class="btn btn-sm btn-light">ดูทั้งหมด</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(count($orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>เลขที่คำสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>จำนวน</th>
                                        <th>ยอดรวม</th>
                                        <th>สถานะ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td><?= $order['order_number'] ?></td>
                                        <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= $order['item_count'] ?> รายการ</td>
                                        <td>฿<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = '';
                                            switch($order['order_status']) {
                                                case 'pending': $statusClass = 'warning'; break;
                                                case 'processing': $statusClass = 'info'; break;
                                                case 'shipped': $statusClass = 'primary'; break;
                                                case 'delivered': $statusClass = 'success'; break;
                                                case 'cancelled': $statusClass = 'danger'; break;
                                                default: $statusClass = 'secondary';
                                            }
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?php 
                                                $statusText = [
                                                    'pending' => 'รอดำเนินการ',
                                                    'processing' => 'กำลังเตรียมสินค้า',
                                                    'shipped' => 'จัดส่งแล้ว',
                                                    'delivered' => 'จัดส่งสำเร็จ',
                                                    'cancelled' => 'ยกเลิก'
                                                ];
                                                echo $statusText[$order['order_status']] ?? $order['order_status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="<?= BASE_URL ?>assets/images/empty-order.png" alt="No orders" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">คุณยังไม่มีคำสั่งซื้อ</h5>
                            <p class="text-muted">เริ่มช้อปปิ้งและค้นหาสินค้าที่คุณชื่นชอบได้เลย</p>
                            <a href="products.php" class="btn btn-success mt-2">
                                <i class="fas fa-shopping-bag me-2"></i>ช้อปเลย
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Address Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>ที่อยู่จัดส่ง</h5>
                        <!-- <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addressModal">
                            <i class="fas fa-plus me-1"></i>เพิ่มที่อยู่
                        </button> -->
                    </div>
                </div>
                <div class="card-body">
                    <?php if($user['address']): ?>
                        <div class="border p-3 rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">ที่อยู่หลัก</h6>
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <p class="mb-1"><?= htmlspecialchars($user['fullname']) ?></p>
                            <p class="mb-1"><?= htmlspecialchars($user['phone']) ?></p>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($user['address'])) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <img src="<?= BASE_URL ?>assets/images/empty-address.png" alt="No address" class="img-fluid mb-3" style="max-height: 150px;">
                            <h5 class="text-muted">คุณยังไม่ได้เพิ่มที่อยู่จัดส่ง</h5>
                            <p class="text-muted">เพิ่มที่อยู่จัดส่งเพื่อความสะดวกในการสั่งซื้อสินค้า</p>
                            <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#addressModal">
                                <i class="fas fa-plus me-2"></i>เพิ่มที่อยู่
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Wishlist Section -->
            <!-- <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-heart me-2"></i>รายการโปรด</h5>
                        <a href="wishlist.php" class="btn btn-sm btn-light">ดูทั้งหมด</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <img src="<?= BASE_URL ?>assets/images/empty-wishlist.png" alt="No wishlist" class="img-fluid mb-3" style="max-height: 150px;">
                        <h5 class="text-muted">คุณยังไม่มีรายการโปรด</h5>
                        <p class="text-muted">เพิ่มสินค้าที่คุณชื่นชอบลงในรายการโปรดได้เลย</p>
                        <a href="products.php" class="btn btn-success mt-2">
                            <i class="fas fa-shopping-bag me-2"></i>ช้อปเลย
                        </a>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addressModalLabel">เพิ่มที่อยู่จัดส่ง</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="save-address.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" id="fullname" name="fullname" 
                               value="<?= htmlspecialchars($user['fullname']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">ที่อยู่</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success">บันทึกที่อยู่</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.profile-card {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.profile-img-container {
    position: relative;
    width: fit-content;
    margin: 0 auto;
}

.profile-img-edit {
    position: absolute;
    bottom: 10px;
    right: 10px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.profile-img-edit:hover {
    background: var(--primary-hover);
    transform: scale(1.1);
}

.list-group-item.active {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.list-group-item:hover:not(.active) {
    background-color: rgba(205, 127, 50, 0.1);
}

.order-status-badge {
    min-width: 100px;
    text-align: center;
}

.address-card {
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary-color);
}

.address-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<?php
include '../includes/footer.php';
?>
