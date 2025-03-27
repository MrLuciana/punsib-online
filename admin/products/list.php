<?php
require_once '../../config/db.php';
require_once '../../config/functions.php';
require_once '../config/admin_functions.php';

if (!isAdmin()) {
    setAlert('danger', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    redirect(BASE_URL);
}

$pageTitle = "จัดการสินค้า";

// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/admin-head.php';
include '../../includes/admin-navbar.php';
?>

<div class="container-fluid">

    <div class="row">
        <?php include '../../includes/admin-sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">จัดการสินค้า</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="add.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i> เพิ่มสินค้า
                    </a>
                </div>
            </div>

            <?php displayAlert(); ?>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>รูปภาพ</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>หมวดหมู่</th>
                                    <th>ราคา</th>
                                    <th>สต็อก</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <img src="<?= BASE_URL . ($product['image'] ?: 'assets/images/product1.jpg') ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             width="50">
                                    </td>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= $product['category_name'] ?? '-' ?></td>
                                    <td>
                                        <?= number_format($product['price'], 2) ?>
                                        <?php if ($product['discount_price'] > 0): ?>
                                            <br><span class="text-danger">ลดเหลือ <?= number_format($product['discount_price'], 2) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $product['stock'] ?></td>
                                    <td>
                                        <span class="badge bg-<?= $product['status'] ? 'success' : 'danger' ?>">
                                            <?= $product['status'] ? 'เปิดขาย' : 'ปิดขาย' ?>
                                        </span>
                                        <?php if ($product['featured']): ?>
                                            <span class="badge bg-warning mt-1">แนะนำ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-product" data-id="<?= $product['id'] ?>">
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
    // ระบบลบสินค้า
    $('.delete-product').click(function() {
        const productId = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบสินค้า',
            text: 'คุณแน่ใจว่าต้องการลบสินค้านี้หรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ลบสินค้า',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete.php?id=' + productId;
            }
        });
    });

    // ระบบ DataTable
    $('#productsTable').DataTable({
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
