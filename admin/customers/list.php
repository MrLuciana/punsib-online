<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "จัดการลูกค้า";

// ดึงข้อมูลลูกค้า
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$where = "WHERE role = 'customer'";
$params = [];

if (!empty($search)) {
    $where .= " AND (username LIKE ? OR email LIKE ? OR fullname LIKE ? OR phone LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

// นับจำนวนลูกค้าทั้งหมด
$countStmt = $conn->prepare("SELECT COUNT(*) as total FROM users $where");
$countStmt->execute($params);
$totalCustomers = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalCustomers / $perPage);

// ดึงข้อมูลลูกค้า
$stmt = $conn->prepare("
    SELECT * FROM users 
    $where 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");

foreach ($params as $i => $param) {
    $stmt->bindValue($i + 1, $param, PDO::PARAM_STR);
}

$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">จัดการลูกค้า</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="input-group">
                        <form method="get" class="d-flex">
                            <input type="text" class="form-control" name="search" placeholder="ค้นหาลูกค้า..." 
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>ชื่อ-สกุล</th>
                                    <th>อีเมล</th>
                                    <th>เบอร์โทร</th>
                                    <th>วันที่สมัคร</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?= $customer['id'] ?></td>
                                    <td><?= htmlspecialchars($customer['username']) ?></td>
                                    <td><?= htmlspecialchars($customer['fullname']) ?></td>
                                    <td><?= htmlspecialchars($customer['email']) ?></td>
                                    <td><?= $customer['phone'] ?: '-' ?></td>
                                    <td><?= date('d/m/Y', strtotime($customer['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $customer['status'] ? 'success' : 'danger' ?>">
                                            <?= $customer['status'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                                        </span>
                                    </td>
                                    
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                        &laquo;
                                    </a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                                        <a class="page-link" 
                                           href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" 
                                       href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                        &raquo;
                                    </a>
                                </li>
                            </ul>
                        </nav>
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
