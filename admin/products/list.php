<?php
require_once '../../../config/db.php';
require_once '../../../config/functions.php';

// ตรวจสอบสิทธิ์ผู้ดูแลระบบ
if(!isAdmin()) {
    $_SESSION['alert'] = [
        'type' => 'danger',
        'message' => 'คุณไม่มีสิทธิ์เข้าึงหน้านี้'
    ];
    redirect(BASE_URL);
}

$pageTitle = "จัดการสินค้า";

// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../../includes/head.php';
?>

<div class="d-flex">
    <?php include '../../../includes/sidebar.php'; ?>
    
    <div class="main-content flex-grow-1 p-3">
        <h2 class="mb-4">จัดการสินค้า</h2>
        
        <?php displayAlert(); ?>
        
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">รายการสินค้า</h5>
                <a href="add.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> เพิ่มสินค้า
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>รูปภาพ</th>
                                <th>ชื่อสินค้า</th>
                                <th>หมวดหมู่</th>
                                <th>ราา</th>
                                <th>สต็อก</th>
                                <th>สถานะ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td>
                                    <img src="<?php echo BASE_URL . $product['image']; ?>" alt="<?php echo $product['name']; ?>" width="50">
                                </td>
                                <td><?php echo $product['name']; ?></td>
                                <td><?php echo $product['category_name'] ?? '-'; ?></td>
                                <td><?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['status'] ? 'success' : 'danger'; ?>">
                                        <?php echo $product['status'] ? 'เปิดขาย' : 'ปิดขาย'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-product" data-id="<?php echo $product['id']; ?>">
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
    </div>
</div>

<script>
$(document).ready(function() {
    $('.delete-product').click(function() {
        const productId = $(this).data('id');
        if(confirm('คุณแน่ใจว่า้องการลบสินค้านี้?')) {
            window.location.href = 'delete.php?id=' + productId;
        }
    });
});
</script>

<?php
include '../../../includes/footer.php';
?>
