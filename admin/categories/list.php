<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "จัดการหมวดหมู่";

// ดึงข้อมูลหมวดหมู่
$stmt = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">จัดการหมวดหมู่</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="manage.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> เพิ่มหมวดหมู่
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อหมวดหมู่</th>
                                    <th>คำอธิบาย</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td><?= htmlspecialchars($category['description'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $category['status'] ? 'success' : 'danger' ?>">
                                            <?= $category['status'] ? 'เปิดใช้งาน' : 'ปิดใช้งาน' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="manage.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-category" data-id="<?= $category['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
    // ระบบลบหมวดหมู่
    $('.delete-category').click(function() {
        const categoryId = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบหมวดหมู่',
            text: 'คุณแน่ใจว่าต้องการลบหมวดหมู่นี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบหมวดหมู่',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + categoryId;
            }
        });
    });

    // ระบบ DataTable
    $('#categoriesTable').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Thai.json'
        }
    });
});
</script>

<?php
include '../../includes/footer.php';
?>
