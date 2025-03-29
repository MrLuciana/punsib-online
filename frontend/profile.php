<?php
require_once '../config/db.php';
require_once '../config/functions.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "โปรไฟล์ของฉัน";

// ดึงข้อมูลผู้ใช้
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงประวัติการสั่งซื้อ
$stmt = $conn->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count,
           SUM(oi.total_price) as order_total
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/head.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar เมนูโปรไฟล์ -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar-placeholder bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                        </div>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($user['fullname']) ?></h4>
                    <p class="text-muted mb-3"><?= htmlspecialchars($user['email']) ?></p>
                    
                    <ul class="nav flex-column profile-menu">
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
                                <i class="fas fa-user-circle me-2"></i>ข้อมูลส่วนตัว
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-bag me-2"></i>คำสั่งซื้อของฉัน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="addresses.php">
                                <i class="fas fa-map-marker-alt me-2"></i>ที่อยู่จัดส่ง
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="change-password.php">
                                <i class="fas fa-lock me-2"></i>เปลี่ยนรหัสผ่าน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../includes/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- ส่วนเนื้อหาหลัก -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>ข้อมูลส่วนตัว</h5>
                </div>
                <div class="card-body">
                    <form id="profileForm" method="POST" action="../includes/update-profile.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fullname" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                       value="<?= htmlspecialchars($user['fullname']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" id="username" 
                                       value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">ที่อยู่</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>บันทึกการเปลี่ยนแปลง
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- ประวัติการสั่งซื้อล่าสุด -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>ประวัติการสั่งซื้อล่าสุด</h5>
                </div>
                <div class="card-body">
                    <?php if (count($orders) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>เลขที่คำสั่งซื้อ</th>
                                        <th>วันที่</th>
                                        <th>จำนวน</th>
                                        <th>ยอดรวม</th>
                                        <th>สถานะ</th>
                                        <th>การดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['order_number']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                            <td><?= $order['item_count'] ?> รายการ</td>
                                            <td><?= number_format($order['order_total'], 2) ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                switch ($order['order_status']) {
                                                    case 'completed':
                                                        $statusClass = 'badge bg-success';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'badge bg-info text-dark';
                                                        break;
                                                    case 'shipped':
                                                        $statusClass = 'badge bg-primary';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'badge bg-danger';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge bg-warning text-dark';
                                                }
                                                ?>
                                                <span class="<?= $statusClass ?>">
                                                    <?= getOrderStatusText($order['order_status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-eye"></i> ดูรายละเอียด
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="orders.php" class="btn btn-success">
                                <i class="fas fa-list me-2"></i>ดูประวัติการสั่งซื้อทั้งหมด
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">ยังไม่มีประวัติการสั่งซื้อ</h5>
                            <a href="products.php" class="btn btn-success mt-3">
                                <i class="fas fa-shopping-cart me-2"></i>ช้อปสินค้าอนนี้
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* สไตล์เฉพาะสำหรับหน้าโปรไฟล์ */
.avatar-placeholder {
    background-color: var(--primary-color);
    color: white;
    font-weight: bold;
}

.profile-menu .nav-link {
    padding: 10px 15px;
    color: #333;
    border-radius: 5px;
    margin-bottom: 5px;
    transition: all 0.3s;
}

.profile-menu .nav-link:hover {
    background-color: rgba(205, 127, 50, 0.1);
    color: var(--primary-color);
}

.profile-menu .nav-link.active {
    background-color: var(--primary-color);
    color: white;
}

.profile-menu .nav-link i {
    width: 20px;
    text-align: center;
}

/* สไตล์ฟอร์ม */
#profileForm .form-control {
    padding: 10px;
    border-radius: 5px;
}

#profileForm label {
    font-weight: 500;
}

/* ตารางคำสั่งซื้อ */
.table th {
    background-color: #f8f9fa;
    border-top: none;
}

.table td, .table th {
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
    .profile-menu {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .profile-menu .nav-item {
        margin: 5px;
    }
}
</style>

<script>
$(document).ready(function() {
    // ฟอร์มอัพเดตโปรไฟล์
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const button = form.find('button[type="submit"]');
        const originalText = button.html();
        
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
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
            },
            complete: function() {
                button.prop('disabled', false);
                button.html(originalText);
            }
        });
    });
    
    // ตรวจสอบความถูกต้องของเบอร์โทรศัพท์
    $('#phone').on('input', function() {
        const phone = $(this).val();
        if (phone.length > 0 && !/^[0-9]{10}$/.test(phone)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>

<?php
include '../includes/footer.php';
?>
